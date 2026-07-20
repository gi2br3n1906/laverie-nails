# Technical Blueprint & Coding Standards

## Stack
- **Framework:** Laravel 12
- **Frontend:** Blade, Tailwind CSS 4, Vite 7
- **Database:** SQLite (development), MySQL (production compatible)

## Architectural Principles
1. **Single Source of Truth:** Size standards must reside in the database (`size_standards` table). Logic must query this table, NOT hardcode values.
2. **Separation of Concerns:**
   - Controller: HTTP input handling.
   - Service Layer: Classification business logic.
   - Models: Data structure and relationships.
3. **Security:**
   - Middleware-based Authorization (Admin and User only).
   - Policy-based record ownership (users can only access their own measurements).
4. **Validation:** Use Laravel Form Requests with strict numeric constraints (min: 0, max: 25).