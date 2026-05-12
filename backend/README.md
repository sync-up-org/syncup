# SyncUp — Backend API

A Laravel 13 RESTful API that powers the SyncUp task management platform. Built with SQLite for local development, Sanctum for token-based authentication, and follows a service-layer architecture pattern.

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Framework | Laravel 13 |
| PHP Version | 8.3 |
| Database | SQLite (production-ready: any Laravel-supported driver) |
| Authentication | Laravel Sanctum (token-based) |
| Queue / Cache / Session | Database driver |
| Testing | PHPUnit (in-memory SQLite) |
| Code Style | Laravel Pint (default rules) |

---

## Quick Start

### Prerequisites

- PHP 8.3+ with Composer
- SQLite3 (bundled with PHP)

### Setup

```bash
composer setup
```

This single command runs the full bootstrap:
1. Installs Composer dependencies
2. Copies `.env.example` to `.env`
3. Generates an application key
4. Runs database migrations
5. Installs and builds frontend dependencies (when applicable)

### Development Server

```bash
php artisan serve --port=8080
```

For concurrent processes (server + queue + logs + Vite):

```bash
composer dev
```

---

## Available Commands

All commands run from the `backend/` directory:

| Command | Description |
|---------|-------------|
| `composer setup` | Full project bootstrap |
| `composer dev` | Start server, queue listener, log watcher, and Vite concurrently |
| `composer test` | `config:clear` + `php artisan test` |
| `./vendor/bin/pint` | Format code with Laravel Pint (default rules, no config file) |
| `php artisan serve --port=8080` | Start the development HTTP server |
| `php artisan migrate:fresh` | Drop all tables and re-run migrations |
| `php artisan queue:listen` | Process queued jobs (required for database queue driver) |

---

## Architecture

### Directory Structure

```
app/
├── Http/
│   ├── Controllers/     # Thin controllers — validation + delegate to services
│   │   ├── AuthController.php
│   │   ├── TaskController.php
│   │   └── UserController.php
│   ├── Requests/         # Form request validation
│   │   └── StoreTaskRequest.php
│   └── Resources/        # API resource transformations
│       └── TaskResource.php
├── Http/Services/        # Business logic layer
│   └── TaskService.php
├── Models/               # Eloquent models
│   ├── User.php
│   └── Tasks.php
└── Providers/
    └── AppServiceProvider.php   # Rate limiter definitions, environment config
```

### API Endpoints

| Method | Path | Auth | Description |
|--------|------|------|-------------|
| POST | `/api/register` | — | Create account (`username`, `email`, `password`) |
| POST | `/api/login` | — | Authenticate, returns `token` + `token_type` (rate-limited: 5/min) |
| GET | `/api/v1/users/me` | Sanctum | Get authenticated user profile |
| PATCH | `/api/v1/users/update` | Sanctum | Update profile (`username`, `email`) |
| DELETE | `/api/v1/users/delete/{id}` | Sanctum | Delete account (self only) |
| GET | `/api/v1/tasks/get` | Sanctum | List tasks (supports `?status=pending&search=term`) |
| POST | `/api/v1/tasks/create` | Sanctum | Create task (`title`, `description`) |
| PATCH | `/api/v1/tasks/update/{id}` | Sanctum | Update task (owner-only) |
| DELETE | `/api/v1/tasks/delete/{id}` | Sanctum | Delete task (owner-only) |

### Response Envelope

**Success:**
```json
{
  "success": true,
  "message": "Logged in successfully",
  "token": "1|abc123...",
  "token_type": "Bearer"
}
```

**Error (validation / business logic):**
```json
{
  "message": "These credentials do not match our records.",
  "errors": { "email": ["These credentials do not match our records."] }
}
```

**Error (unauthenticated):**
```json
{
  "message": "Unauthenticated."
}
```

---

## Security Audit

13 vulnerabilities were identified and remediated in an initial security audit. The following table documents each finding and its resolution.

| ID | Severity | Issue | Resolution |
|----|----------|-------|-----------|
| C-01 | **Critical** | SQL injection via unescaped `LIKE` clause in task search | Validated `search` parameter and escaped `%`/`_` wildcards with `str_replace()` |
| C-02 | **Critical** | Token leaked in registration response due to `#[VisibleFor('*')]` on `UserResource` | Replaced implicit model exposure with explicit JSON response (no `UserResource`) |
| H-01 | **High** | Sanctum token embedded inside `user` object in login response | Separated into dedicated `token` and `token_type` fields |
| H-02 | **High** | No rate limiting on login endpoint | Added `throttle:login` — 5 requests per minute per email+IP |
| H-03 | **High** | User enumeration via distinct error messages on duplicate registration | Consolidated to generic `'Registration failed. Please check your input.'` |
| H-04 | **High** | `TaskController::index()` returned all users' tasks | Scoped query to `$request->user()->tasks()` |
| H-05 | **High** | No ownership validation on `TaskController::update()` and `destroy()` | Added `(int) $task->user_id !== (int) Auth::id()` checks |
| M-01 | **Medium** | Wildcard characters `%`/`_` not escaped in SQL `LIKE` | Escaped with `str_replace()` before building the LIKE pattern |
| M-02 | **Medium** | `AuthenticationException` rendered HTML instead of 401 JSON | Configured `shouldRenderJsonWhen()` and custom `AuthenticationException` renderer in `bootstrap/app.php` |
| M-03 | **Medium** | `ValidationException` rendered HTML on API routes | Same `shouldRenderJsonWhen()` fix forces JSON for `/api/*` routes |
| M-04 | **Medium** | SQLite returned string IDs causing `!==` to fail in ownership checks | All comparisons use explicit `(int)` cast |
| L-01 | **Low** | User deletion route lacked explicit `auth:sanctum` middleware | Route was already inside the `auth:sanctum` group, but scope was made explicit |
| L-02 | **Low** | Generic `catch` returned 500 with obfuscated message | Removed `debug` field from production error response |

---

## Known Caveats

- The `User` model `#[Fillable]` attribute lists `'name'` but the actual `$fillable` array uses `'username'`. The attribute is misleading; always trust the `$fillable` array.
- The `down()` method in the `create_task_table` migration drops `task` (singular) before `tasks` (plural). Only `tasks` is created by `up()`.
- Login validation error is hardcoded as `'These credentials do not match our records.'` rather than using `__('auth.failed')`. This matches Postman test expectations.
- No CORS configuration is present. Add Sanctum stateful domains or custom CORS middleware when serving the frontend from a different origin.
- The exception handler in `bootstrap/app.php` forces JSON responses for all `/api/*` routes and returns `401` JSON for `AuthenticationException`.
- The `throttle:login` rate limiter (5 requests/minute per email+IP) is defined in `AppServiceProvider::boot()`.

---

## Testing

```bash
composer test
```

Runs all PHPUnit tests against an in-memory SQLite database:

| Test Suite | Tests | Scope |
|------------|-------|-------|
| `AuthTest` | 9 | Registration, duplicate prevention, weak password rejection, login, rate limiting |
| `TaskTest` | 14 | CRUD, ownership enforcement, status validation, search, wildcard escaping |
| `UserTest` | 7 | Profile retrieval, update, self-deletion, cross-user authorization |
| **Total** | **32** | Full coverage of all API endpoints and security boundaries |
