@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header title="Bulk Import Students" subtitle="Import multiple students at once via Excel or copy-paste.">
        <a href="{{ route('admin.students.index') }}" class="btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
    </x-admin.page-header>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-2 space-y-5">

            {{-- Excel Upload --}}
            <form method="POST" action="{{ route('admin.students.bulk.preview') }}" enctype="multipart/form-data">
                @csrf
                <div class="form-section">
                    <div class="form-section-title"><i class="fas fa-file-excel mr-1.5 text-emerald-500"></i> Upload Excel (.xlsx)</div>
                    <input type="file" name="file" accept=".xlsx,.xls" class="form-input mb-3">
                    @error('file') <p class="text-xs text-rose-500 mb-2">{{ $message }}</p> @enderror
                    <p class="text-xs text-slate-400 mb-3">Format kolom: <code class="bg-slate-100 px-1 rounded">name, nim, email(opsional)</code>. Header row diabaikan.</p>
                    <button type="submit" class="btn-primary"><i class="fas fa-eye mr-1.5"></i> Preview Excel</button>
                </div>
            </form>

            {{-- CSV Paste --}}
            <form method="POST" action="{{ route('admin.students.bulk.preview') }}">
                @csrf
                <div class="form-section">
                    <div class="form-section-title"><i class="fas fa-paste mr-1.5 text-indigo-500"></i> Copy-Paste CSV</div>
                    <textarea name="csv_data" rows="10" class="form-textarea font-mono text-sm mb-2"
                        placeholder="Budi Santoso,2021001,budi@email.com&#10;Siti Rahayu,2021002&#10;Ahmad Fauzi,2021003,ahmad@email.com">{{ old('csv_data') }}</textarea>
                    @error('csv_data') <p class="text-xs text-rose-500 mb-2">{{ $message }}</p> @enderror
                    <p class="text-xs text-slate-400 mb-3">One row = one student. Format: <code class="bg-slate-100 px-1 rounded">name,nim</code> or <code class="bg-slate-100 px-1 rounded">name,nim,email</code></p>
                    <button type="submit" class="btn-primary"><i class="fas fa-eye mr-1.5"></i> Preview CSV</button>
                </div>
            </form>

        </div>

        {{-- Instructions --}}
        <div class="space-y-4">
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                <div class="font-semibold text-slate-700 mb-3"><i class="fas fa-info-circle text-indigo-500 mr-1.5"></i> Format</div>
                <table class="text-sm w-full">
                    <thead><tr class="text-xs text-slate-400 uppercase"><th class="text-left pb-1">Kolom</th><th class="text-left pb-1">Wajib</th></tr></thead>
                    <tbody class="text-slate-600 space-y-1">
                        <tr><td class="py-0.5 font-mono font-semibold">name</td><td>Ya</td></tr>
                        <tr><td class="py-0.5 font-mono font-semibold">nim</td><td>Ya</td></tr>
                        <tr><td class="py-0.5 font-mono font-semibold">email</td><td>Tidak</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="bg-amber-50 rounded-2xl border border-amber-200 p-5">
                <div class="font-semibold text-amber-700 mb-2"><i class="fas fa-exclamation-triangle mr-1.5"></i> Notes</div>
                <ul class="text-xs text-amber-700 space-y-1 list-disc list-inside">
                    <li>Duplicate NIMs will be skipped</li>
                    <li>Empty rows are ignored</li>
                    <li>For Excel, the first row (header) is ignored</li>
                </ul>
            </div>
        </div>
    </div>
@endsection
