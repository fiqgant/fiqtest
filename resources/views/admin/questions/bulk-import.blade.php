@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header title="Bulk Import Questions" subtitle="Upload an Excel file to create multiple questions at once.">
        <a href="{{ route('admin.questions.index') }}" class="btn-secondary"><i class="fas fa-arrow-left"></i> Back to Questions</a>
        <a href="/questions-template.xlsx" download="questions-template.xlsx" class="btn-primary"><i class="fas fa-download"></i> Download Template</a>
    </x-admin.page-header>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        {{-- Upload form --}}
        <div class="md:col-span-2">
            <form method="POST" action="{{ route('admin.questions.bulk.preview') }}" enctype="multipart/form-data">
                @csrf
                <div class="form-section">
                    <div class="form-section-title"><i class="fas fa-cog mr-1.5"></i> Import Settings</div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="form-label">Course Offering</label>
                            <select name="course_offering_id" class="form-select" required>
                                <option value="">Select offering…</option>
                                @foreach($offerings as $offering)
                                    <option value="{{ $offering->id }}" {{ old('course_offering_id') == $offering->id ? 'selected' : '' }}>
                                        {{ $offering->course->name }} — {{ $offering->academicPeriod->name }} · {{ $offering->class_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Language (applies to all questions)</label>
                            <div
                                id="language-combobox"
                                class="relative"
                                data-languages='@json($judge0Languages)'
                                data-initial="{{ old('language', 'Python (3.8.1)') }}"
                            >
                                <input type="hidden" name="language" id="language-value" required>
                                <input type="text" id="language-search" class="form-input" placeholder="Type to search language…"
                                    autocomplete="off" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="language-options">
                                <div id="language-options"
                                    class="hidden absolute z-30 mt-1 max-h-64 w-full overflow-auto rounded-xl border border-slate-200 bg-white py-1 shadow-xl"
                                    role="listbox"></div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Excel File (.xlsx)</label>
                        <input type="file" name="file" accept=".xlsx,.xls" class="form-input" required>
                        <p class="text-xs text-slate-400 mt-1">Max 5MB. Use the template above for the correct format.</p>
                        @error('file') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="btn-primary"><i class="fas fa-eye mr-1.5"></i> Preview Import</button>
                </div>
            </form>
        </div>

        {{-- Instructions --}}
        <div class="space-y-4">
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                <div class="font-semibold text-slate-700 mb-3"><i class="fas fa-info-circle text-indigo-500 mr-1.5"></i> How it works</div>
                <ol class="text-sm text-slate-600 space-y-2 list-decimal list-inside">
                    <li>Download the template</li>
                    <li>Fill in your questions (keep the header row)</li>
                    <li>Select offering and language above</li>
                    <li>Upload the file and preview</li>
                    <li>Confirm to import</li>
                </ol>
            </div>
            <div class="bg-amber-50 rounded-2xl border border-amber-200 p-5">
                <div class="font-semibold text-amber-700 mb-2"><i class="fas fa-exclamation-triangle mr-1.5"></i> Test cases format</div>
                <code class="text-xs text-amber-800 bg-amber-100 px-2 py-1 rounded block mb-2">input||output||hidden</code>
                <p class="text-xs text-amber-700">Separate multiple test cases with <code class="bg-amber-100 px-1 rounded">;</code></p>
                <code class="text-xs text-amber-800 bg-amber-100 px-2 py-1 rounded block mt-2">5||25||0;-3||9||1</code>
                <p class="text-xs text-amber-600 mt-1">hidden: <code class="bg-amber-100 px-1 rounded">1</code> = hidden, <code class="bg-amber-100 px-1 rounded">0</code> = visible</p>
            </div>
        </div>

    </div>

    @include('admin.questions._language-combobox-script')
@endsection
