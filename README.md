# SyncUp

A task management application composed of two independent packages:

- **`backend/`** — Laravel 13 RESTful API (PHP 8.3, SQLite, Sanctum token auth, Queue w/ database driver)
- **`frontend/`** — Vue 3 + Vite single-page application (Pinia, Vue Router)

---

## Quick Start

### Prerequisites

- PHP 8.3+ with Composer
- Node.js 20+ with npm

### Backend

```bash
cd backend
composer setup                    # Install deps, configure .env, run migrations
php artisan serve --port=8080     # Start the API server
```

See [`backend/README.md`](backend/README.md) for detailed backend documentation.

### Frontend

```bash
cd frontend
npm install
npm run dev                       # Starts Vite dev server (API proxy → :8080)
```

See [`frontend/README.md`](frontend/README.md) for detailed frontend documentation.

---

## Architecture

### API Endpoints

| Method | Path | Auth | Description |
|--------|------|------|-------------|
| POST | `/api/register` | — | Create a new user account |
| POST | `/api/login` | — | Authenticate and receive a Bearer token |
| GET | `/api/v1/users/me` | Sanctum | Get the authenticated user's profile |
| PATCH | `/api/v1/users/update` | Sanctum | Update the authenticated user's profile |
| DELETE | `/api/v1/users/delete/{id}` | Sanctum | Delete a user account |
| GET | `/api/v1/tasks/get` | Sanctum | List tasks (supports `?status=` and `?search=` filters) |
| POST | `/api/v1/tasks/create` | Sanctum | Create a new task |
| PATCH | `/api/v1/tasks/update/{id}` | Sanctum | Update a task (owner-only) |
| DELETE | `/api/v1/tasks/delete/{id}` | Sanctum | Delete a task (owner-only) |

### Key Design Decisions

- **Authentication:** Laravel Sanctum issues a `plainTextToken` on login. The client must send it as `Authorization: Bearer <token>` on all protected routes.
- **Task Statuses:** The enum `['pending', 'incomplete', 'completed']` maps to the frontend board columns Pending / In Progress / Completed.
- **Testing:** Backend runs PHPUnit against an in-memory SQLite database. Frontend runs Vitest with jsdom and mocked fetch.
- **Service Layer:** Backend business logic is encapsulated in `App\Http\Services\` — controllers delegate to services rather than implementing logic directly.

---

## Security Audit

13 vulnerabilities were identified and remediated in the initial audit:

| Severity | Count | Key Issues |
|----------|-------|------------|
| Critical | 2 | SQL injection in task LIKE clause, token leaked in registration response |
| High | 5 | Missing ownership checks, no rate limiting, user enumeration, token exposure |
| Medium | 4 | Unescaped wildcards, HTML error pages on API routes, SQLite type mismatches |
| Low | 2 | Missing auth middleware, obfuscated 500 errors |

Details are documented in the [backend README](backend/README.md#security-audit).

---

## Postman Testing

The repository includes `postman_collection.json` and `postman_environment.json` for API testing.

### Setup

1. **Import** `postman_collection.json` into Postman (File → Import → Upload Files)
2. **Import** `postman_environment.json` into Postman (creates environment "SyncUp API – Local")
3. Select the "SyncUp API – Local" environment from the Postman dropdown
4. Run `php artisan migrate:fresh` from `backend/` to reset the database
5. Start the server: `php artisan serve --port=8080`

### Environment Variables

| Variable | Default | Auto-saved By | Description |
|---|---|---|---|
| `scheme` | `http` | — | Protocol |
| `host` | `localhost` | — | Server host |
| `port` | `8080` | — | Server port |
| `auth_token` | — | Login valid credentials | Bearer token for authenticated requests |
| `user_email` | — | Register user (pre-request) | First user's email |
| `user_username` | — | Register user (pre-request) | First user's username |
| `user_id` | — | Register user / Get profile | First user's ID |
| `second_user_email` | — | Register second user (pre-request) | Second user's email |
| `second_user_id` | — | Register second user | Second user's ID |
| `task_id` | — | Create task | Task ID for CRUD operations |

### Collection Flow

```
Auth ──> Register user ──> Register duplicate email ──> Register weak password
    ──> Login valid credentials ──> Login wrong password ──> Login nonexistent email
Users ──> Get profile ──> Get profile (no auth) ──> Update profile ──> Register second user
Tasks ──> 11 task CRUD and filter/search operations
Cleanup ──> Delete other user (unauthorized) ──> Delete user (self)
```

> **Important:** Run requests in order — each depends on variables saved by the previous request. Run `migrate:fresh` before each collection run. Delete operations run last because self-deletion invalidates the Sanctum token.

---

## Known Caveats

- Root `.gitignore` blocks all `*.md` files except `README.md` — any new `.md` file at any level is ignored by git unless explicitly un-ignored.
- The `User` model has a `#[Fillable(['name', ...])]` attribute that is misleading; the actual `$fillable` array uses `'username'`.
- The `down()` method in the `create_task_table` migration drops `task` (singular) before `tasks` — only `tasks` (plural) is created by `up()`.
- No CORS configuration is present. If the frontend is served from a different origin, configure Sanctum stateful domains or add CORS middleware.
- The login validation error message is hardcoded as `'These credentials do not match our records.'` (matching Postman test expectations) rather than using `__('auth.failed')`.
- SQLite returns integer columns as strings — all ownership comparisons use an explicit `(int)` cast.
- The exception handler in `bootstrap/app.php` forces JSON responses for all `/api/*` routes and returns `401` JSON for `AuthenticationException`.
- The `throttle:login` rate limiter (5 requests per minute per email+IP) is defined in `AppServiceProvider::boot()`.
