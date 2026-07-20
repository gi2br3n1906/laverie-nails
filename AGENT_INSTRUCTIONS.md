# AI Agent Execution Directives & Coding Standards

This document establishes the strict runtime behavior, coding style, and architectural compliance rules for all AI agents operating on this codebase. You must read and adhere to these constraints before generating any code.

---

## 1. Core Behavioral Constraints
- **Strict Scope Lock:** This is an academic research application restricted to manual millimeter measurements entered via web forms. Absolutely do not write, suggest, or scaffold any code related to camera APIs, computer vision libraries (OpenCV), or automated image processing.
- **No Hardcoded Mappings:** Hardcoding size charts in controllers, models, or Blade views is strictly forbidden. The single source of truth for standard measurements is the `size_standards` database table.
- **Test-Driven Development (TDD) Preference:** Every business logic function introduced inside the Service layer must be accompanied by comprehensive Unit/Feature tests (PHPUnit/Pest) before modifying routing or presentation layers.

---

## 2. Backend Coding Standards (PHP 8.2+ & Laravel 12)
- **Strict Typing:** Every single PHP file must begin with `declare(strict_types=1);` without exception.
- **Type Hinting:** Explicitly declare argument types and return types for all controller actions, service methods, and model functions. Use `void` for methods returning nothing.
- **Lightweight Controllers:** Controllers must only handle HTTP input validation, invoke the appropriate Application Service, and return a view or redirect response. No mathematical iteration or raw database updates are allowed inside controllers.
- **Form Requests:** All POST, PUT, and PATCH inputs must be validated using dedicated Laravel Form Request classes (`app/Http/Requests/`). Do not call `$request->validate()` directly inside controller methods.
- **Mass Assignment Protection:** Explicitly declare `$fillable` fields in all Eloquent models. Do not use `$guarded = []`.

---

## 3. Frontend Standards (Tailwind CSS 4 & Blade)
- **Utility-First Style:** Use native Tailwind CSS 4 utility classes exclusively. Braid styling details directly into the markup; inline style attributes (`style="..."`) are banned unless dynamically computing element coordinates/widths.
- **Component Isolation:** Move repetitive UI blocks (like measurement grids, navbar components, form rows) into clean, standalone Blade components (`resources/views/components/`) or partials.
- **Vanilla JavaScript Modules:** Keep frontend scripts modular. Avoid dumping global spaghetti code into `app.blade.php`. Use strict, scoped event listeners inside specific Blade layouts for interactive components like the right/left-hand data toggle.

---

## 4. Specific Security & Data Constraints
- **Data Encapsulation:** Always cast coordinate array payloads (like `right_hand_data` and `left_hand_data`) to standard arrays using Laravel 12 Eloquent JSON casting rules.
- **Enforced Context Binding:** Never create standalone measurement logs without evaluating record ownership policies. Standard users can only access rows matching their primary entity key.
- **Explicit Role Gates:** Protect structural Admin resources via route-level middleware checked against the authorized `admin` JSON role. The only role values are `user` and `admin`; never introduce third-party vendor roles or loose session flags.

---

## 5. Mathematical Enforcement Rules
When writing the algorithm loop within `NailClassifierService`, implement the matching rules explicitly:
1. Retrieve active metrics directly via database queries (`SizeStandard::where('is_active', true)->get()`).
2. Iterate through records and compute absolute variance bounds (L1 / Manhattan Distance) across all five fingers independently.
3. Apply the custom threshold cutoff exactly against the retrieved standard `tolerance` property.
4. Compute independent confidence percentages using the exact thesis mathematical formula:
   Confidence = max(0, 100 - (5 * SmallestDifference))