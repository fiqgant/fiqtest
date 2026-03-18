<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseOffering;
use App\Models\Question;
use App\Models\QuestionTag;
use App\Services\Judge0Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class QuestionController extends Controller
{
    public function index(Request $request): View
    {
        $offeringId = $request->integer('course_offering_id');

        $questions = Question::with('courseOffering.course', 'courseOffering.academicPeriod')
            ->when($offeringId, fn($q) => $q->where('course_offering_id', $offeringId))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $offerings = CourseOffering::with('course', 'academicPeriod')->latest()->get();

        return view('admin.questions.index', compact('questions', 'offerings', 'offeringId'));
    }

    public function create(): View
    {
        $offerings = CourseOffering::with('course', 'academicPeriod')->latest()->get();
        $judge0Languages = app(Judge0Service::class)->getSupportedLanguages();
        $tags = QuestionTag::orderBy('name')->get();

        return view('admin.questions.form', [
            'question' => new Question(),
            'offerings' => $offerings,
            'testCasesText' => '',
            'judge0Languages' => $judge0Languages,
            'tags' => $tags,
            'selectedTagIds' => [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatePayload($request);
        $data['slug'] = Str::slug($data['title']) . '-' . Str::lower(Str::random(6));

        $question = Question::create($data);
        $question->tags()->sync($request->input('tag_ids', []));

        return redirect()->route('admin.questions.index')->with('success', 'Question created.');
    }

    public function edit(Question $question): View
    {
        $offerings = CourseOffering::with('course', 'academicPeriod')->latest()->get();
        $judge0Languages = app(Judge0Service::class)->getSupportedLanguages();
        $tags = QuestionTag::orderBy('name')->get();
        $selectedTagIds = $question->tags()->pluck('question_tags.id')->toArray();
        $testCasesText = collect($question->test_cases)->map(function (array $tc): string {
            $input = str_replace("\n", '\\n', (string) ($tc['input'] ?? ''));
            $output = str_replace("\n", '\\n', (string) ($tc['expected_output'] ?? ''));
            $hidden = !empty($tc['is_hidden']) ? '1' : '0';

            return $input . '||' . $output . '||' . $hidden;
        })->implode("\n");

        return view('admin.questions.form', compact('question', 'offerings', 'testCasesText', 'judge0Languages', 'tags', 'selectedTagIds'));
    }

    public function update(Request $request, Question $question): RedirectResponse
    {
        $data = $this->validatePayload($request);
        $question->update($data);
        $question->tags()->sync($request->input('tag_ids', []));

        return redirect()->route('admin.questions.index')->with('success', 'Question updated.');
    }

    public function destroy(Question $question): RedirectResponse
    {
        $question->delete();

        return redirect()->route('admin.questions.index')->with('success', 'Question deleted.');
    }

    private function validatePayload(Request $request): array
    {
        $data = $request->validate([
            'course_offering_id' => ['required', 'exists:course_offerings,id'],
            'title' => ['required', 'string', 'max:255'],
            'difficulty' => ['required', 'in:easy,medium,hard'],
            'description' => ['required', 'string'],
            'default_weight' => ['required', 'numeric', 'min:0'],
            'starter_code' => ['nullable', 'string'],
            'reference_solution' => ['nullable', 'string'],
            'hint' => ['nullable', 'string'],
            'language' => ['required', 'string', 'max:50'],
            'test_cases_text' => ['required', 'string'],
        ]);

        $testCases = collect(explode("\n", trim($data['test_cases_text'])))
            ->map(fn($line) => trim($line))
            ->filter()
            ->map(function (string $line): array {
                $parts = explode('||', $line);

                return [
                    'input' => str_replace('\\n', "\n", $parts[0] ?? ''),
                    'expected_output' => str_replace('\\n', "\n", $parts[1] ?? ''),
                    'is_hidden' => ($parts[2] ?? '0') === '1',
                ];
            })
            ->values()
            ->all();

        unset($data['test_cases_text']);
        $data['test_cases'] = $testCases;

        return $data;
    }
}
