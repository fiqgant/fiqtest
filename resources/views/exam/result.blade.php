<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Result</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white min-h-screen flex items-center justify-center p-4">
    <div class="max-w-2xl w-full bg-gray-800 rounded-2xl p-8">
        <h1 class="text-2xl font-bold mb-3">Exam Result</h1>
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
