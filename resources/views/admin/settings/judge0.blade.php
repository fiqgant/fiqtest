@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header title="Judge0 Settings" subtitle="Manage your runner configuration from the admin panel without editing .env." />

    <div class="max-w-2xl flex flex-col gap-5">

    <form method="POST" action="{{ route('admin.settings.judge0.update') }}">
        @csrf
        @method('PUT')

        <div class="form-section">
            <div class="form-section-title"><i class="fas fa-server mr-1.5"></i> Connection</div>
            <div class="mb-4">
                <label class="form-label">Judge0 Base URL</label>
                <input type="url" name="url" value="{{ old('url', $url) }}" class="form-input font-mono" placeholder="https://judge0-ce.example.com" required>
                <p class="text-xs text-slate-400 mt-1">The base URL of your Judge0 instance.</p>
            </div>
            <div class="mb-4">
                <label class="form-label">API Host <span class="font-normal text-slate-400">(RapidAPI)</span></label>
                <input type="text" name="api_host" value="{{ old('api_host', $host) }}" class="form-input font-mono" placeholder="judge0-ce.p.rapidapi.com">
                <p class="text-xs text-slate-400 mt-1">Leave blank if not using RapidAPI proxy.</p>
            </div>
            <div>
                <label class="form-label">Timeout (seconds)</label>
                <input type="number" name="timeout" min="5" max="120" value="{{ old('timeout', $timeout) }}" class="form-input" required>
            </div>
        </div>

        <div class="form-section">
            <div class="form-section-title"><i class="fas fa-key mr-1.5"></i> API Key</div>
            <div>
                <label class="form-label">API Key</label>
                <input type="password" name="api_key" class="form-input font-mono" placeholder="Leave empty to keep the current key">
                @if($hasApiKey)
                    <div class="flex items-center gap-2 mt-2 p-3 rounded-xl bg-emerald-50 border border-emerald-200">
                        <i class="fas fa-check-circle text-emerald-500 text-sm"></i>
                        <span class="text-xs text-emerald-700 font-medium">An API key is currently stored and active.</span>
                    </div>
                    <label class="inline-flex items-center gap-2.5 mt-3 cursor-pointer">
                        <input type="checkbox" name="clear_api_key" value="1" class="w-4 h-4 rounded accent-rose-500">
                        <span class="text-sm text-slate-600">Remove stored API key</span>
                    </label>
                @else
                    <p class="text-xs text-slate-400 mt-1">No API key stored. Enter one above if required.</p>
                @endif
            </div>
        </div>

        <x-admin.form-actions :cancel="route('admin.dashboard')" save-label="Save Settings" />
    </form>

    <!-- Connection Test -->
    <div>
        <div class="form-section">
            <div class="form-section-title"><i class="fas fa-plug mr-1.5"></i> Connection Test</div>
            <p class="text-sm text-slate-500 mb-4">Test whether Judge0 is reachable using the currently saved configuration.</p>

            <button id="btn-test" type="button" onclick="testJudge0()"
                class="btn-primary">
                <i class="fas fa-bolt"></i> Test Connection
            </button>

            <div id="test-result" class="mt-4 hidden">
                <div id="test-result-inner" class="rounded-xl p-4 border text-sm flex items-start gap-3"></div>
            </div>
        </div>
    </div>

    </div>{{-- end max-w-2xl wrapper --}}

    <script>
    async function testJudge0() {
        const btn = document.getElementById('btn-test');
        const resultBox = document.getElementById('test-result');
        const resultInner = document.getElementById('test-result-inner');

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Testing...';
        resultBox.classList.add('hidden');

        try {
            const res = await fetch('{{ route('admin.settings.judge0.test') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
            });

            const data = await res.json();

            if (data.ok) {
                resultInner.className = 'rounded-xl p-4 border text-sm flex items-start gap-3 bg-emerald-50 border-emerald-200 text-emerald-800';
                resultInner.innerHTML = `
                    <i class="fas fa-check-circle text-emerald-500 mt-0.5 text-base flex-shrink-0"></i>
                    <div>
                        <div class="font-semibold">Connected successfully</div>
                        <div class="text-xs mt-0.5 text-emerald-600">${data.languages} languages available &nbsp;·&nbsp; Response time: ${data.ms}ms</div>
                    </div>`;
            } else {
                resultInner.className = 'rounded-xl p-4 border text-sm flex items-start gap-3 bg-rose-50 border-rose-200 text-rose-800';
                resultInner.innerHTML = `
                    <i class="fas fa-times-circle text-rose-500 mt-0.5 text-base flex-shrink-0"></i>
                    <div>
                        <div class="font-semibold">Connection failed</div>
                        <div class="text-xs mt-0.5 text-rose-600 font-mono">${data.message}</div>
                        <div class="text-xs mt-0.5 text-rose-500">Response time: ${data.ms}ms</div>
                    </div>`;
            }
        } catch (e) {
            resultInner.className = 'rounded-xl p-4 border text-sm flex items-start gap-3 bg-rose-50 border-rose-200 text-rose-800';
            resultInner.innerHTML = `
                <i class="fas fa-times-circle text-rose-500 mt-0.5 text-base flex-shrink-0"></i>
                <div>
                    <div class="font-semibold">Request error</div>
                    <div class="text-xs mt-0.5 font-mono text-rose-600">${e.message}</div>
                </div>`;
        }

        resultBox.classList.remove('hidden');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-bolt"></i> Test Connection';
    }
    </script>
@endsection
