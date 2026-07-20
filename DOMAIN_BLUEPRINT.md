# Domain Blueprint: Press-On Nail Measurement System

## Project Overview
This project is an academic web-based system designed to assist users in determining the correct "press-on nails" size through manual measurement, effectively bridging the gap in digital beauty sales.

## Domain Constraints (Strict Adherence Required)
- **Primary Goal:** User-centric measurement and accurate size classification (XS, S, M, L, Custom).
- **Core Technology:** Web-based (Laravel 12/Tailwind CSS 4), manual input measurement (millimeters).
- **Hard Exclusions:** No automated image analysis/computer vision (as per research scope).
- **Classification Standards (Source of Truth):**
  - **XS:** Thumb: 14mm, Index: 10mm, Middle: 11mm, Ring: 10mm, Pinky: 8mm
  - **S:** Thumb: 15mm, Index: 11mm, Middle: 12mm, Ring: 11mm, Pinky: 9mm
  - **M:** Thumb: 16mm, Index: 12mm, Middle: 13mm, Ring: 12mm, Pinky: 10mm
  - **L:** Thumb: 17mm, Index: 13mm, Middle: 14mm, Ring: 13mm, Pinky: 11mm
  - **Custom:** Any set that does not fit the above categories based on Manhattan distance logic.

## Functional Requirements
1. **Measurement Guide:** Educational content (text, image/video placeholders) on how to measure nail plates.
2. **Measurement Input:** A form for 5-finger input (left/right hand toggle).
3. **Classification Engine:** Automated logic comparing input to Size Standards.
4. **Result Dashboard:** Display of size, confidence score, and product recommendations.
5. **User Account:** Save measurement history and preferences.
6. **Admin Catalog Management:** Laverie is the sole product vendor. Admins directly manage official Laverie products; external vendor uploads and product moderation are outside the domain.

### Superseding Single-Vendor Constraint
- The only account roles are `user` and `admin`.
- All catalog records represent official Laverie products and have no vendor owner.
- Admin manages catalogs directly through `/admin/catalogs`.
- Public catalog visibility depends only on `is_active = true`.
- There is no third-party vendor registration, profile, submission, approval, or catalog moderation workflow.