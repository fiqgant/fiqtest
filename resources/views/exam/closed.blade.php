<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Closed</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white min-h-screen flex items-center justify-center p-4">
    <div class="max-w-xl w-full bg-gray-800 rounded-2xl p-8 text-center">
        <h1 class="text-2xl font-bold mb-3">Exam Unavailable</h1>
        <p class="text-gray-300 mb-2">{{ $exam->title }}</p>
        <p class="text-gray-400">This exam is not published yet or has already been closed.</p>
    </div>
</body>
</html>
