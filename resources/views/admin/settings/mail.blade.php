@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header title="Mail / SMTP Settings" subtitle="Konfigurasi server email untuk pengiriman backup dan notifikasi sistem." />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

        <div class="lg:col-span-2">
            <form method="POST" action="{{ route('admin.settings.mail.update') }}">
                @csrf
                @method('PUT')

                {{-- Driver --}}
                <div class="form-section">
                    <div class="form-section-title"><i class="fas fa-cog mr-1.5"></i> Driver</div>
                    <div>
                        <label class="form-label">Mailer</label>
                        <select name="mailer" id="mailer-select" class="form-input" onchange="switchMailer(this.value)">
                            <option value="resend" {{ $mailer === 'resend' ? 'selected' : '' }}>Resend (Recommended — no SMTP port needed)</option>
                            <option value="smtp" {{ $mailer === 'smtp' ? 'selected' : '' }}>SMTP</option>
                            <option value="log" {{ $mailer === 'log' ? 'selected' : '' }}>Log (development only)</option>
                        </select>
                    </div>
                </div>

                {{-- Resend Section --}}
                <div class="form-section" id="section-resend" style="display:none">
                    <div class="form-section-title">
                        <i class="fas fa-bolt mr-1.5 text-indigo-500"></i> Resend API Key
                    </div>

                    <div class="mb-4 p-3 rounded-xl bg-indigo-50 border border-indigo-200 flex items-start gap-3">
                        <i class="fas fa-info-circle text-indigo-500 mt-0.5 flex-shrink-0"></i>
                        <div class="text-xs text-indigo-700">
                            Resend mengirim email via API — tidak perlu port SMTP. Cocok untuk VPS yang memblokir port 587/465.
                            Gratis 3.000 email/bulan.
                        </div>
                    </div>

                    <div>
                        <label class="form-label">API Key</label>
                        <input type="password" name="resend_api_key" class="form-input font-mono"
                               placeholder="{{ $hasResendKey ? 'Biarkan kosong untuk mempertahankan API key lama' : 're_xxxxxxxxxxxxxxxxxxxx' }}">

                        @if($hasResendKey)
                            <div class="flex items-center gap-2 mt-2 p-3 rounded-xl bg-emerald-50 border border-emerald-200">
                                <i class="fas fa-check-circle text-emerald-500 text-sm"></i>
                                <span class="text-xs text-emerald-700 font-medium">API Key tersimpan dan aktif.</span>
                            </div>
                            <label class="inline-flex items-center gap-2.5 mt-3 cursor-pointer">
                                <input type="checkbox" name="clear_resend_key" value="1" class="w-4 h-4 rounded accent-rose-500">
                                <span class="text-sm text-slate-600">Hapus API key tersimpan</span>
                            </label>
                        @else
                            <p class="text-xs text-slate-400 mt-1">Buat API key di dashboard Resend kamu.</p>
                        @endif
                    </div>
                </div>

                {{-- SMTP Section --}}
                <div class="form-section" id="section-smtp" style="display:none">
                    <div class="form-section-title"><i class="fas fa-server mr-1.5"></i> SMTP Connection</div>

                    <div class="mb-5 p-3 rounded-xl bg-blue-50 border border-blue-200 flex items-start gap-3">
                        <i class="fab fa-google text-blue-500 mt-0.5 text-lg flex-shrink-0"></i>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-blue-800 mb-1">Gmail Quickfill</p>
                            <p class="text-xs text-blue-600 mb-2">Isi host/port/encryption otomatis untuk Gmail.</p>
                            <button type="button" onclick="fillGmail()" class="text-xs bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg font-medium transition">
                                <i class="fab fa-google mr-1"></i> Isi Setting Gmail
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="form-label">Host</label>
                            <input type="text" name="host" id="smtp-host" value="{{ old('host', $host) }}"
                                   class="form-input font-mono" placeholder="smtp.gmail.com">
                        </div>
                        <div>
                            <label class="form-label">Port</label>
                            <input type="number" name="port" id="smtp-port" value="{{ old('port', $port) }}"
                                   class="form-input font-mono" placeholder="587">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Encryption</label>
                        <select name="encryption" id="smtp-encryption" class="form-input">
                            <option value="tls" {{ $encryption === 'tls' ? 'selected' : '' }}>TLS (port 587)</option>
                            <option value="ssl" {{ $encryption === 'ssl' ? 'selected' : '' }}>SSL (port 465)</option>
                            <option value="" {{ $encryption === '' ? 'selected' : '' }}>None</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" value="{{ old('username', $username) }}"
                               class="form-input font-mono" placeholder="fiqgant@gmail.com">
                    </div>

                    <div>
                        <label class="form-label">Password / App Password</label>
                        <input type="password" name="password" class="form-input font-mono"
                               placeholder="{{ $hasPassword ? 'Biarkan kosong untuk mempertahankan password lama' : 'Masukkan password' }}">

                        @if($hasPassword)
                            <div class="flex items-center gap-2 mt-2 p-3 rounded-xl bg-emerald-50 border border-emerald-200">
                                <i class="fas fa-check-circle text-emerald-500 text-sm"></i>
                                <span class="text-xs text-emerald-700 font-medium">Password tersimpan dan aktif.</span>
                            </div>
                            <label class="inline-flex items-center gap-2.5 mt-3 cursor-pointer">
                                <input type="checkbox" name="clear_password" value="1" class="w-4 h-4 rounded accent-rose-500">
                                <span class="text-sm text-slate-600">Hapus password tersimpan</span>
                            </label>
                        @endif

                        <div class="mt-3 p-3 rounded-xl bg-amber-50 border border-amber-200">
                            <p class="text-xs font-semibold text-amber-800 mb-1"><i class="fas fa-exclamation-triangle mr-1"></i> Gmail — gunakan App Password</p>
                            <p class="text-xs font-mono bg-amber-100 rounded px-2 py-1 text-amber-800">
                                myaccount.google.com → Security → 2-Step Verification → App passwords
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Sender Identity --}}
                <div class="form-section">
                    <div class="form-section-title"><i class="fas fa-id-card mr-1.5"></i> Identitas Pengirim</div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">From Email</label>
                            <input type="email" name="from_address" value="{{ old('from_address', $fromAddress) }}"
                                   class="form-input" placeholder="fiqgant@gmail.com" required>
                            <p class="text-xs text-slate-400 mt-1" id="from-hint-resend" style="display:none">
                                Pakai domain terverifikasi di Resend, atau <code class="font-mono">onboarding@resend.dev</code> untuk testing.
                            </p>
                        </div>
                        <div>
                            <label class="form-label">From Name</label>
                            <input type="text" name="from_name" value="{{ old('from_name', $fromName) }}"
                                   class="form-input" placeholder="{{ config('app.name') }}" required>
                        </div>
                    </div>
                </div>

                <x-admin.form-actions :cancel="route('admin.dashboard')" save-label="Simpan Pengaturan" />
            </form>
        </div>

        {{-- Right --}}
        <div class="lg:col-span-1 space-y-4">

            {{-- Test Send --}}
            <div class="form-section">
                <div class="form-section-title"><i class="fas fa-paper-plane mr-1.5"></i> Test Kirim Email</div>
                <p class="text-sm text-slate-500 mb-4">Verifikasi konfigurasi dengan mengirim email test.</p>
                <form method="POST" action="{{ route('admin.settings.mail.test') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Kirim test ke</label>
                        <input type="email" name="test_email" value="{{ $fromAddress ?: '' }}"
                               class="form-input" placeholder="email@example.com" required>
                    </div>
                    <button type="submit" class="btn-primary w-full justify-center">
                        <i class="fas fa-bolt"></i> Kirim Test Email
                    </button>
                </form>
            </div>

            {{-- Active Config --}}
            <div class="form-section bg-slate-50 dark:bg-slate-800/50">
                <div class="form-section-title text-slate-500"><i class="fas fa-info-circle mr-1.5"></i> Konfigurasi Aktif</div>
                <div class="space-y-2 text-xs text-slate-600 dark:text-slate-400">
                    <div><span class="font-medium">Driver:</span> {{ config('mail.default') }}</div>
                    @if(config('mail.default') === 'resend')
                        <div><span class="font-medium">API Key:</span> {{ config('resend.api_key') ? '✓ tersimpan' : '✗ belum diset' }}</div>
                    @else
                        <div><span class="font-medium">Host:</span> {{ config('mail.mailers.smtp.host') ?: '-' }}</div>
                        <div><span class="font-medium">Port:</span> {{ config('mail.mailers.smtp.port') ?: '-' }}</div>
                    @endif
                    <div><span class="font-medium">From:</span> {{ config('mail.from.address') ?: '-' }}</div>
                </div>
            </div>

        </div>
    </div>

    <script>
    function switchMailer(val) {
        document.getElementById('section-resend').style.display = val === 'resend' ? '' : 'none';
        document.getElementById('section-smtp').style.display   = val === 'smtp'   ? '' : 'none';
        document.getElementById('from-hint-resend').style.display = val === 'resend' ? '' : 'none';
    }

    function fillGmail() {
        document.getElementById('smtp-host').value = 'smtp.gmail.com';
        document.getElementById('smtp-port').value = '587';
        document.getElementById('smtp-encryption').value = 'tls';
    }

    switchMailer('{{ $mailer }}');
    </script>
@endsection
