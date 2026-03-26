<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: sans-serif; background: #f8fafc; color: #1e293b; margin: 0; padding: 24px; }
        .card { background: #fff; border-radius: 12px; padding: 32px; max-width: 540px; margin: auto; box-shadow: 0 2px 12px rgba(0,0,0,0.08); }
        .badge { display: inline-block; background: #eef2ff; color: #4f46e5; font-size: 12px; font-weight: 600; border-radius: 6px; padding: 4px 10px; margin-bottom: 16px; }
        h2 { margin: 0 0 8px; font-size: 20px; }
        p { color: #475569; line-height: 1.6; margin: 0 0 16px; }
        .meta { background: #f1f5f9; border-radius: 8px; padding: 16px; font-size: 13px; color: #64748b; }
        .meta b { color: #1e293b; }
        .footer { text-align: center; font-size: 12px; color: #94a3b8; margin-top: 24px; }
    </style>
</head>
<body>
    <div class="card">
        <div class="badge">Database Backup</div>
        <h2>Backup Ready</h2>
        <p>Your database backup is attached to this email. Please store the SQL file in a safe location.</p>

        <div class="meta">
            <div><b>Database:</b> {{ $database }}</div>
            <div style="margin-top:6px"><b>Generated at:</b> {{ $generatedAt }}</div>
            <div style="margin-top:6px"><b>File:</b> {{ $filename }}</div>
        </div>

        <p style="margin-top:20px; font-size:13px; color:#94a3b8;">
            This email was sent automatically by <strong>{{ config('app.name') }}</strong>.
        </p>
    </div>
    <div class="footer">{{ config('app.name') }} &copy; {{ date('Y') }}</div>
</body>
</html>
