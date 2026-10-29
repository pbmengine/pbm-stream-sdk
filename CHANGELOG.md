# Changelog

All notable changes to `pbm-stream-sdk` will be documented in this file

## 2.3.0 - 2026-10-29

- support Laravel 13 (illuminate ^13.0)
- ⚠️ require PHP ^8.2 and Laravel ^12.0 (dropped PHP 7.4–8.1 and Laravel 7–11, which are EOL)
- security: percent-encode all URL path segments (project, collection, event id, field, value) to prevent path traversal to other API endpoints
- require `guzzlehttp/guzzle` explicitly (used by the HTTP client)
- throw a `LogicException` instead of sending a malformed request when no collection is set
- accept `CarbonImmutable` / any `CarbonInterface` in `Query::timeFrame()`
- relative time frames no longer break when the app uses immutable dates (`Date::use()`)
- `declare(strict_types=1)` and PHPStan level 6
- test suite migrated to Pest, GitHub Actions matrix for PHP 8.2–8.4 / Laravel 12–13

## 1.0.0 - 2021-11-30

- initial release

## 1.4.0 - 2022-02-25

- support illuminate ^9.0
