@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header title="Database Backup" subtitle="Download a manual backup or schedule automatic backups sent to email." />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

        <div class="lg:col-span-2 space-y-6">

            <form method="POST" action="{{ route('admin.settings.backup.update') }}">
                @csrf
                @method('PUT')

                <div class="form-section">
                    <div class="form-section-title"><i class="fas fa-envelope mr-1.5"></i> Recipient Email</div>
                    <div>
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" value="{{ old('email', $email) }}"
                               class="form-input" placeholder="admin@example.com" required>
                        <p class="text-xs text-slate-400 mt-1">The .sql file will be sent to this address on schedule or manually.</p>
                        @error('email') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-title"><i class="fas fa-calendar-alt mr-1.5"></i> Automatic Schedule</div>

                    <div class="mb-4">
                        <label class="inline-flex items-center gap-3 cursor-pointer">
                            <input type="hidden" name="schedule_enabled" value="0">
                            <input type="checkbox" name="schedule_enabled" value="1"
                                   class="w-4 h-4 rounded accent-indigo-500"
                                   {{ $enabled === '1' ? 'checked' : '' }}>
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Enable automatic backup</span>
                        </label>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Day</label>
                            <select name="schedule_day" class="form-input">
                                @foreach(['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'] as $i => $name)
                                    <option value="{{ $i }}" {{ (int)$day === $i ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Time</label>
                            <input type="time" name="schedule_time" value="{{ old('schedule_time', $time) }}" class="form-input" required>
                            <p class="text-xs text-slate-400 mt-1">Make sure the Laravel Scheduler cron is active on the server.</p>
                        </div>
                    </div>
                </div>

                <x-admin.form-actions :cancel="route('admin.dashboard')" save-label="Save Settings" />
            </form>

        </div>

        <div class="lg:col-span-1 space-y-4">

            <div class="form-section">
                <div class="form-section-title"><i class="fas fa-download mr-1.5"></i> Manual Download</div>
                <p class="text-sm text-slate-500 mb-4">Download the SQL dump directly to your device.</p>
                <a href="{{ route('admin.settings.backup.download') }}" class="btn-primary w-full justify-center">
                    <i class="fas fa-database"></i> Download .sql
                </a>
            </div>

            <div class="form-section">
                <div class="form-section-title"><i class="fas fa-paper-plane mr-1.5"></i> Send to Email Now</div>
                <p class="text-sm text-slate-500 mb-4">Send a backup immediately to the configured recipient.</p>
                <form method="POST" action="{{ route('admin.settings.backup.send') }}">
                    @csrf
                    <button type="submit" class="btn-secondary w-full justify-center"
                            onclick="return confirm('Send database backup to email now?')">
                        <i class="fas fa-envelope"></i> Send Now
                    </button>
                </form>
                @if($email)
                    <p class="text-xs text-slate-400 mt-2 text-center">Will be sent to: <strong>{{ $email }}</strong></p>
                @else
                    <p class="text-xs text-rose-400 mt-2 text-center">No recipient email configured.</p>
                @endif
            </div>

            <div class="form-section bg-slate-50 dark:bg-slate-800/50">
                <div class="form-section-title text-slate-500"><i class="fas fa-info-circle mr-1.5"></i> Scheduler Setup</div>
                <p class="text-xs text-slate-500 mb-2">Add this cron entry on the server to enable automatic backups:</p>
                <code class="block bg-slate-800 text-emerald-400 text-xs p-3 rounded-lg font-mono leading-relaxed">
                    * * * * * docker exec fiqtest-app-1 php artisan schedule:run >> /dev/null 2>&1
                </code>
                <p class="text-xs text-slate-400 mt-2">Or run manually: <code class="font-mono">php artisan db:backup</code></p>
            </div>

        </div>
    </div>
@endsection
