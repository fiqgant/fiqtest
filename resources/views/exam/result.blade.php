<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Result</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white min-h-screen flex items-center justify-center p-4">
    <div class="max-w-2xl w-full bg-gray-800 rounded-2xl p-8">
        <div class="flex items-start justify-between mb-3">
            <h1 class="text-2xl font-bold">Exam Result</h1>
            <a href="{{ route('exam.result.pdf', $attempt) }}" target="_blank"
               class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium px-4 py-2 rounded-xl transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Download PDF
            </a>
        </div>
        <p class="text-gray-300 mb-6">{{ $exam->title }}</p>

        @if($attempt->is_disqualified)
            <div class="mb-6 rounded-lg border border-red-500/50 bg-red-900/30 px-4 py-3 text-red-200">
                <div class="font-semibold">Attempt failed by exam policy</div>
                <div class="text-sm">{{ $attempt->disqualification_reason ?? 'Disqualified due to violation policy.' }}</div>
            </div>
        @endif

        <div class="grid grid-cols-3 gap-4 mb-8">
            <div class="bg-gray-700 rounded-lg p-4">
                <div class="text-gray-400 text-sm">Total Score</div>
                <div class="text-2xl font-bold">{{ number_format((float) $attempt->total_score, 2) }}</div>
            </div>
            <div class="bg-gray-700 rounded-lg p-4">
                <div class="text-gray-400 text-sm">Max Score</div>
                <div class="text-2xl font-bold">{{ number_format((float) $attempt->max_score, 2) }}</div>
            </div>
            <div class="bg-gray-700 rounded-lg p-4">
                <div class="text-gray-400 text-sm">Percentage</div>
                <div class="text-2xl font-bold">{{ number_format((float) $attempt->percentage, 2) }}%</div>
            </div>
        </div>

        <div class="space-y-3">
            @foreach($attempt->attemptQuestions as $aq)
                <div class="bg-gray-700 rounded-lg p-4 flex items-center justify-between">
                    <div>
                        <div class="font-semibold">{{ $aq->question->title }}</div>
                        <div class="text-sm text-gray-400">{{ ucfirst($aq->question->difficulty) }}</div>
                    </div>
                     <div class="text-right">
                         <div class="font-semibold">{{ number_format((float) $aq->score, 2) }} / {{ number_format((float) $aq->weight, 2) }}</div>
                        <div class="text-xs text-gray-400">Passed tests: {{ $aq->passed_tests ?? 0 }} / {{ $aq->total_tests ?? 0 }}</div>
                         <div class="text-sm {{ $aq->is_correct ? 'text-green-400' : 'text-red-400' }}">{{ $aq->is_correct ? 'Correct' : 'Incorrect' }}</div>
                     </div>
                 </div>
            @endforeach
        </div>
    </div>
</body>
</html>
