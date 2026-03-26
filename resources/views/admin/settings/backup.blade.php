@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header title="Database Backup" subtitle="Download manual atau kirim backup otomatis ke email sesuai jadwal." />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

        {{-- Left: Settings Form --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Schedule Settings --}}
            <form method="POST" action="{{ route('admin.settings.backup.update') }}">
                @csrf
                @method('PUT')

                <div class="form-section">
                    <div class="form-section-title"><i class="fas fa-envelope mr-1.5"></i> Email Tujuan</div>
                    <div>
                        <label class="form-label">Alamat Email</label>
                        <input type="email" name="email" value="{{ old('email', $email) }}"
                               class="form-input" placeholder="admin@example.com" required>
                        <p class="text-xs text-slate-400 mt-1">File .sql akan dikirim ke email ini secara otomatis maupun manual.</p>
                        @error('email') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-title"><i class="fas fa-calendar-alt mr-1.5"></i> Jadwal Otomatis</div>

                    <div class="mb-4">
                        <label class="inline-flex items-center gap-3 cursor-pointer">
                            <input type="hidden" name="schedule_enabled" value="0">
                            <input type="checkbox" name="schedule_enabled" value="1" class="w-4 h-4 rounded accent-indigo-500"
                                   {{ $enabled === '1' ? 'checked' : '' }}>
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Aktifkan backup otomatis</span>
                        </label>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Hari</label>
                            <select name="schedule_day" class="form-input">
                                @foreach(['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'] as $i => $nama)
                                    <option value="{{ $i }}" {{ (int)$day === $i ? 'selected' : '' }}>{{ $nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Jam (WIB)</label>
                            <input type="time" name="schedule_time" value="{{ old('schedule_time', $time) }}" class="form-input" required>
                            <p class="text-xs text-slate-400 mt-1">Pastikan Laravel Scheduler aktif di server.</p>
                        </div>
                    </div>
                </div>

                <x-admin.form-actions :cancel="route('admin.dashboard')" save-label="Simpan Pengaturan" />
            </form>

        </div>

        {{-- Right: Actions --}}
        <div class="lg:col-span-1 space-y-4">

            {{-- Download Manual --}}
            <div class="form-section">
                <div class="form-section-title"><i class="fas fa-download mr-1.5"></i> Download Manual</div>
                <p class="text-sm text-slate-500 mb-4">Unduh file .sql langsung ke perangkat Anda sekarang.</p>
                <a href="{{ route('admin.settings.backup.download') }}"
                   class="btn-primary w-full justify-center">
                    <i class="fas fa-database"></i> Download .sql
                </a>
            </div>

            {{-- Send to Email Now --}}
            <div class="form-section">
                <div class="form-section-title"><i class="fas fa-paper-plane mr-1.5"></i> Kirim ke Email Sekarang</div>
                <p class="text-sm text-slate-500 mb-4">Kirim backup langsung ke email yang sudah dikonfigurasi.</p>
                <form method="POST" action="{{ route('admin.settings.backup.send') }}">
                    @csrf
                    <button type="submit" class="btn-secondary w-full justify-center"
                            onclick="return confirm('Kirim backup database ke email sekarang?')">
                        <i class="fas fa-envelope"></i> Kirim Sekarang
                    </button>
                </form>
                @if($email)
                    <p class="text-xs text-slate-400 mt-2 text-center">Akan dikirim ke: <strong>{{ $email }}</strong></p>
                @else
                    <p class="text-xs text-rose-400 mt-2 text-center">Isi email tujuan terlebih dahulu.</p>
                @endif
            </div>

            {{-- Scheduler Info --}}
            <div class="form-section bg-slate-50 dark:bg-slate-800/50">
                <div class="form-section-title text-slate-500"><i class="fas fa-info-circle mr-1.5"></i> Setup Scheduler</div>
                <p class="text-xs text-slate-500 mb-2">Tambahkan cron ini di server agar jadwal otomatis berjalan:</p>
                <code class="block bg-slate-800 text-emerald-400 text-xs p-3 rounded-lg font-mono leading-relaxed">
                    * * * * * cd /path-to-app && php artisan schedule:run >> /dev/null 2>&1
                </code>
                <p class="text-xs text-slate-400 mt-2">Atau jalankan manual: <code class="font-mono">php artisan db:backup</code></p>
            </div>

        </div>
    </div>
@endsection
