---
name: testing-protocol
description: "Use when writing test cases, designing test coverage, or verifying use cases during STEP 7."
---

# Testing Protocols

## 14.1. Coverage Targets

```text
  Unit test coverage   : N/A — academic project, PHPUnit optional
  Integration coverage : All UCs (UC-01 to UC-25) have at least one success + one failure manual test
  E2E / Vision UI      : Critical user flows only: guest play/download limit, registered user playlist/favorites, admin song CRUD

  Coverage is measured on: N/A — manual testing only
  Coverage is NOT required on: UI components, migration files
```

## 14.2. Test Structure Rules

```text
- Test cases mirror the UC table:
    UC-08 (Guest Play) → tests/uc-08-guest-play-limit.md

- Each test file MUST contain:
    - At least one "happy path" test (guest plays 3 songs → allow)
    - At least one "invalid input" test (guest plays 4th song → deny modal)
    - At least one "edge case" test (session reset → play_count = 0)

- Test names MUST describe behavior, not implementation:
    ✓ "blocks 4th play attempt with registration modal"
    ✗ "test check_play_limit play_count >= 3 branch"
```

## 14.3. Mocking Rules

```text
MOCK in unit tests  : N/A — no external HTTP requests, file system, or time mocking required
DO NOT MOCK         : The model function under test. Business logic in models being tested.

REAL in integration : Database (use XAMPP dev DB), internal controller calls
MOCK in integration : N/A — no third-party APIs

Test database rules :
  - Use the same dev database (XAMPP local) — no separate test DB
  - Seed fresh data before each test (manual INSERT via phpMyAdmin)
  - Tear down after tests — DELETE from all tables between test cases
```

## 14.4. Test Framework & Tools

```text
  Unit / Integration : N/A — manual browser testing only
  E2E / Vision UI    : Manual browser testing (Chrome/Firefox)
  Mocking library    : N/A
  Test DB utility    : phpMyAdmin (manual SQL)
```
