# Contributing to SyncUp

Thank you for your interest in contributing to SyncUp. This document outlines the process for contributing to the project.

## Development Workflow

1. Fork the repository and create a feature branch from `main`
2. Make your changes following the existing code conventions
3. Write or update tests to cover your changes
4. Run the full test suite to ensure nothing is broken
5. Submit a pull request with a clear description of the changes

## Code Style

### Backend (PHP / Laravel)

```bash
cd backend
./vendor/bin/pint
```

Laravel Pint enforces PSR-12 coding standards with the framework's default ruleset. Run it before committing.

### Frontend (JavaScript / Vue)

- Follow the existing component patterns (Composition API, `<script setup>`)
- Use Vue's `defineProps` / `defineEmits` macros for type-safe component interfaces
- Keep components focused and extract reusable logic into composables or stores

## Testing

All changes must be accompanied by tests.

### Backend

```bash
cd backend
composer test
```

- 32 PHPUnit tests covering Auth, Task, and User endpoints
- Tests run against an in-memory SQLite database

### Frontend

```bash
cd frontend
npm test
```

- 38 Vitest tests covering the API client, Pinia stores, and Vue components
- Tests run in jsdom with mocked HTTP

## Pull Request Guidelines

- Use a descriptive title following conventional commit format: `type(scope): description`
  - Types: `feat`, `fix`, `refactor`, `docs`, `test`, `chore`
  - Examples: `feat(backend): add task filtering`, `fix(frontend): debounce search input`
- Keep pull requests focused on a single concern
- Update relevant documentation if your changes affect the API surface or setup process
- Verify the security audit checklist if your changes touch authentication or authorization

## Security

If you discover a security vulnerability, please follow the instructions in [`SECURITY.md`](SECURITY.md) rather than opening a public issue.
