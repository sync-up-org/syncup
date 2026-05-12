# SyncUp

Two independent packages — no shared monorepo tooling:

- **`backend/`** — Laravel 13 API (PHP 8.3, SQLite, Sanctum token auth, Queue w/ database driver)
- **`frontend/`** — standalone Vue 3 + Vite SPA (not wired to the backend yet)

They ship separately. The root is just a container.

## Commands

All backend commands run from `backend/`:

```bash
composer setup   # full bootstrap: install deps, copy .env, key:generate, migrate, npm ci + build
composer dev     # starts 4 processes: server, queue:listen, pail (logs), vite dev
composer test    # config:clear then php artisan test
./vendor/bin/pint          # PHP CS fixer (Laravel Pint, no pint.json — uses defaults)
```

Frontend (from `frontend/`):

```bash
npm run dev       # Vite dev server
npm run build     # production build
npm run preview   # preview production build
```

## Architecture

- **API prefix:** `/api/v1/` for auth-protected routes; `/api/register` and `/api/login` are public.
- **Auth:** Laravel Sanctum. Login returns a `plainTextToken` — store and send as `Authorization: Bearer <token>`.
- **Database:** SQLite at `backend/database/database.sqlite`. The `.env.example` uses `DB_CONNECTION=sqlite` with no explicit path (Laravel defaults to `database/database.sqlite`). Tests use `:memory:`.
- **Queue/cache/session:** all use the `database` driver — must run `php artisan queue:listen` for queued jobs.
- **Service layer:** business logic lives in `app/Http/Services/` (e.g., `TaskService`). Controllers call services.
- **Frontend is not integrated** with Laravel's Vite or Blade. It's a standalone Vite project.

## Security Audit Summary

13 vulnerabilities were identified and fixed (2 Critical, 5 High, 4 Medium, 2 Low):

| ID | Severity | Issue | File |
|---|---|---|---|
| C-01 | Critical | SQL injection via LIKE clause in task search | `TaskController.php:33` |
| C-02 | Critical | Token leaked in registration response (`#[VisibleFor('*')]`) | `UserResource.php` |
| H-01 | High | Token response format — token embedded in user object | `AuthController.php:34` |
| H-02 | High | No rate limiting on login endpoint | `api.php`, `AppServiceProvider.php` |
| H-03 | High | User enumeration via register error messages | `UserController.php:33-38` |
| H-04 | High | Task ownership not enforced in `index()` | `TaskController.php:25` |
| H-05 | High | No ownership check on task `update()`/`destroy()` | `TaskController.php:67,102` |
| M-01 | Medium | Wildcard characters unescaped in LIKE clause | `TaskController.php:32` |
| M-02 | Medium | Auth exception renders HTML instead of 401 JSON | `bootstrap/app.php` |
| M-03 | Medium | ValidationException renders HTML for API routes | `bootstrap/app.php` |
| M-04 | Medium | SQLite string ID type mismatch in ownership checks | `UserController.php:91`, `TaskController.php:67,102` |
| L-01 | Low | User deletion not scoped — `auth:sanctum` middleware missing | `UserController.php:89` (was inside group) |
| L-02 | Low | Generic catch returning 500 with obfuscated cause | `TaskController.php:88-93` |

## Postman Testing

The repo includes `postman_collection.json` and `postman_environment.json` for replication.

### Setup
1. **Import** `postman_collection.json` into Postman (File → Import → Upload Files)
2. **Import** `postman_environment.json` into Postman (same process; creates environment "SyncUp API – Local")
3. Select the "SyncUp API – Local" environment from the dropdown in Postman
4. Run `php artisan migrate:fresh` from `backend/` to reset the database
5. Start the server: `php artisan serve --port=8080`

### Environment variables
| Variable | Default | Auto-saved by | Description |
|---|---|---|---|
| `scheme` | `http` | — | Protocol |
| `host` | `localhost` | — | Server host |
| `port` | `8080` | — | Server port |
| `auth_token` | empty | Login valid credentials | Bearer token for auth |
| `user_email` | empty | Register user (prerequest) | First user's email |
| `user_username` | empty | Register user (prerequest) | First user's username |
| `user_id` | empty | Register user or Get profile | First user's ID |
| `second_user_email` | empty | Register second user (prerequest) | Second user's email |
| `second_user_id` | empty | Register second user | Second user's ID |
| `task_id` | empty | Create task | Task ID for CRUD ops |

### Collection flow
Auth → Register user → Register duplicate email → Register weak password → Login valid credentials → Login wrong password → Login nonexistent email → Users → Get profile → Get profile (no auth) → Update profile → Register second user → Tasks → (11 task operations) → Cleanup → Delete other user (unauthorized) → Delete user (self)

### Important
- Run requests **in order** — each depends on variables saved by the previous request
- Run `php artisan migrate:fresh` before each collection run — stale data causes unpredictable IDs and authorization failures
- Delete operations run **last** (Cleanup folder) because self-deletion invalidates the Sanctum token. All task operations run while the token is valid

## Gotchas

- Root `.gitignore` blocks all `*.md` files except `README.md`. Any new `.md` file at any level will be ignored by git unless explicitly unignored.
- `User` model has an attribute `#[Fillable(['name', ...])]` but `$fillable` uses `'username'`. The attribute is misleading; the actual fillable is the `$fillable` array.
- The `down()` method in `create_task_table` migration drops `task` (singular) before `tasks` — only `tasks` (plural) is created by `up()`.
- No CORS config file — if the frontend calls the API from a different origin, you'll need to handle CORS (either via Sanctum stateful domains or custom middleware).
- Login validation error is hardcoded (`'These credentials do not match our records.'`) instead of using `__('auth.failed')` — matches Postman test expectations.
- SQLite returns integer columns as strings — all ownership comparisons use explicit `(int)` cast to handle this.
- Exception handler in `bootstrap/app.php` forces JSON for all `/api/*` routes and handles `AuthenticationException` with `401` JSON.
- `throttle:login` rate limiter (5/min per email+IP) is defined in `AppServiceProvider::boot()`.
