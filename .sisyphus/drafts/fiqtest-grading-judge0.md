# Draft: fiqtest partial scoring + Judge0 setup

## Requirements (confirmed)
- User wants immediate continuation: "yaudah langsung kamu buatlah"
- User asks for partial-credit behavior: "setengah benar"
- User selected scoring model: proportional per test case
- User requests Judge0 to be "installed in project" for direct testing
- User preference for Judge0 location: local setup in same workspace under new folder `judge0`

## Technical Decisions
- Current grading is all-or-nothing for final submit (full pass only gets weight)
- Proposed target model: `score = weight * (passed_tests / total_tests)`
- Keep `is_correct = true` only when 100% test cases pass

## Research Findings
- `app/Services/GradingService.php`: currently compares expected vs actual per test case and sets score to 0 unless all pass
- `app/Http/Controllers/Exam/ExamWorkspaceController.php`: Run Code uses only the first visible test case for preview
- `app/Services/Judge0Service.php`: integration is external API call to Judge0 endpoint
- Oracle architecture consultation completed (`ses_30358c772ffeGK0pd13AFpDWGK`): recommends private self-hosted Judge0 subsystem, internal-only networking, strong service auth, backpressure/circuit breaker, and strict resource caps
- Test infrastructure assessment completed (`ses_30357bb61ffepl5pUr95q4ymoI`): PHPUnit present, sqlite in-memory test env, minimal current feature coverage, no Judge0/Grading unit tests, no CI workflow files

## Confirmed Decisions
- Scoring policy target: proportional per test case (`score = weight * passed/total`)
- Judge0 deployment target: self-hosted for production
- Judge0 local workspace preference: create folder `judge0/` in same repository root for deployment assets

## Scope Boundaries
- INCLUDE: scoring-policy update plan, self-hosted Judge0 deployment plan (`judge0/`), Laravel integration migration plan, testing/CI plan, rollout and rollback plan
- EXCLUDE: immediate code implementation in this planning turn

## Open Questions
- None currently blocking planning. Proceed with full implementation-ready plan.
