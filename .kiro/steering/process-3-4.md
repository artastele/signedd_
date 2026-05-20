# Process 3 & 4 — Prompt Block for Kiro
> Add this to your Kiro steering files as `/kiro/steering/process-3-4.md`

---
inclusion: auto
description: Process 3 (Initial Assessment) and Process 4 (IEP Meeting) — covers DepEd SPED forms Part I and Part II, auto-fill, dynamic MDT uploads, availability calendar, Claude Vision AI extraction, signature pad, and document passing. Use when working on assessment, IEP meeting scheduling, PDSP, or any related feature.
---

# Process 3 — Conducting Initial Assessment

> DO NOT ALTER WITHOUT APPROVAL — Process 3
> One feature at a time. Describe → ask → build → test → approve.

## Trigger
Process 3 becomes available only after a student has a verified record from Process 2.
SPED Teacher role required (`sped_teacher`).

## DB Tables (schema.sql only)
```
assessment_records    — id, student_id, version (int, auto-increment per student),
                        conducted_by (user_id), status (draft/finalized),
                        created_at, updated_at

assessment_services   — id, assessment_id, service_name, mdt_members (JSON),
                        date_of_assessment, created_at

assessment_documents  — id, assessment_service_id, file_path, file_type,
                        original_name, uploaded_at

assessment_checklists — id, assessment_id, service_type, checked (bool)
                        (one row per service type per assessment)
```

## Part I — Learner's Educational Assessment

### Section A — Auto-fill behavior
When SPED Teacher selects a student to assess:
- Pull from `student_records` and auto-fill these fields:
  Last Name, First Name, Middle Name, Name Extension, Date of Birth, Age,
  Sex, Religion, Home Address, LRN, School, School Year, Name of Adviser,
  Name of Father, Father Contact + Occupation,
  Name of Mother, Mother Contact + Occupation,
  Guardian/Caregiver name, contact, occupation,
  Previous School Attended, Grade Level,
  with IEP (yes/no), with support service/s (yes/no), support services detail,
  Education History
- All auto-filled fields remain EDITABLE — teacher can correct if info has changed
- Fields NOT yet in student_records remain blank for manual input
- Self-check: verify every auto-filled field maps to the correct student_records column

### Section A — Services / Screening checklist
Always manual — assessment-specific, never auto-filled.
Render as Bootstrap checkboxes (two-column layout, crimson checkbox accent):
- [ ] Occupational Therapy
- [ ] Physical Therapy
- [ ] Behavioral Therapy
- [ ] Psychosocial Intervention
- [ ] Speech and Language Therapy
- [ ] Daily Living Skills
- [ ] Skills Development
- [ ] Others (with text input if checked)
- Screening and Assessment types: MFAT, ECCD Checklist, Psycho-Educational (checkboxes)

Save checked items to `assessment_checklists` table.

### Section B — MDT Assessment Table (dynamic, service-driven)
The MDT table is driven by the services checked in Section A.
Only services that were checked appear as rows in the MDT table.

For each checked service, a row is shown with:
- Service name (auto-filled from checkbox, read-only)
- MDT Members (dynamic — JS "Add member" button per row, list of name + designation)
- Date/s of Assessment (date picker)
- Supporting Documents upload (one upload slot per service — accepts jpg, png, pdf)

JS behavior:
- When teacher checks/unchecks a service in Section A, the MDT row appears/disappears in Section B in real time
- Each service row has its own "Add MDT member" button for dynamic member entries
- Upload slot shows filename on success, error on invalid file type

Files saved to `assessment_documents` linked to `assessment_services` entry.
Viewable and downloadable by: sped_teacher, guidance, principal.

## Versioning
- Every submission of Part I creates a new version in `assessment_records`
- Version number auto-increments per student (student 1 → v1, v2, v3...)
- Old versions are NEVER overwritten — always preserved with all linked files
- Assessment history tab in the student profile shows all versions

## Build order for Process 3 (one feature at a time):
1. schema.sql migration for assessment tables
2. Student selector with auto-fill (Section A)
3. Services checklist (Section A)
4. Dynamic MDT table driven by checklist (Section B)
5. File upload per service
6. Save + versioning logic
7. Assessment history view

---

# Process 4 — Facilitating IEP Meeting

> DO NOT ALTER WITHOUT APPROVAL — Process 4
> One feature at a time. Describe → ask → build → test → approve.

## Trigger
Process 4 becomes available only after a finalized assessment record exists from Process 3.
SPED Teacher initiates. Guidance and Principal are participants.

## DB Tables (schema.sql only)
```
user_availability     — id, user_id, type (recurring/exception),
                        day_of_week (0-6, for recurring, nullable),
                        specific_date (date, for exceptions, nullable),
                        is_available (bool), created_at

iep_meetings          — id, student_id, assessment_id, scheduled_by (user_id),
                        meeting_date, meeting_time, venue, online_link,
                        agenda_notes, status (scheduled/rescheduled/completed),
                        created_at, updated_at

meeting_notifications — id, meeting_id, user_id, notified_via (email/system),
                        sent_at

pdsp_records          — id, meeting_id, student_id, filled_by (user_id),
                        status (draft/complete), ai_extracted (bool),
                        created_at, updated_at

pdsp_domains          — id, pdsp_id, domain_name, sub_domain, skills_description,
                        mastered (bool), educational_recommendation,
                        q1_level, q2_level
                        (q_level values: beginning/developing/approaching/proficient/advanced)

pdsp_signatures       — id, pdsp_id, signatory_role, signatory_name,
                        signature_image_path, signed_at
                        (signatory_role: sped_teacher / gen_ed_teacher / school_head /
                         ilrc_supervisor / parent_guardian / medical_allied_1 /
                         medical_allied_2 / medical_allied_3)
```

## Feature 1 — Availability Calendar

### Each user sets their availability (hybrid model):

**Recurring availability:**
- User selects which days of the week they are regularly available
  (e.g. every Monday, Wednesday, Friday)
- Saved as rows in `user_availability` with type = 'recurring', day_of_week = 0-6

**Exception dates:**
- User can mark specific dates as an exception to their recurring schedule:
  - "I'm unavailable on Dec 12 even though it's a Wednesday" (available → unavailable exception)
  - "I'm available this Saturday as a makeup" (unavailable → available exception)
- Saved as rows with type = 'exception', specific_date = that date, is_available = override value

### Calendar UI:
- Monthly calendar view, crimson/navy color scheme
- Available days: navy highlight
- Unavailable days: light gray
- Exception overrides: shown with a small crimson dot indicator
- Click any day to toggle or set exception
- Recurring pattern set via a separate "Set weekly schedule" panel (checkboxes for Mon–Sun)
- Each role (SPED Teacher, Guidance, Principal) manages their own calendar from their dashboard

### Scheduling — suggested dates:
When SPED Teacher opens the meeting scheduler:
- System queries `user_availability` for all three roles (sped_teacher, guidance, principal)
- Resolves recurring + exceptions for the next 60 days
- Returns dates where ALL THREE are available → shown as highlighted suggested dates
- Teacher can pick a suggested date OR manually override with a reason field
- If overriding: reason is stored and shown to participants in the notification

## Feature 2 — Meeting Scheduling

SPED Teacher fills:
- Student (auto-linked to their latest finalized assessment)
- Date (from suggested available dates or manual override)
- Time
- Venue (text field) OR Online link (URL field) — toggle between the two
- Agenda notes (textarea)

On submit:
- Row created in `iep_meetings`
- PHPMailer sends email to: Guidance, Principal, Parent (role: parent linked to student)
- In-system notification created for: Guidance, Principal, Parent
- Email contains: student name, meeting date/time, venue/link, agenda, scheduled by

## Feature 3 — Rescheduling

SPED Teacher can reschedule:
- System re-checks availability calendar for new suggested dates
- Teacher picks new date or overrides with reason
- `iep_meetings` status updated to 'rescheduled'
- PHPMailer re-sends updated notification to all parties
- In-system notification updated

## Feature 4 — Part II PDSP Form (AI-assisted, filled during meeting)

### Domains (from DepEd form — do not add or remove):
1. Perceptuo-Cognitive
2. Psychosocial
3. Socio-Emotional
4. Psychomotor
5. Daily Living Skills (sub-domain example: Bathing)
6. Communication and Language (sub-domains: Expressive Language, Fine Motor,
   Cognitive, Self-Management, Social Interaction Skills, Psychomotor,
   Fine Motor, Writing Skills)

For each domain row:
- Sub-Domain (text)
- Skills Description (text)
- Mastered? (Yes / No toggle)
- Educational Recommendation (text)
- Q1 Level of Performance (select: Beginning / Developing / Approaching Proficiency / Proficient / Advanced)
- Q2 Level of Performance (same select options)

### AI Extraction flow (Claude Vision API):
1. Teacher uploads photo or scanned PDF of handwritten Part II form
2. System sends image to Claude Vision API with a structured extraction prompt:
   "Extract the PDSP form fields from this image. Return JSON with domains array,
   each containing: domain_name, sub_domain, skills_description, mastered,
   educational_recommendation, q1_level, q2_level."
3. Claude Vision returns JSON → system parses and pre-fills the form fields
4. Teacher reviews every field — all are editable
5. Teacher corrects any misread fields
6. Teacher saves (draft — not yet complete until all signatures collected)

AI extraction failure handling:
- If Claude Vision API fails → show friendly message "AI extraction unavailable.
  Please fill in the form manually." → form remains blank and fully editable
- Never crash or show raw API error to teacher
- Log API errors to /logs/activity.log

### Document passing:
- Once Part II is saved (even as draft), Guidance and Principal can access it
  from their dashboard
- They can open, review, and add their signature
- SPED Teacher can also pass it manually by selecting which users to notify
- All changes and signatures are saved in real time to `pdsp_signatures`

### Signature pad:
- Canvas-based signature pad (use signature_pad.js from CDN)
- Each signatory sees their own signature slot
- They draw their signature using finger (mobile) or mouse (desktop)
- On save: signature canvas exported as PNG → stored in /uploads/signatures/
- Saved to `pdsp_signatures` with signatory_role, name, image path, timestamp
- Signature order: ANY ORDER — no sequence enforced
- Once signed, that signatory's slot shows their signature image (read-only)
- They cannot re-sign unless admin resets it

### Completion trigger:
- System checks `pdsp_signatures` after every signature save
- When ALL required signatory roles have a signed entry → auto-update
  `pdsp_records` status to 'complete' AND `iep_meetings` status to 'completed'
- Process 5 (IEP Generation) is unlocked for this student
- Notification sent to SPED Teacher that Part II is complete

### Document storage and access:
- Completed Part II (with all signatures) stored as a viewable record
- Accessible and downloadable by: sped_teacher, guidance, principal
- Parent can view but not download (read-only in-system view)
- All stored copies are versioned and linked to the meeting record

## Build order for Process 4 (one feature at a time):
1. schema.sql migration for availability, meetings, pdsp tables
2. Availability calendar UI — recurring weekly schedule setter
3. Availability calendar UI — exception date toggling
4. Meeting scheduler with suggested dates from availability cross-check
5. PHPMailer notifications (email + in-system) on schedule
6. Rescheduling flow
7. Part II PDSP form (manual fill first, no AI yet)
8. Claude Vision AI extraction + pre-fill
9. Signature pad per signatory
10. Document passing between roles
11. Completion trigger and Process 5 unlock
12. Document storage, view, and download

---

## Shared rules for Process 3 & 4

- All file uploads: accept jpg, png, pdf — reject others with clear error message
- File size limit: define during build (recommend 10MB max per file)
- All uploaded files: viewable in-system (embedded viewer) AND downloadable
- Uploaded files never deleted — soft delete only (hidden from UI, kept in storage)
- All forms: success → redirect with `$_SESSION['flash']` success message
- All forms: failure → redirect back with error — never blank page
- DB connection: every model uses `require_once db.php` and `$pdo` — no exceptions
- Color scheme: #a01422 crimson, #1e4072 navy — applied to all UI components
- Bootstrap customized — no default blue buttons or gray cards

