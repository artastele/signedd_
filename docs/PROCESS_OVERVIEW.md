# SPED LMS — Process overview (1–7)

This document summarizes how the **Signedd / SPED LMS** codebase maps enrollment and SPED workflows into **seven processes**, what each is for, and what to smoke-test after a deploy or PC change. It reflects the application design; treat “working” as **verified in code + your environment** until you run the checks below on your instance.

---

## Process 1 — Parent enrollment (compliance)

**Purpose:** Parents complete enrollment requirements and submit supporting documents.

**Typical routes / areas:** Enrollment submission flows, document uploads, parent dashboard.

**Smoke test:** Parent can start enrollment, attach allowed files, submit; staff can see the submission in the expected queue.

---

## Process 2 — Review & verification

**Purpose:** School staff review submitted enrollment data and documents; verification workflow.

**Typical routes / areas:** Review/verification screens tied to enrollment records.

**Smoke test:** Reviewer can open a submission, approve/reject or request changes per your business rules; parent sees updated status.

---

## Process 3 — Role requests & staff access

**Purpose:** Users request elevated roles (e.g. SPED teacher, guidance); admins/principals approve.

**Typical routes / areas:** `role_requests`, role documents, permissions in `config/permissions.php`.

**Smoke test:** Applicant submits role request with documents; approver can approve; user gains correct menu access after login.

---

## Process 4 — IEP meeting & PDSP (Part II)

**Purpose:** Schedule and run the IEP meeting; complete **PDSP** (Part II); upload signed PDSP when applicable.

**Typical routes / areas:** `IEPMeetingController`, PDSP forms, signed PDSP upload, meeting status.

**Smoke test:** Meeting → PDSP saved/submitted → signed document path stored; student becomes eligible for a new IEP draft where your rules allow.

---

## Process 5 — IEP (Part III) — “living document”

**Purpose:** Draft, edit, sign, and maintain the **IEP** for the learner (header, domains, steps, lesson-plan links, materials visibility, signatories).

**Typical routes / areas:** `IEPController`, `form_simplified.php`, `save-part1`, `save-steps`, `submitIEP`, digital signing (`signing` → `/iep/sign/...` → `save-signature` → `finalize-digital`), meeting-record signing with optional **face-to-face proof** upload.

**Recent fixes (high level):**

- **Digital signing table:** Requires `iep_signatories.send_status` (and related columns). The app now **auto-adds** missing columns so “Pending” + sign links show instead of everything looking “On file.”
- **Legacy insert bug:** Pending rows no longer get `signed_at = NOW()` when the DB has no `send_status` column path (fixed in `replaceSignatoryRows` legacy branch).
- **Inline signature pad:** When the IEP is in **`signing`** and the **current user** may sign a slot (parent / guidance / principal / SNEd teacher who drafted), a **canvas + Submit** block appears on the IEP form (same backend as the dedicated sign page).
- **Meeting proof (F2F):** Optional scan/photo/PDF before **Meeting record** finalize; **Take photo** on small screens uses the device camera and attaches to the main file field where supported.

**Smoke test:**

1. Draft IEP → Save sections → **Meeting record** Continue with/without proof file → status **signed**; proof link visible if uploaded.
2. Draft IEP → **Digital signatures** Continue → status **signing**; table shows **Pending** + **Open sign page** for invite roles; **Finalize** disabled until every row has a signature image (or `f2f_signed` for roles attested on paper in the digital flow).
3. As SNEd (drafter), use **inline pad** or sign page for the SNEd slot; parent/guidance/principal use their link or inline pad.

---

## Process 6 — IEP implementation (workspace / lesson plans)

**Purpose:** After the IEP is **signed**, implement linked **lesson plans**, materials, activities, uploads, publish.

**Typical routes / areas:** `IEPImplementationController`, workspace view, templates (DLL/DLP), `lesson_plans`, `lesson_materials`, links from IEP steps.

**Smoke test:** Open workspace for a signed IEP; create/publish LP; materials appear on the IEP form step when the LP is linked to that step.

---

## Process 7 — Learner experience & progress (LMS / gamification)

**Purpose:** Assigned learners (and parents) access materials, activities, and progress where enabled.

**Typical routes / areas:** Learning dashboards, progress trackers, activity attempts, notifications tied to published content.

**Smoke test:** Assigned learner sees published lesson content; completion/progress updates as designed.

---

## Database & portability

- **`config/schema.sql`** carries versioned migrations (through **v46** in-repo). On a new PC/server: run the app once so **`SchemaManager`** applies pending blocks **or** import `schema.sql`, then copy **`.env`**, **`public/uploads/`**, and **`public/templates/`** (DLL/DLP).
- **Runtime guards** (e.g. `ensurePartOneSaveSchema`, `ensureIepSignatoriesDigitalColumns`) reduce failures when a host’s MySQL skipped an `ALTER`.

---

## Honest “confirmation” statement

We can confirm these behaviors are **implemented and wired in the codebase** with the fixes above. Full “production working” still depends on your **data** (linked parent, active guidance/principal users), **mail / `APP_URL`**, and **file permissions** on `public/uploads/**`. Use the smoke tests per process after each deploy.
