# CHANGELOG — SPED LMS

> This file is updated after every approved feature. Never skip this step.
> Format: describe what was built, what schema changed, what was tested, and the approval date.

---

## Unreleased
- Project initialized
- Steering files created: product.md, tech.md, structure.md, workflow.md, rbac.md, security.md

## [v41] — Mobile Responsive Implementation (System-wide)
- **Built:** Mobile responsive design with hamburger navigation, camera upload support, and reusable upload component
- **Tables added/modified:** Added `student_documents` table for centralized document storage
- **Features implemented:**
  - Hamburger navigation for mobile (sidebar slides in from left with overlay)
  - Camera upload option on all upload zones (mobile only, uses device rear camera)
  - Reusable upload component with drag-and-drop, validation, and preview
  - Mobile-responsive layouts for all processes (forms stack, tables scroll, buttons full-width)
  - Centralized document storage linking all uploads to student records
  - Updated IEP form to use reusable upload component with camera support
  - Updated enrollment form (Step 7) to use reusable upload component with camera support
  - **Updated ALL upload zones system-wide to include camera support:**
    - PDSP form (simplified) - signed document upload
    - Role selection form - government ID and proof of designation uploads
    - IEP implementation materials - file uploads
    - IEP P2 form - PDF document upload
    - IEP P3 form - PDF document upload
- **Fixes applied:**
  - Removed duplicate CSS rules that were hiding camera button on mobile
  - Fixed hamburger menu toggle functionality with proper overlay positioning
  - Added !important declarations to ensure mobile CSS takes precedence
  - Cleaned up conflicting CSS between global and component-specific styles
  - Fixed sidebar scrolling issue - menu now scrolls properly without overlapping logout section
  - Updated enrollment form validation to work with new upload component
  - Fixed notification panel alignment with arrow pointer
- **Tested:** Desktop sidebar unchanged, mobile hamburger menu, camera upload functionality, responsive layouts, sidebar scrolling, notification alignment
- **Status:** Approved
- **Date:** 2026-05-12

---

<!-- TEMPLATE — copy this block for each approved feature:

## [vN] — Feature Name (Process N or Security Module N)
- **Built:** [what was created — controllers, models, views]
- **Tables added/modified:** [schema.sql changes, or "none"]
- **Tested:** [what was verified and how]
- **Status:** Approved
- **Date:** YYYY-MM-DD

-->
