# SPED LMS — Process Status & Pending Work
> Last updated: 2026-05-08
> DO NOT DELETE — reference document for development progress

---

## ⛔ LOCKED — Processes 1, 2, 3, 4 (Complete)

### Process 1 — Parent Enrollment ✅
- Multi-step form (7 steps), draft save, document upload
- Returning student lookup
- Parent dashboard with LRN confirmation card (shows when learner_account_created = 1)
- Enrollment status tracking, rejection feedback

### Process 2 — Enrollment Verification ✅
- SPED Teacher reviews, approves/rejects per document
- Student record + LRN auto-generated
- Learner account created, credentials emailed to parent

### Process 3 — Initial Assessment ✅
- Part I form with auto-fill from student records
- Services checklist + dynamic MDT table
- File upload per service (stored in public/uploads/assessments/)
- Assessment versioning — grouped by student in index view
- Assessment history per student
- **Bug fixed:** File path now saved as `uploads/assessments/` (was `assessments/`)
- **Bug fixed:** `FileController` now serves assessment files (plain, not encrypted)

### Process 4 — IEP Meeting ✅
- Availability calendar (recurring + exceptions + task notes)
- IEP meeting dates auto-marked on calendar
- Meeting scheduling with suggested dates
- PHPMailer notifications to all participants
- PDSP form (6 DepEd domains, handwritten upload)
- PDSP viewable by Guidance/Principal once uploaded
- Meeting edit (SPED Teacher only)
- Meeting cancellation (SPED Teacher, Guidance, Principal — parent cannot cancel)
- Upcoming vs Past meetings based on status
- AI extraction removed (redundant)

---

## ⚠️ IN PROGRESS — Process 5 (IEP Generation)

### What's built:
- `IEPModel.php` — full model: iep_records, iep_domains, iep_core, iep_steps, iep_signatories, iep_copies
- `IEPController.php` — index, create, form, saveDraft, uploadSignedDoc, saveSignature, markSigned, newCycle, sendSignatureRequest, signPage
- `app/Views/iep/form.php` — full IEP form with all 9 sections
- `app/Views/iep/index.php` — IEP repository with filters
- `app/Views/iep/sign.php` — digital signature page
- `public/css/print.css` — DepEd Part III print layout
- Schema migration v39 — 6 new tables: iep_records, iep_domains, iep_core, iep_steps, iep_signatories, iep_copies
- Routes: /iep, /iep/create, /iep/form/{id}, /iep/save-draft, /iep/mark-signed, /iep/new-cycle, /iep/sign/{id}/{sigId}
- Sidebar updated for SPED Teacher (Part 3: Generate IEP), Guidance, Principal

### ⏳ PENDING — waiting for user testing:
- Test full flow: create → fill → sign → lock → new cycle
- Verify auto-fill from student_records
- Verify domain pre-population from signed PDSP
- Verify print layout

---

## ⏳ NOT STARTED — Process 6 & 7

### Process 6 — Implementing IEP
- Assign IEP to learner
- Upload learning materials per domain
- Track implementation progress

### Process 7 — Learner Learning Activities
- Learner dashboard with modules
- Activity completion tracking
- Progress reports

---

## 🔧 Known Issues / Fixes Applied This Session

| Issue | Fix | Status |
|-------|-----|--------|
| `meeting_time` column not found | Combined date+time into DATETIME | ✅ Fixed |
| `review_note` missing from enrollment_submissions | Schema migration v1.36 | ✅ Fixed |
| `ADD COLUMN IF NOT EXISTS` MariaDB error | Replaced with INFORMATION_SCHEMA checks | ✅ Fixed |
| `CREATE INDEX IF NOT EXISTS` MariaDB error | Replaced with INFORMATION_SCHEMA checks | ✅ Fixed |
| Assessment documents not saving | Fixed file path (public/uploads/) + DataTransfer re-attach | ✅ Fixed |
| Student list empty info | Fixed JOIN to use enrollment_id not lrn | ✅ Fixed |
| Meeting location showing "Online" | Fixed to read meeting_location column | ✅ Fixed |
| Meeting edit not saving | Fixed allowedFields in IEPMeetingModel::update() | ✅ Fixed |
| Meeting edit sets status to rescheduled but enum missing it | Schema migration v1.38 | ✅ Fixed |
| iep_meetings status enum missing 'rescheduled' | Fixed in schema.sql base definition | ✅ Fixed |
| Assessment index showing all versions flat | Grouped by student, History button per student | ✅ Fixed |
| AI extraction button in PDSP | Removed (redundant) | ✅ Fixed |
| Meeting cancellation not built | Added cancelMeeting() + route + UI | ✅ Fixed |

---

## 📋 Schema Migrations Applied

| Version | Description |
|---------|-------------|
| v20-21 | Base schema |
| v22 | User security columns |
| v23-24 | Assessment Section A columns |
| v25-27 | Process 4 tables |
| v28-30 | Assessment conducted_by, updated_at |
| v31-33 | PDSP updates |
| v34-36 | review_note columns |
| v37 | user_availability.note column |
| v38 | iep_meetings status enum + rescheduled |

---

## 🗄️ Schema Import Notes (for PC transfer)

The `config/schema.sql` file is self-contained and clean (692 lines, no redundancy).

**To import on a new machine:**
```bash
# 1. Create the database first
mysql -u root -p -e "CREATE DATABASE sped_lms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 2. Import schema
mysql -u root -p sped_lms < config/schema.sql
```

**What was cleaned (2026-05-08):**
- Removed duplicate `iep_meetings` table definition (migration v1.27 block was dead code)
- Removed orphaned `iep_meeting_calendars` table (replaced by `user_availability`)
- Removed orphaned `iep_p2_documents` + `iep_p2_reviews` (replaced by `pdsp_records`)
- Removed old `iep_documents` + `iep_signatures` (replaced by `iep_p3_documents`)
- Removed all ALTER TABLE migration blocks — all columns now in base definitions
- `learner_iep.iep_p3_id` FK now correctly points to `iep_p3_documents`
- All db_version entries 20–38 inserted in single block at end
- Schema reduced from 1287 lines → 692 lines

**36 tables defined (no duplicates):**
db_version, users, role_requests, role_documents, enrollment_submissions,
enrollment_documents, student_records, education_history, assessment_records,
assessment_services, assessment_documents, assessment_checklists, iep_meetings,
meeting_notifications, user_availability, pdsp_records, pdsp_domains,
pdsp_signatories, iep_p3_documents, iep_p3_signatures, iep_audit_log,
learner_iep, learning_materials, activity_templates, activity_attempts,
assignment_submissions, learner_progress, activity_records, module_access_logs,
login_log, activity_log, notifications, encryption_audit, csrf_tokens,
rate_limit_log, dlp_settings, system_settings
