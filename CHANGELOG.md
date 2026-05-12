# Changelog

All notable changes to this project are documented in this file.

## [1.0.0] — 2026-05-13

### Added

- **Backend:** Laravel 13 RESTful API with Sanctum token authentication
- **Frontend:** Vue 3 + Vite single-page application
- **Security audit:** 13 vulnerabilities identified and remediated (2 Critical, 5 High, 4 Medium, 2 Low)
- **Authentication:** Registration, login with rate limiting (5/min), session management
- **Task management:** Full CRUD with ownership enforcement, status transitions, search with wildcard escaping
- **User management:** Profile retrieval, update, self-deletion
- **Testing:** 32 PHPUnit tests (backend) + 38 Vitest tests (frontend)
- **API testing:** Postman collection with 23 requests and environment configuration
- **Architecture:** Service layer pattern, API resource transformations, form request validation
- **Tooling:** Vite proxy configuration, Laravel Pint, IDE helper support
- **UI/UX redesign:** Split-screen auth pages with embedded logo SVG, staggered animations
- **Drag and drop:** Task cards draggable between status columns with optimistic updates
- **Design system:** OKLCH color palette, custom easing curves, semantic 4pt spacing scale
- **Responsive layout:** Mobile-first breakpoints with column stacking, icon-only nav on small screens
- **Favicon:** Custom abstract sync-arrow SVG for all modern browsers
- **Error handling:** Graceful JSON parse error recovery in API client
- **Password visibility:** Toggle button on all password fields
