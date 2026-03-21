<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Submitted</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white min-h-screen flex items-center justify-center p-4">
    <div class="max-w-xl w-full bg-gray-800 rounded-2xl p-8 text-center">
        <h1 class="text-2xl font-bold mb-3">{{ $attempt->is_disqualified ? 'Attempt Disqualified' : 'Submission Received' }}</h1>
        <p class="text-gray-300 mb-2">{{ $exam->title }}</p>
        @if($attempt->is_disqualified)
            <div class="mb-4 rounded-lg border border-red-500/50 bg-red-900/30 px-4 py-3 text-sm text-red-200">
                {{ $attempt->disqualification_reason ?? 'Your attempt has been marked as failed by exam policy.' }}
            </div>
        @endif
        <p class="text-gray-400">Scores are hidden by exam configuration. Please wait for the instructor announcement.</p>
    </div>
</body>
</html>
