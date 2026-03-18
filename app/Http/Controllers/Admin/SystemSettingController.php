<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Services\Judge0Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\View\View;

class SystemSettingController extends Controller
{
    public function editJudge0(): View
    {
        $url = SystemSetting::getValue('judge0.url', env('JUDGE0_URL', 'https://judge0-ce.p.rapidapi.com'));
        $host = SystemSetting::getValue('judge0.api_host', env('JUDGE0_API_HOST', 'judge0-ce.p.rapidapi.com'));
        $timeout = SystemSetting::getValue('judge0.timeout', (string) env('JUDGE0_TIMEOUT', 30));
        $encryptedKey = SystemSetting::getValue('judge0.api_key');

        $hasApiKey = !empty($encryptedKey);

        return view('admin.settings.judge0', compact('url', 'host', 'timeout', 'hasApiKey'));
    }

    public function updateJudge0(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'url' => ['required', 'url'],
            'api_host' => ['nullable', 'string', 'max:255'],
            'timeout' => ['required', 'integer', 'min:5', 'max:120'],
            'api_key' => ['nullable', 'string', 'max:500'],
            'clear_api_key' => ['nullable', 'boolean'],
        ]);

        SystemSetting::setValue('judge0.url', $data['url']);
        SystemSetting::setValue('judge0.api_host', $data['api_host'] ?? '');
        SystemSetting::setValue('judge0.timeout', (string) $data['timeout']);

        if (!empty($data['clear_api_key'])) {
            SystemSetting::setValue('judge0.api_key', null);
        } elseif (!empty($data['api_key'])) {
            SystemSetting::setValue('judge0.api_key', Crypt::encryptString($data['api_key']));
        }

        return redirect()->route('admin.settings.judge0.edit')->with('success', 'Judge0 settings updated.');
    }

    public function testJudge0(): JsonResponse
    {
        $start = microtime(true);

        $url     = rtrim((string) SystemSetting::getValue('judge0.url', env('JUDGE0_URL', '')), '/');
        $host    = SystemSetting::getValue('judge0.api_host', env('JUDGE0_API_HOST', ''));
        $timeout = (int) SystemSetting::getValue('judge0.timeout', (string) env('JUDGE0_TIMEOUT', 30));
        $encKey  = SystemSetting::getValue('judge0.api_key');
        $apiKey  = null;

        if ($encKey) {
            try { $apiKey = \Illuminate\Support\Facades\Crypt::decryptString($encKey); } catch (\Throwable) {}
        }

        if (empty($url)) {
            return response()->json(['ok' => false, 'message' => 'Judge0 URL is not configured.', 'ms' => 0]);
        }

        try {
            $request = \Illuminate\Support\Facades\Http::timeout($timeout)->acceptJson();

            if ($apiKey && $host) {
                $request = $request->withHeaders([
                    'X-RapidAPI-Key'  => $apiKey,
                    'X-RapidAPI-Host' => $host,
                ]);
            }

            $response = $request->get($url . '/languages');
            $ms = round((microtime(true) - $start) * 1000);

            if (!$response->successful()) {
                return response()->json([
                    'ok'      => false,
                    'message' => 'HTTP ' . $response->status() . ': ' . $response->body(),
                    'ms'      => $ms,
                ]);
            }

            $languages = collect($response->json())->filter(fn($r) => is_array($r) && !empty($r['id']))->count();

            return response()->json([
                'ok'        => true,
                'message'   => 'Connected successfully.',
                'ms'        => $ms,
                'languages' => $languages,
            ]);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return response()->json([
                'ok'      => false,
                'message' => 'Could not connect to Judge0: ' . $e->getMessage(),
                'ms'      => round((microtime(true) - $start) * 1000),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'ok'      => false,
                'message' => $e->getMessage(),
                'ms'      => round((microtime(true) - $start) * 1000),
            ]);
        }
    }
}
