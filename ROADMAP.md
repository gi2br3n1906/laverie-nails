# Comprehensive Development Roadmap: Press-On Nail Measurement System

This document maps out the precise, step-by-step technical implementation stages for the AI Agent. It strictly follows the academic Research & Development (R&D) 4D methodology (Define, Design, Develop, Disseminate) established in the thesis scope[cite: 1].

---

## 📅 Current Execution Phase: PHASE 3 (DEVELOPMENT)
*The Define and Design phases have been finalized conceptually through the thesis proposal evaluation[cite: 1]. The AI agent is strictly tasked with executing the **Develop** phase systematically[cite: 1].*

---

## 🧭 Step-by-Step Milestone Track

### Milestone 1: Baseline Environment Setup & Authentication
**Goal:** Initialize a secure Laravel 12 application with multi-role capabilities.
- [ ] Initialize Laravel 12 codebase with SQLite configuration (`database.sqlite`)[cite: 2].
- [ ] Set up Tailwind CSS 4 and Vite 7 asset bundling architecture[cite: 2].
- [ ] Implement robust User Authentication (Registration, Login, Logout) with complete field validations[cite: 1, 2].
- [x] Create the database migration column for the `roles` JSON casting with the locked values `['user']` and `['admin']`[cite: 2].
- [x] Build Role-based Authorization Middleware/Gates preventing non-admins from hitting `/admin/*`[cite: 2].

### Milestone 2: Single Source of Truth - Size Standards & Database Seeders
**Goal:** Establish the mathematical baseline for the classification engine in the database[cite: 2].
- [ ] Create `size_standards` migration table containing fields: `id`, `size_name`, `jempol`, `telunjuk`, `tengah`, `manis`, `kelingking`, `tolerance`, `is_active`, and timestamps[cite: 2].
- [ ] Write `SizeStandardSeeder` to inject the precise empirical thesis metrics[cite: 1, 2]:
  - **XS:** 14, 10, 11, 10, 8 (mm)[cite: 1]
  - **S:** 15, 11, 12, 11, 9 (mm)[cite: 1]
  - **M:** 16, 12, 13, 12, 10 (mm)[cite: 1]
  - **L:** 17, 13, 14, 13, 11 (mm)[cite: 1]
- [ ] Implement full Admin CRUD capabilities under the `/admin/size-standards` route group to manage these records seamlessly[cite: 2].

### Milestone 3: The Core Classification Engine (Domain Service Layer)
**Goal:** Isolate the Manhattan Distance algorithm inside a pure, bulletproof Service Layer[cite: 2].
- [ ] Create `app/Services/NailClassifierService.php`[cite: 2].
- [ ] Implement runtime database querying to fetch active metrics from the `size_standards` table (Zero Hardcoding Allowed)[cite: 2].
- [ ] Write the mathematical L1/Manhattan distance loop to evaluate the five-finger metrics independently for both hands[cite: 2]:
  $$D(size) = \sum |measured\_finger - standard\_finger|$$
- [ ] Implement Custom size cutoff rules using the database `tolerance` value as the threshold[cite: 2].
- [ ] Implement multi-hand independent confidence scoring calculations:
  $$Confidence = \max(0, 100 - (5 \times SmallestDifference))$$
- [ ] Write automated Feature/Unit tests via Pest or PHPUnit verifying exact metric boundary matches, tie-breaker handling, and fallback `Custom` classifications[cite: 2].

### Milestone 4: Measurement Database Normalization & Secure Ownership
**Goal:** Prevent public data exposure by tying measurements strictly to authenticated user entities[cite: 2].
- [ ] Create `measurements` table ensuring a nullable `user_id` constraint (allowing guest testing flows but enforcing record binding upon customer login/registration)[cite: 2].
- [ ] Structure schema fields to persist complete independent data: `right_hand_data` (JSON), `left_hand_data` (JSON), `classified_size_right`, `classified_size_left`, `confidence_score_right`, and `confidence_score_left`[cite: 2].
- [ ] Build Laravel Route Policies enforcing that customers can only view, print, or clear their personal measurement history records (`/riwayat/{id}`)[cite: 2].

### Milestone 5: User Journey & Frontend Views Implementation (Blade + Tailwind 4)
**Goal:** Build a smooth, fully accessible web layout matching the thesis visual specifications[cite: 1, 2].
- [ ] **Guidance Page (`/panduan`):** Craft the instructional interface featuring clear text descriptions, inline vector graphics, and video placeholders showing the manual measuring techniques[cite: 1, 2].
- [ ] **Input Page (`/input-data`):** Construct the 5-finger input form with a custom JavaScript right/left hand asymmetry toggle layout. Enforce strict `required_with` server-side validation properties[cite: 1, 2].
- [ ] **Result Page (`/hasil-klasifikasi`):** Build the dashboard view showcasing individual finger values, calculated sizes, independent hand confidence percentages, dynamically fetched size comparison charts, and a direct WhatsApp consultation click-through link[cite: 1, 2].

### Milestone 6: Single-Vendor Catalog and Review System
**Goal:** Connect the measurement system directly to the digital commerce catalog flow[cite: 1, 2].
- [x] Build direct Admin CRUD for official Laverie products, images, prices, sizes, and active state via `/admin/catalogs`[cite: 2].
- [x] Enforce the single-vendor boundary: no vendor owner, third-party vendor role, submission, approval, or moderation pipeline.
- [x] Integrate deep measurement queries: pass the calculated user size query into the product view (`/produk?size=M`) to show matching items automatically[cite: 2].
- [x] Inject unique user-submitted ratings and reviews safely[cite: 2].

---

## 🛡️ Agent Compliance Directives
1. **Never skip milestones:** Do not write frontend pages before database tables and service layers are securely implemented and unit-tested[cite: 2].
2. **Strict Scope Lock:** If any prompt tries to deviate towards machine learning, computer vision, or camera capture APIs, you must explicitly refuse and state that manual millimeter measurement is a firm domain constraint of this academic scope[cite: 1].
3. **Database Dependency:** Always query the active records from the database. Hardcoded size mappings within controllers or views are explicitly banned[cite: 2].