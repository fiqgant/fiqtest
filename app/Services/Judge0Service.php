<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class Judge0Service
{
    private string $baseUrl;
    private ?string $apiKey;
    private ?string $apiHost;
    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) $this->resolveSetting('judge0.url', (string) config('services.judge0.url', 'https://judge0-ce.p.rapidapi.com')), '/');
        $this->apiHost = $this->resolveSetting('judge0.api_host', (string) config('services.judge0.api_host'));
        $this->timeout = (int) $this->resolveSetting('judge0.timeout', (string) config('services.judge0.timeout', 30));

        $storedEncryptedKey = $this->resolveSetting('judge0.api_key', null);
        $this->apiKey = $storedEncryptedKey ? $this->decryptKey($storedEncryptedKey) : (string) config('services.judge0.api_key');

        if ($this->apiKey === '') {
            $this->apiKey = null;
        }

        if ($this->apiHost === '') {
            $this->apiHost = null;
        }
    }

    public function execute(string $code, string $language, array $stdin = []): ExecutionResult
    {
        $payload = [
            'source_code' => $code,
            'language_id' => $this->mapLanguageToId($language),
            'stdin' => implode("\n", $stdin),
        ];

        try {
            $request = Http::timeout($this->timeout)
                ->acceptJson()
                ->asJson();

            if (!empty($this->apiKey) && !empty($this->apiHost)) {
                $request = $request->withHeaders([
                    'X-RapidAPI-Key' => $this->apiKey,
                    'X-RapidAPI-Host' => $this->apiHost,
                ]);
            }

            $response = $request->post($this->baseUrl . '/submissions?base64_encoded=false&wait=true', $payload);

            if (!$response->successful()) {
                Log::error('Judge0 API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return ExecutionResult::error('Judge0 request failed: ' . $response->status());
            }

            return $this->parseResponse((array) $response->json());
        } catch (ConnectionException $e) {
            Log::error('Judge0 connection timeout', ['message' => $e->getMessage()]);

            return ExecutionResult::error('Judge0 timeout. Please retry.');
        } catch (\Throwable $e) {
            Log::error('Judge0 execution error', ['message' => $e->getMessage()]);

            return ExecutionResult::error('Judge0 execution failed: ' . $e->getMessage());
        }
    }

    public function getSupportedLanguages(): array
    {
        $cacheKey = 'judge0.languages.' . md5($this->baseUrl . '|' . ($this->apiHost ?? ''));

        return Cache::remember($cacheKey, now()->addMinutes(10), function (): array {
            try {
                $request = Http::timeout($this->timeout)
                    ->acceptJson();

                if (!empty($this->apiKey) && !empty($this->apiHost)) {
                    $request = $request->withHeaders([
                        'X-RapidAPI-Key' => $this->apiKey,
                        'X-RapidAPI-Host' => $this->apiHost,
                    ]);
                }

                $response = $request->get($this->baseUrl . '/languages');

                if (!$response->successful()) {
                    Log::warning('Judge0 languages endpoint failed', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);

                    return $this->fallbackLanguages();
                }

                $payload = $response->json();

                if (!is_array($payload)) {
                    return $this->fallbackLanguages();
                }

                $languages = collect($payload)
                    ->map(function (mixed $row): ?array {
                        if (!is_array($row)) {
                            return null;
                        }

                        $id = data_get($row, 'id');
                        $name = data_get($row, 'name');

                        if (!is_numeric($id) || !is_string($name) || trim($name) === '') {
                            return null;
                        }

                        return [
                            'id' => (int) $id,
                            'name' => trim($name),
                        ];
                    })
                    ->filter()
                    ->values()
                    ->all();

                return !empty($languages) ? $languages : $this->fallbackLanguages();
            } catch (\Throwable $e) {
                Log::warning('Judge0 languages fetch failed', ['message' => $e->getMessage()]);

                return $this->fallbackLanguages();
            }
        });
    }

    private function mapLanguageToId(string $language): int
    {
        $normalized = trim($language);

        if (is_numeric($normalized)) {
            return (int) $normalized;
        }

        return match (strtolower($language)) {
            'python', 'python3' => 71,
            'javascript', 'js' => 63,
            'java' => 62,
            'cpp', 'c++' => 54,
            'c' => 50,
            'go' => 60,
            'rust' => 73,
            'php' => 68,
            default => $this->resolveLanguageIdFromSupportedList($normalized) ?? 71,
        };
    }

    private function resolveLanguageIdFromSupportedList(string $language): ?int
    {
        if ($language === '') {
            return null;
        }

        $target = mb_strtolower($language);

        foreach ($this->getSupportedLanguages() as $supportedLanguage) {
            if (mb_strtolower((string) data_get($supportedLanguage, 'name')) === $target) {
                return (int) data_get($supportedLanguage, 'id');
            }
        }

        return null;
    }

    private function fallbackLanguages(): array
    {
        return [
            ['id' => 71, 'name' => 'Python (3.8.1)'],
            ['id' => 63, 'name' => 'JavaScript (Node.js 12.14.0)'],
            ['id' => 62, 'name' => 'Java (OpenJDK 13.0.1)'],
            ['id' => 54, 'name' => 'C++ (GCC 9.2.0)'],
            ['id' => 50, 'name' => 'C (GCC 9.2.0)'],
            ['id' => 60, 'name' => 'Go (1.13.5)'],
            ['id' => 73, 'name' => 'Rust (1.40.0)'],
            ['id' => 68, 'name' => 'PHP (7.4.1)'],
        ];
    }

    private function parseResponse(array $data): ExecutionResult
    {
        $statusId = (int) data_get($data, 'status.id', 0);
        $statusDescription = (string) data_get($data, 'status.description', 'Unknown');
        $output = data_get($data, 'stdout');
        $stderr = data_get($data, 'stderr');
        $compileOutput = data_get($data, 'compile_output');
        $message = data_get($data, 'message');

        $error = $stderr ?: ($compileOutput ?: $message);

        $status = $statusId === 3 ? 'success' : ($statusId === 5 ? 'timeout' : 'error');

        return new ExecutionResult(
            output: is_string($output) ? $output : null,
            error: is_string($error) ? $error : null,
            status: $status,
            executionTime: $this->normalizeExecutionTime(data_get($data, 'time')),
            externalStatus: $statusDescription,
        );
    }

    private function normalizeExecutionTime(mixed $time): ?int
    {
        if ($time === null || $time === '') {
            return null;
        }

        $floatSeconds = (float) $time;

        if ($floatSeconds < 0) {
            return null;
        }

        return (int) round($floatSeconds * 1000);
    }

    private function resolveSetting(string $key, ?string $fallback): ?string
    {
        try {
            if (!Schema::hasTable('system_settings')) {
                return $fallback;
            }

            $value = SystemSetting::getValue($key);

            return $value ?? $fallback;
        } catch (\Throwable) {
            return $fallback;
        }
    }

    private function decryptKey(string $encrypted): string
    {
        try {
            return Crypt::decryptString($encrypted);
        } catch (\Throwable) {
            return $encrypted;
        }
    }
}

class ExecutionResult
{
    public function __construct(
        public ?string $output = null,
        public ?string $error = null,
        public string $status = 'error',
        public ?int $executionTime = null,
        public ?string $externalStatus = null,
    ) {
    }

    public static function error(string $message): self
    {
        return new self(
            output: null,
            error: $message,
            status: 'error',
            executionTime: null,
            externalStatus: 'Error'
        );
    }

    public function isSuccess(): bool
    {
        return $this->status === 'success';
    }
}
