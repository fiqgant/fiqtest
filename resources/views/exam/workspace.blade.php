<!DOCTYPE html>
<html lang="en">

@include('exam.workspace._head')

<body class="bg-black text-white h-screen overflow-hidden" x-data="examApp()" @time-up.window="handleTimeUp()">

    @include('exam.workspace._guards')

    <div class="flex h-full">
        @include('exam.workspace._sidebar')

        <main class="flex-1 flex flex-col min-w-0">
            @include('exam.workspace._header')

            <div class="flex-1 flex overflow-hidden">
                @include('exam.workspace._question_panel')
                @include('exam.workspace._editor_panel')
            </div>
        </main>
    </div>

    @include('exam.workspace._modals')

    @include('exam.workspace._scripts_monaco')
    @include('exam.workspace._scripts_app')

</body>
</html>
