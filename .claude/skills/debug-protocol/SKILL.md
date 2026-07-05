---
name: debug-protocol
description: "Use when debugging a failure, investigating unexpected behavior, isolating a bug, or tracing an error to its root cause."
---

# Debugging Protocol

> Rule: Do NOT build or modify the interface layer if the core engine is
> producing incorrect output. Isolate the failure first.

## 6.1. Isolation-First Strategy

```
Step 1 — Isolate the failing unit
  Run the failing model function in isolation with hardcoded test input via phpMyAdmin query tester.
  Print/log its raw output before touching anything else.
  Do not wrap it in a controller or view yet.

Step 2 — Verify the data contract at each phase boundary
  Confirm PHASE 1 output (validated upload data) matches expected schema before PHASE 2 runs.
  Confirm PHASE 2 output (song_id) matches expected schema before PHASE 3 runs.
  (See §1.2 for phase definitions.)

Step 3 — Attach the interface only after core output is verified correct
  Only then connect the verified model to the controller route and view.
```

## 6.2. Problem-Solving Matrix

```
If [EXPECTED_ERROR_1: Upload returns "invalid file type"]:
  → Trace to application/controllers/Admin_Song_controller.php::add_post()
  → Log raw MIME type detected by finfo_file() — the issue is likely non-audio file or wrong extension

If [EXPECTED_ERROR_2: Play limit not enforced]:
  → Dump session['play_count'] value in Player_controller.php::check_play_limit()
  → Verify CI3 session is enabled in application/config/config.php ($config['sess_enabled'] = TRUE)

If [EXPECTED_ERROR_3: Download returns 403 for registered user]:
  → The session check in Download_controller.php::index() is likely failing — check $this->session->userdata('user_id')

If [EXPECTED_ERROR_4: File not found on download]:
  → Run `ls -la protected_uploads/audio/` and verify file_path in songs table matches actual filename
  → Check schema.sql migration was applied: verify songs.file_path column exists
```

## 6.3. Logging Conventions

```
- Log levels  : error | warn | info | debug — use them semantically, not arbitrarily
- NEVER log   : passwords, tokens, secrets, full PII fields (see §18.6)
- ALWAYS log  : request IDs on errors, phase transition outputs during development
- Format      : Human-readable in dev (CI3 log_message() helper), structured JSON not required
- Log library : CI3 built-in Log class (application/logs/)
```
