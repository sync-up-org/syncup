# SyncUp — Frontend

A Vue 3 single-page application that provides the user interface for the SyncUp task management platform. Built with Pinia for state management and Vue Router for client-side navigation.

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Framework | Vue 3 (Composition API, `<script setup>`) |
| Build Tool | Vite 8 |
| State Management | Pinia 2 |
| Routing | Vue Router 4 |
| HTTP Client | Native `fetch()` (no Axios dependency) |
| Testing | Vitest + @vue/test-utils + jsdom |

---

## Getting Started

### Prerequisites

- Node.js 20+ with npm

### Installation

```bash
npm install
```

### Development

```bash
npm run dev
```

Starts the Vite development server (default port 5173). The Vite config proxies `/api` requests to `http://localhost:8080`, so the Laravel backend must be running on port 8080 for API calls to work.

### Production Build

```bash
npm run build     # Outputs to dist/
npm run preview   # Preview the production build locally
```

---

## Project Structure

```
src/
├── api/              # HTTP client layer
│   └── index.js      #   Centralized fetch wrapper with auth token injection
├── components/       # Vue SFC components
│   ├── LoginView.vue
│   ├── RegisterView.vue
│   ├── MainView.vue
│   ├── AppHeader.vue
│   ├── TaskBoard.vue
│   ├── TaskCard.vue
│   ├── AddTaskModal.vue
│   ├── SearchBar.vue
│   └── UserProfileModal.vue
├── router/           # Client-side routing
│   └── index.js      #   Route definitions with lazy loading and auth guards
├── store/            # Pinia stores
│   └── index.js      #   authStore + taskStore with real API integration
├── styles/           # Scoped CSS files
│   ├── LoginView.css
│   ├── RegisterView.css
│   ├── MainView.css
│   ├── AppHeader.css
│   ├── TaskBoard.css
│   ├── TaskCard.css
│   ├── AddTaskModal.css
│   ├── SearchBar.css
│   └── UserProfileModal.css
├── __tests__/        # Test suite
│   ├── api.test.js
│   ├── store.test.js
│   ├── components.test.js
│   └── setup.js
├── App.vue           # Root component (router-view + auth check)
├── main.js           # Application entry point
└── style.css         # Global CSS custom properties (light/dark theme)
```

---

## Architecture

### Data Flow

```
Component ──> Pinia Store ──> API Client ──> fetch() ──> Laravel Backend
                      ^                        │
                      └──── localStorage ◄─────┘  (auth token persistence)
```

- **API Client** (`src/api/index.js`): Wraps `fetch()` with automatic auth token injection from `localStorage`, JSON serialization, and error handling. Provides typed methods for every backend endpoint.
- **Stores** (`src/store/index.js`):
  - **authStore**: Manages authentication state — `register()`, `login()`, `fetchProfile()`, `updateProfile()`, `deleteUser()`, `logout()`. Persists the Bearer token in `localStorage`.
  - **taskStore**: Manages task CRUD operations — `fetchTasks()`, `addTask()`, `updateTask()`, `deleteTask()`, `setSearch()`. Maintains an in-memory task list with loading indicator.
- **Router** (`src/router/index.js`): Three routes — `/login`, `/register`, `/app` (protected). The `beforeEach` guard redirects unauthenticated users to `/login`.
- **Vite Proxy** (`vite.config.js`): Forwards `/api/*` requests to the Laravel backend during development, eliminating CORS issues.

### Component Tree

```
App.vue
├── LoginView.vue        (/login)
├── RegisterView.vue     (/register)
└── MainView.vue         (/app)
    ├── AppHeader.vue
    ├── SearchBar.vue        (conditional)
    ├── TaskBoard.vue
    │   └── TaskCard.vue     (× N)
    ├── AddTaskModal.vue     (conditional, teleported to body)
    └── UserProfileModal.vue (conditional, teleported to body)
```

---

## Testing

```bash
npm test             # Run all tests (single run)
npm run test:watch   # Run tests in watch mode
```

The test suite covers:

| Test File | Scope | Tests |
|-----------|-------|-------|
| `api.test.js` | API client: request formation, auth headers, query params, error handling | 10 |
| `store.test.js` | Stores: auth lifecycle, token persistence, task CRUD, search | 15 |
| `components.test.js` | Components: rendering, form validation, events, user interactions | 13 |
| **Total** | | **38** |

Tests use mocked `fetch()` via Vitest and jsdom for a browser-like environment. A setup file provides `localStorage` stubs for auth persistence tests.

---

## Scripts Reference

| Script | Description |
|--------|-------------|
| `npm run dev` | Start Vite development server with HMR |
| `npm run build` | Build for production (outputs to `dist/`) |
| `npm run preview` | Preview the production build |
| `npm test` | Run all Vitest tests |
| `npm run test:watch` | Run Vitest in watch mode |
