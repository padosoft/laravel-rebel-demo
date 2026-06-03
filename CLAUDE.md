# CLAUDE.md — AI working guide for `padosoft/laravel-rebel-demo`

> Working on this app with an AI agent (Claude Code, Cursor, Copilot, Codex)? Read this first.
> It's the "batteries" that make vibe-coding here land on the first try. Plain Markdown — every
> tool can read it.

## What this app is
The **integration demo** for the **Laravel Rebel** suite: a real Laravel application that installs,
activates and wires together the **entire `padosoft/laravel-rebel-*` ecosystem** in one app —
sharing one database, one session and one audit trail — and exercises every package end to end
(front-end in a real browser + back-end). This is the **guarantee gate**: proof the packages work
together, not just in their own unit tests.

This is an **app, not a package** — there's no library API to publish or tag. The job here is to
wire packages and demonstrate capabilities, then verify them in a browser (Playwright).

## Non-negotiable conventions
- `declare(strict_types=1);` in PHP; follow Laravel + Pint style (`./vendor/bin/pint`). **Docs and
  comments in English.**
- Reuse the suite's value objects/contracts from `padosoft/laravel-rebel-core` (identifiers,
  `SecurityContext`, `AuditLogger`, `KeyedHasher`) — don't reinvent them in the app.
- Security rules still apply: never store PII in cleartext (identifiers/IP/User-Agent are **keyed
  HMACs**), never log OTPs/secrets. Record demo events through the core `AuditLogger`.
- Tests live under `tests/`; capability demos must be **clickable** and exercisable in a real
  browser (Playwright), not faked.

## How to extend it
- **Add a capability demo:** a route in `routes/web.php` (the file already imports every rebel-*
  package — `SessionManager`, `RecoveryCodeManager`, `AnomalyDetector`, `VerificationRouter`,
  `RebelEmailOtp`, `RebelStepUp`, …) plus a Blade view under `resources/views/demo/`.
- **The login → session/device wiring** lives in `app/Providers/AppServiceProvider.php`: an
  `Event::listen(Login::class, …)` that calls `app(SessionManager::class)->start(...)` to register
  the session/device on every Fortify login. Extend this listener to demo new session/device flows.
- Demo context helpers (e.g. the `CF-IPCountry` header) come from
  `app/Http/Middleware/SetDemoCountryHeader.php`; the seeded users live via `app/Models/User.php`.
- When you add a capability, also wire it into the admin panel/links so it's reachable from the UI.

## Definition of Done (per change)
1. The capability is reachable and works in a real browser (Playwright), front-end **and** back-end.
2. `composer test` green; `./vendor/bin/pint` clean.
3. One feature branch, one PR to `main`. Update `README.md` to list the new demo.
4. This is an app — **no Composer tag/release** (unlike the packages in the suite).

## Skills
This repo ships invocable skills under `.claude/skills/` — at least `rebel-package-dev` (the suite's
dev loop, security/telemetry rules, and the PHPStan-max recipes used by the packages this app
installs). Invoke it before non-trivial work.
