@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header title="My Profile" subtitle="Manage your account details and change your password." />

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Profile Info -->
        <div>
            <form method="POST" action="{{ route('admin.profile.update') }}">
                @csrf
                @method('PUT')

                <div class="form-section">
                    <div class="form-section-title"><i class="fas fa-user mr-1.5"></i> Profile Information</div>
                    <div class="flex items-center gap-4 mb-5">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-400 to-indigo-600 flex items-center justify-center shadow-lg shadow-indigo-500/20 flex-shrink-0">
                            <i class="fas fa-user text-white text-lg"></i>
                        </div>
                        <div>
                            <div class="font-bold text-slate-800">{{ $admin->name }}</div>
                            <div class="text-xs text-slate-400">{{ $admin->email }}</div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-input" value="{{ $admin->name }}" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-input" value="{{ $admin->email }}" required>
                    </div>
                    <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Save Changes</button>
                </div>
            </form>
        </div>

        <!-- Change Password -->
        <div>
            <form method="POST" action="{{ route('admin.profile.password') }}">
                @csrf
                @method('PUT')

                <div class="form-section">
                    <div class="form-section-title"><i class="fas fa-lock mr-1.5"></i> Change Password</div>
                    <div class="mb-4">
                        <label class="form-label">Current Password</label>
                        <input type="password" name="current_password" class="form-input" placeholder="••••••••" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">New Password</label>
                        <input type="password" name="password" class="form-input" placeholder="Min. 8 characters" required minlength="8">
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="password_confirmation" class="form-input" placeholder="Repeat new password" required minlength="8">
                    </div>
                    <button type="submit" class="btn-primary"><i class="fas fa-key"></i> Change Password</button>
                </div>
            </form>
        </div>
    </div>
@endsection
