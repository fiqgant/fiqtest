<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseOffering;
use App\Models\Question;
use App\Models\QuestionTag;
use App\Services\Judge0Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class QuestionController extends Controller
{
    public function index(Request $request): View
    {
        $offeringId = $request->integer('course_offering_id');
        $search     = $request->string('search')->trim()->toString();
        $type       = $request->string('type')->trim()->toString();
        $difficulty = $request->string('difficulty')->trim()->toString();

        $perPage = in_array((int) $request->input('per_page'), [20, 50, 100, 0]) ? (int) $request->input('per_page') : 20;

        $query = Question::with('courseOffering.course', 'courseOffering.academicPeriod', 'tags')
            ->when($offeringId, fn($q) => $q->where('course_offering_id', $offeringId))
            ->when($search,     fn($q) => $q->where('title', 'like', "%{$search}%"))
            ->when($type,       fn($q) => $q->where('type', $type))
            ->when($difficulty, fn($q) => $q->where('difficulty', $difficulty))
            ->latest();

        $questions = $perPage === 0
            ? $query->paginate($query->count() ?: 1)->withQueryString()
            : $query->paginate($perPage)->withQueryString();

        $offerings = CourseOffering::with('course', 'academicPeriod')->latest()->get();

        $stats = Question::selectRaw('difficulty, count(*) as total')
            ->when($offeringId, fn($q) => $q->where('course_offering_id', $offeringId))
            ->groupBy('difficulty')
            ->pluck('total', 'difficulty');

        return view('admin.questions.index', compact('questions', 'offerings', 'offeringId', 'search', 'type', 'difficulty', 'stats', 'perPage'));
    }

    public function create(): View
    {
        $offerings        = CourseOffering::with('course', 'academicPeriod')->latest()->get();
        $judge0Languages  = app(Judge0Service::class)->getSupportedLanguages();
        $tags             = QuestionTag::orderBy('name')->get();

        return view('admin.questions.form', [
            'question'      => new Question(),
            'offerings'     => $offerings,
            'testCasesText' => '',
            'judge0Languages' => $judge0Languages,
            'tags'          => $tags,
            'selectedTagIds' => [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatePayload($request);
        $data['slug'] = Str::slug($data['title']) . '-' . Str::lower(Str::random(6));

        $question = Question::create($data);
        $question->tags()->sync($request->input('tag_ids', []));
        $this->syncOptions($question, $request);

        return redirect()->route('admin.questions.index')->with('success', 'Question created.');
    }

    public function edit(Question $question): View
    {
        $offerings       = CourseOffering::with('course', 'academicPeriod')->latest()->get();
        $judge0Languages = app(Judge0Service::class)->getSupportedLanguages();
        $tags            = QuestionTag::orderBy('name')->get();
        $selectedTagIds  = $question->tags()->pluck('question_tags.id')->toArray();

        $testCasesText = collect($question->test_cases ?? [])->map(function (array $tc): string {
            $input  = str_replace("\n", '\\n', (string) ($tc['input'] ?? ''));
            $output = str_replace("\n", '\\n', (string) ($tc['expected_output'] ?? ''));
            $hidden = !empty($tc['is_hidden']) ? '1' : '0';

            return $input . '||' . $output . '||' . $hidden;
        })->implode("\n");

        return view('admin.questions.form', compact(
            'question', 'offerings', 'testCasesText', 'judge0Languages', 'tags', 'selectedTagIds'
        ));
    }

    public function update(Request $request, Question $question): RedirectResponse
    {
        $data = $this->validatePayload($request);
        $question->update($data);
        $question->tags()->sync($request->input('tag_ids', []));
        $this->syncOptions($question, $request);

        return redirect()->route('admin.questions.index')->with('success', 'Question updated.');
    }

    public function destroy(Question $question): RedirectResponse
    {
        $question->delete();

        return redirect()->route('admin.questions.index')->with('success', 'Question deleted.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $ids   = $request->input('ids', []);
        $count = Question::whereIn('id', $ids)->delete();

        return redirect()->route('admin.questions.index')->with('success', "{$count} question(s) deleted.");
    }

    public function preview(Question $question): View
    {
        $question->load('options');

        return view('admin.questions.preview', compact('question'));
    }

    public function previewRun(Request $request, Question $question): JsonResponse
    {
        $request->validate(['code' => ['required', 'string'], 'stdin' => ['nullable', 'string']]);

        $result = app(Judge0Service::class)->execute(
            $request->input('code'),
            $question->language ?? 'python3',
            $request->input('stdin') ? [$request->input('stdin')] : []
        );

        return response()->json([
            'output' => $result->output,
            'error'  => $result->error,
            'status' => $result->status,
            'time'   => $result->executionTime,
        ]);
    }

    public function duplicate(Question $question): RedirectResponse
    {
        $question->load('options');

        $new = $question->replicate();
        $new->title = $question->title . ' (Copy)';
        $new->slug  = Str::slug($new->title) . '-' . Str::lower(Str::random(6));
        $new->save();

        foreach ($question->options as $option) {
            $new->options()->create($option->only(['text', 'is_correct', 'order']));
        }

        $new->tags()->sync($question->tags()->pluck('question_tags.id'));

        return redirect()->route('admin.questions.edit', $new)->with('success', 'Question duplicated. Review and save changes.');
    }

    public function stats(Question $question): View
    {
        $rows = $question->attemptQuestions()
            ->whereHas('attempt', fn($q) => $q->whereIn('status', ['submitted', 'graded']))
            ->with('attempt.student')
            ->get();

        $total      = $rows->count();
        $correct    = $rows->where('is_correct', true)->count();
        $avgScore   = $total > 0 ? round($rows->avg(fn($aq) => (float) $aq->effectiveScore()), 2) : 0;
        $pctCorrect = $total > 0 ? round($correct / $total * 100) : 0;

        return view('admin.questions.stats', compact('question', 'rows', 'total', 'correct', 'avgScore', 'pctCorrect'));
    }

    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate(['image' => ['required', 'image', 'max:4096']]);

        $path = $request->file('image')->store('question-images', 'public');

        return response()->json(['url' => asset('storage/' . $path)]);
    }

    // ── Internals ─────────────────────────────────────────────────────

    private function validatePayload(Request $request): array
    {
        $type = $request->input('type', 'coding');

        $rules = [
            'course_offering_id' => ['required', 'exists:course_offerings,id'],
            'type'               => ['required', 'in:' . implode(',', array_keys(Question::TYPES))],
            'title'              => ['required', 'string', 'max:255'],
            'difficulty'         => ['required', 'in:easy,medium,hard'],
            'description'        => ['required', 'string'],
            'default_weight'     => ['required', 'numeric', 'min:0'],
            'starter_code'       => ['nullable', 'string'],
            'reference_solution' => ['nullable', 'string'],
            'hint'               => ['nullable', 'string'],
        ];

        if ($type === 'coding') {
            $rules['language']        = ['required', 'string', 'max:50'];
            $rules['test_cases_text'] = ['required', 'string'];
        }

        if ($type === 'fill_in_blank') {
            $rules['fill_blank_answer'] = ['required', 'string', 'max:1000'];
        }

        if ($type === 'true_false') {
            $rules['true_false_answer'] = ['required', 'in:0,1'];
        }

        if (in_array($type, ['multiple_choice', 'multiple_select'])) {
            $rules['options']                  = ['required', 'array', 'min:2'];
            $rules['options.*.text']           = ['required', 'string', 'max:1000'];
            $rules['options.*.is_correct']     = ['nullable'];
        }

        $data = $request->validate($rules);

        // Parse test cases for coding
        if ($type === 'coding') {
            $data['test_cases'] = collect(explode("\n", trim($data['test_cases_text'])))
                ->map(fn($line) => trim($line))
                ->filter()
                ->map(function (string $line): array {
                    $parts = explode('||', $line);

                    return [
                        'input'           => str_replace('\\n', "\n", $parts[0] ?? ''),
                        'expected_output' => str_replace('\\n', "\n", $parts[1] ?? ''),
                        'is_hidden'       => ($parts[2] ?? '0') === '1',
                    ];
                })
                ->values()
                ->all();

            unset($data['test_cases_text']);
        } else {
            $data['test_cases'] = null;
            $data['language']   = null;
        }

        // Clear fields that belong to other types
        if ($type !== 'fill_in_blank') {
            $data['fill_blank_answer'] = null;
        }

        if ($type !== 'true_false') {
            $data['true_false_answer'] = null;
        }

        if ($type !== 'coding') {
            $data['starter_code'] = null;
        }

        unset($data['options']); // handled separately via syncOptions()

        return $data;
    }

    private function syncOptions(Question $question, Request $request): void
    {
        if (! in_array($question->type, ['multiple_choice', 'multiple_select'])) {
            $question->options()->delete();

            return;
        }

        $question->options()->delete();

        $rawOptions = $request->input('options', []);
        foreach ($rawOptions as $i => $opt) {
            $text = trim($opt['text'] ?? '');
            if ($text === '') {
                continue;
            }

            $question->options()->create([
                'text'       => $text,
                'is_correct' => isset($opt['is_correct']) && $opt['is_correct'] == '1',
                'order'      => $i,
            ]);
        }
    }
}
