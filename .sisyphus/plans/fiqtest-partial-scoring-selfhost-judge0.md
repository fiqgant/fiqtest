# Plan: Partial Scoring + Self-Hosted Judge0 (fiqtest)

## Objective
Implement proportional partial scoring per test case and migrate execution from external Judge0 API style to self-hosted Judge0, with deployment assets under `judge0/` and production-safe operations.

## Confirmed Inputs
- Scoring model: proportional (`score = weight * passed_tests / total_tests`)
- Keep `is_correct = true` only when all test cases pass
- Judge0 target: self-hosted in production
- Workspace preference: create root folder `judge0/`

## Constraints
- Existing app stack remains Laravel + MySQL + Blade + Tailwind + Alpine + Monaco
- No destructive schema/data operations without migration path
- Shared-hosting-friendly baseline no longer applies to code runner; runner becomes separate infra subsystem

## Current State Snapshot
- Grading is all-or-nothing in `app/Services/GradingService.php`
- Run preview uses first visible test case in `app/Http/Controllers/Exam/ExamWorkspaceController.php`
- Judge0 client exists at `app/Services/Judge0Service.php` with configurable base URL and optional RapidAPI headers
- PHPUnit configured (`phpunit.xml`), but coverage for grading/Judge0 is minimal; CI workflow not present

## Implementation Workstreams

### W1 — Partial Scoring Engine
1. Update grading algorithm in `app/Services/GradingService.php`:
   - compute `passed_tests`, `total_tests`
   - compute `score = round(weight * passed_tests / total_tests, 2)` when `total_tests > 0`
   - score `0` when `total_tests = 0`
   - set `is_correct = (passed_tests === total_tests && total_tests > 0)`
2. Avoid repeated total updates inside per-question loop:
   - grade each question first, then call `updateAttemptTotal` once in `gradeAllQuestions`
3. Preserve compatibility with existing result pages by retaining `score` and `is_correct` semantics.

QA:
- Command: `php artisan test --filter=GradingServiceTest`
- Steps: run full-pass, partial-pass, zero-pass fixtures
- Expected: score equals rounded proportional formula; `is_correct=true` only on full pass

### W2 — Store Per-Question Pass Detail
1. Migration: add columns to `attempt_questions`:
   - `passed_tests` (unsigned integer, nullable/default 0)
   - `total_tests` (unsigned integer, nullable/default 0)
2. Update model fill/update flow to persist both values from grading.
3. Update result/admin views to show pass ratio (`passed_tests/total_tests`) where available.

QA:
- Command: `php artisan test --filter=FinalSubmitPartialScoringTest`
- Steps: submit attempt with mixed pass outcomes
- Expected: DB row in `attempt_questions` contains non-null `passed_tests`, `total_tests`, and score consistent with ratio

### W3 — Self-Hosted Judge0 Deployment Assets (`judge0/`)
1. Add `judge0/docker-compose.yml` for production-like stack:
   - Judge0 API
   - Judge0 workers
   - Postgres (Judge0 datastore)
   - Redis (job queue)
2. Add `judge0/.env.example`:
   - internal service ports
   - DB credentials
   - worker/resource constraints
3. Add `judge0/README.md` runbook:
   - bootstrap, health checks, logs, upgrade, rollback
4. Add reverse-proxy template in `judge0/nginx/` for internal-only exposure.

QA:
- Command: `docker compose -f judge0/docker-compose.yml up -d`
- Steps: start services, inspect logs, then run concrete endpoint checks
- Command: `curl -sS http://localhost:2358/languages | head -c 200`
- Expected: non-empty JSON payload containing language objects
- Command: `curl -sS -X POST "http://localhost:2358/submissions?base64_encoded=false&wait=true" -H "Content-Type: application/json" -d '{"source_code":"print(1+1)","language_id":71,"stdin":""}'`
- Expected: JSON response with `status` and `stdout` containing `2` (or equivalent success status)
- Command: `docker compose -f judge0/docker-compose.yml ps`
- Expected: API/worker/db/redis services show `running` / healthy state

### W4 — Laravel-to-Runner Integration Hardening
1. Update `app/Services/Judge0Service.php` to support both header modes:
   - self-hosted auth header (e.g., `Authorization: Bearer ...`) via settings
   - optional RapidAPI headers only when configured
2. Add resilient request behavior:
   - strict timeout
   - clearer error mapping for network/5xx/timeout
3. Add idempotency/correlation support in logs:
   - attach request ID when calling Judge0
   - log status and latency consistently

QA:
- Command: `php artisan test --filter=Judge0ServiceTest`
- Steps: run mocked success, timeout, and non-200 scenarios
- Expected: mapped statuses are correct (`success/error/timeout`) and errors are user-safe

### W5 — Admin Settings Extension
1. Extend settings page `resources/views/admin/settings/judge0.blade.php`:
   - auth mode selector (none / bearer / rapidapi)
   - base URL validation for internal endpoint
2. Update `app/Http/Controllers/Admin/SystemSettingController.php`:
   - validate/store new auth-mode fields
   - encrypt secret values
3. Add safe defaults to `config/services.php` and `.env.example`.

QA:
- Command: `php artisan test --filter=SystemSetting`
- Steps: save settings in each auth mode; reload service config
- Expected: persisted values are validated/encrypted and used by `Judge0Service` without manual code edits

### W6 — Tests (Required)
1. Unit tests:
   - `tests/Unit/GradingServiceTest.php`
     - full pass = full score
     - partial pass = fractional score
     - zero pass = 0
     - zero test-cases = 0, not correct
   - `tests/Unit/Judge0ServiceTest.php`
     - success parse
     - timeout/error mapping
     - non-200 behavior
2. Feature tests:
   - `tests/Feature/ExamWorkspaceRunCodeTest.php`
     - submission persisted correctly from mocked Judge0 response
   - `tests/Feature/FinalSubmitPartialScoringTest.php`
     - final submit updates per-question score and attempt totals
3. Mock external HTTP with `Http::fake()` in all runner-related tests.

QA:
- Command: `php artisan test`
- Expected: all new/old tests pass with zero failures and deterministic no-network execution

### W7 — CI
1. Add `.github/workflows/php-tests.yml`:
   - PHP setup
   - composer install
   - `php artisan test`
2. Keep sqlite in-memory test strategy from `phpunit.xml`.

QA:
- Command: `git push <remote> <branch>` then open PR in GitHub
- Steps: verify GitHub Actions check named `php-tests` executes on PR
- Expected: workflow installs PHP/composer deps and ends green with successful `php artisan test`

## Verification Gate (Must Pass)
1. `lsp_diagnostics` clean for all modified PHP files
2. `php artisan test` all green
3. `php artisan route:list` green
4. Runner connectivity smoke test:
   - Laravel `runCode` endpoint returns success against self-hosted Judge0
5. Manual scoring check:
   - known 50% passing solution yields 50% of question weight

## Rollout Strategy
1. Deploy DB migration first
2. Deploy app code with backward-compatible display logic
3. Bring up `judge0/` stack and verify health
4. Switch admin Judge0 settings to internal endpoint/auth mode
5. Run smoke tests, then enable for live exams

## Rollback Strategy
1. Repoint Judge0 settings to prior endpoint (or fallback provider)
2. Revert app release if scoring defects are found
3. Keep additive migration columns (no destructive rollback during active exam window)

## Risks and Mitigations
- Runner overload -> enforce queue/concurrency limits and request throttling
- Sandbox risk -> isolate runner network/host and apply strict resource caps
- Grading drift -> lock tests around partial scoring scenarios and golden cases
- Infra fragility -> keep clear runbook + versioned compose images + rollback steps

## Execution Order (Recommended)
1. W1 + W2 (core scoring behavior)
2. W6 (tests for scoring)
3. W3 + W4 + W5 (self-hosted runner + integration)
4. W6 runner-related tests
5. W7 CI
6. Verification gate + rollout
