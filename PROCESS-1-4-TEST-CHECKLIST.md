# PROCESS 1-4 TEST CHECKLIST
> Self-verification guide for new PC setup
> Run through every item top to bottom. Check off as you go.
> Last updated: 2026-05-08

---

## BEFORE YOU START  Setup Verification

- [ ] Database created: `CREATE DATABASE sped_lms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;`
- [ ] Schema imported: `mysql -u root -p sped_lms < config/schema.sql`
- [ ] No errors on import (warnings about existing tables are OK)
- [ ] Admin account exists: login with `admin@spedlms.local` / `password`
- [ ] `db_version` table has versions 2038 all inserted
- [ ] `/public/uploads/` folder exists and is writable
- [ ] `/public/uploads/assessments/` folder exists and is writable
- [ ] `/public/uploads/pdsp/` folder exists and is writable
- [ ] `/public/uploads/signatures/` folder exists and is writable
- [ ] `.env` file configured (DB credentials, mail settings, app URL)
- [ ] PHPMailer credentials set in `.env` or `config/mail.php`

---

## PROCESS 1  Parent Enrollment Submission

### Setup: Create a parent account
- [ ] Go to `/register`  register a new account
- [ ] Verify email (OTP sent, enter code at `/auth/verify-email`)
- [ ] Go to `/role/select`  select **Parent**
- [ ] Redirected to parent dashboard at `/dashboard`

### New Enrollment  7-step form
- [ ] Go to `/enrollment`  see enrollment type selection (New / Transfer / Returning)
- [ ] Click **New Student**  redirected to `/enrollment/create`
- [ ] **Step 1  Learner Info:** Fill in Last Name, First Name, Birth Date, Sex, Grade Level  Next
- [ ] **Step 2  Current Address:** Fill province, city, barangay  Next
- [ ] **Step 3  Parent/Guardian:** Fill father/mother/guardian info  Next
- [ ] **Step 4  Previous School:** Fill or skip  Next
- [ ] **Step 5  Enrollment Details:** Grade level, school year  Next
- [ ] **Step 6  Learning Modality:** Select at least one modality  Next
- [ ] **Step 7  Documents & Signature:** Upload PSA Birth Certificate (jpg/png/pdf)  draw signature  Submit
- [ ] After submit: redirected to `/enrollment/status` with status = **Pending**
- [ ] Flash message: "Enrollment submitted successfully"

### Draft save
- [ ] Start a new enrollment, fill Step 1 only, wait 3 seconds
- [ ] Draft auto-saved (check `enrollment_submissions` table: `is_draft = 1`)
- [ ] Close browser, reopen `/enrollment`  draft detected, option to continue or discard
- [ ] Click **Discard**  draft deleted, back to enrollment type selection

### Returning student lookup
- [ ] Go to `/enrollment/returning-lookup`
- [ ] Search by LRN of a previously verified student  student info appears
- [ ] Confirm  enrollment form pre-filled with previous data

### Enrollment status page
- [ ] Go to `/enrollment/status`  see submitted enrollment card
- [ ] Status badge shows **Pending** (crimson)
- [ ] If rejected: rejection reason visible, option to re-submit

---

## PROCESS 2  Enrollment Verification (SPED Teacher)

### Setup: Create a SPED Teacher account
- [ ] Register new account  verify email
- [ ] Go to `/role/select`  select **SPED Teacher**  submit staff application
- [ ] Login as admin  go to `/admin/role-requests`  approve the SPED Teacher request
- [ ] SPED Teacher receives approval email
- [ ] Login as SPED Teacher  redirected to teacher dashboard

### Review enrollment list
- [ ] Go to `/verification`  see list of pending enrollments
- [ ] Each row shows: student name, enrollment type, submitted date, status badge
- [ ] Click **View** on a pending enrollment  `/verification/{id}`

### Enrollment detail view
- [ ] All 7 steps of data visible (learner info, address, parents, school, modality)
- [ ] Uploaded documents visible with **View** and **Download** buttons
- [ ] Document viewer opens file inline (PDF/image)

### Verify enrollment
- [ ] Click **Verify & Create Student Record** button
- [ ] Confirmation modal appears  click Confirm
- [ ] System creates:
  - [ ] `student_records` row with auto-generated LRN (format: YYYYMMDDNNNN)
  - [ ] `education_history` row from previous school data
  - [ ] `users` row with role = `learner` (temporary password generated)
- [ ] Parent receives email with: LRN, learner login credentials
- [ ] Parent dashboard shows LRN confirmation card
- [ ] Enrollment status updated to **Verified**
- [ ] Flash message: "Student verified. Learner account created."

### Reject enrollment
- [ ] On a different pending enrollment  click **Reject**
- [ ] Enter rejection reason  submit
- [ ] Status updated to **Rejected**
- [ ] Parent sees rejection reason on `/enrollment/status`

---

## PROCESS 3  Initial Assessment (SPED Teacher)

### Access assessment
- [ ] Login as SPED Teacher  go to `/assessment`
- [ ] Dashboard shows: Finalized assessments tab, Drafts tab
- [ ] Click **Conduct New Assessment**  `/assessment/conduct`
- [ ] Student selector dropdown shows only verified students (from Process 2)

### Section A  Auto-fill
- [ ] Select a verified student from dropdown
- [ ] Section A fields auto-fill from `student_records` + `enrollment_submissions`:
  - [ ] Last Name, First Name, Middle Name, Extension
  - [ ] Date of Birth, Age, Sex
  - [ ] Home Address (current address fields)
  - [ ] LRN, School Year
  - [ ] Father name + contact, Mother name + contact
  - [ ] Guardian name + contact
  - [ ] Previous School, Grade Level
- [ ] All auto-filled fields are **editable** (can be corrected)
- [ ] Fields not in student_records remain blank for manual input

### Section A  Services checklist
- [ ] Checkboxes visible (two-column layout, crimson accent):
  - [ ] Occupational Therapy
  - [ ] Physical Therapy
  - [ ] Behavioral Therapy
  - [ ] Psychosocial Intervention
  - [ ] Speech and Language Therapy
  - [ ] Daily Living Skills
  - [ ] Skills Development
  - [ ] Others (text input appears when checked)
- [ ] Screening types: MFAT, ECCD Checklist, Psycho-Educational checkboxes visible

### Section B  Dynamic MDT table
- [ ] Check "Occupational Therapy"  MDT row appears in Section B immediately (JS)
- [ ] Uncheck it  row disappears immediately
- [ ] Check 3 services  3 rows appear in MDT table
- [ ] Each row has:
  - [ ] Service name (read-only, from checkbox)
  - [ ] "Add MDT Member" button  adds name + designation fields dynamically
  - [ ] Date of Assessment date picker
  - [ ] File upload slot (accepts jpg, png, pdf only)
- [ ] Upload a PDF to one service  filename shown on success
- [ ] Upload a .exe file  error message shown, file rejected

### Submit assessment
- [ ] Fill Section A + check 2 services + add MDT members + upload files
- [ ] Click **Submit Assessment**
- [ ] Redirected to `/assessment/view/{id}`
- [ ] Assessment shows status = **Finalized**
- [ ] Version number = 1 (first assessment for this student)
- [ ] Uploaded files visible with View/Download buttons

### Versioning
- [ ] Conduct a second assessment for the same student
- [ ] Submit  version number = 2
- [ ] Go to `/assessment/history/{student_id}`  both versions listed
- [ ] Click version 1  shows original data (not overwritten)
- [ ] Click version 2  shows new data

### Assessment index grouping
- [ ] Go to `/assessment`  students grouped (not flat list of all versions)
- [ ] Each student row shows latest version + "History" button
- [ ] Click History  shows all versions for that student

---

## PROCESS 4  IEP Meeting (SPED Teacher, Guidance, Principal)

### Setup: Create Guidance and Principal accounts
- [ ] Register Guidance account  verify email  apply for Guidance role
- [ ] Register Principal account  verify email  apply for Principal role
- [ ] Admin approves both role requests
- [ ] Both receive approval emails

### Availability Calendar  SPED Teacher
- [ ] Login as SPED Teacher  go to `/iep/availability`
- [ ] Monthly calendar visible (crimson/navy color scheme)
- [ ] "Set Weekly Schedule" panel visible (MonSun checkboxes)
- [ ] Check Monday, Wednesday, Friday  Save
- [ ] Calendar updates: Mon/Wed/Fri highlighted in navy
- [ ] Click a specific Monday  toggle exception (mark as unavailable)
- [ ] That Monday shows crimson dot indicator (exception override)
- [ ] Click again  exception removed

### Availability Calendar  Guidance & Principal
- [ ] Login as Guidance  go to `/iep/availability`  set Tue/Thu/Fri as available
- [ ] Login as Principal  go to `/iep/availability`  set Mon/Wed/Fri as available

### Meeting Scheduling
- [ ] Login as SPED Teacher  go to `/iep/meetings/schedule`
- [ ] Student selector shows students with finalized assessments
- [ ] Select a student  suggested dates appear (dates when all 3 are available)
  - [ ] SPED Teacher: Mon/Wed/Fri, Guidance: Tue/Thu/Fri, Principal: Mon/Wed/Fri
  - [ ] Suggested dates should be **Fridays** (all three available)
- [ ] Click a suggested Friday  date auto-fills
- [ ] Fill: Time, Location, Agenda Notes
- [ ] Click **Schedule Meeting**
- [ ] Meeting created in `iep_meetings` table
- [ ] PHPMailer sends email to: Guidance, Principal, Parent
  - [ ] Email contains: student name, date/time, location, agenda
- [ ] In-app notifications created for: Guidance, Principal, Parent
- [ ] Redirected to `/iep/meetings`  meeting listed under Upcoming

### Meeting detail view
- [ ] Click meeting  `/iep/meetings/{id}`
- [ ] All details visible: student, date/time, location, agenda, scheduled by
- [ ] Status badge: **Scheduled** (navy)
- [ ] SPED Teacher sees Edit and Cancel buttons
- [ ] Guidance/Principal see meeting details (no edit)
- [ ] Parent sees meeting details (no edit, no cancel)

### Edit meeting (SPED Teacher only)
- [ ] Click Edit  change location and time  Save
- [ ] Status updates to **Rescheduled**
- [ ] PHPMailer re-sends updated notification to all parties
- [ ] In-app notifications updated

### Cancel meeting
- [ ] Click Cancel  enter cancellation reason  Confirm
- [ ] Status updates to **Cancelled**
- [ ] Meeting moves to Past Meetings section
- [ ] Cancellation reason visible on detail view

### PDSP Form (Part II)
- [ ] Go to `/iep/meetings/{id}/pdsp`
- [ ] PDSP form shows 6 DepEd domains:
  - [ ] Perceptuo-Cognitive
  - [ ] Psychosocial
  - [ ] Socio-Emotional
  - [ ] Psychomotor
  - [ ] Daily Living Skills
  - [ ] Communication and Language
- [ ] Each domain row has: Sub-Domain, Skills Description, Mastered toggle, Educational Recommendation, Q1 Level, Q2 Level
- [ ] Fill all 6 domains  click **Save Draft**
- [ ] Flash message: "PDSP saved as draft"
- [ ] `pdsp_records` row created with status = `draft`
- [ ] `pdsp_domains` rows created (6 rows)

### PDSP  Upload signed document
- [ ] Click **Upload Signed Document**  upload a PDF/image of the signed form
- [ ] File saved to `/public/uploads/pdsp/`
- [ ] `pdsp_records.signed_document_path` updated

### PDSP  Add signatories and mark as signed
- [ ] Click **Mark as Signed**  signatory form appears
- [ ] Add at least: SPED Teacher name, School Head name
- [ ] Submit  `pdsp_records.status` = `signed`
- [ ] `pdsp_signatories` rows created
- [ ] Meeting status updates to **Completed**
- [ ] Process 5 (IEP Generation) unlocked for this student

### PDSP  Access by Guidance and Principal
- [ ] Login as Guidance  go to `/iep/meetings/{id}/pdsp`
- [ ] PDSP form visible (read-only view of saved data)
- [ ] Signed document downloadable
- [ ] Login as Principal  same access confirmed

### Notifications verification
- [ ] Login as Parent  bell icon shows unread notification count
- [ ] Click bell  notification: "IEP Meeting scheduled for [student name]"
- [ ] Click notification  redirected to meeting detail
- [ ] Mark as read  notification count decreases

---

## CROSS-PROCESS FLOW TEST (End-to-End)

Run this full flow once to confirm all 4 processes chain correctly:

1. [ ] Parent registers  verifies email  selects Parent role
2. [ ] Parent submits enrollment (new student, uploads PSA)
3. [ ] SPED Teacher verifies enrollment  LRN generated  learner account created
4. [ ] Parent receives email with LRN and learner credentials
5. [ ] SPED Teacher conducts assessment  selects student  auto-fill works  submits
6. [ ] Assessment shows as Finalized (version 1)
7. [ ] SPED Teacher schedules IEP meeting  selects student with finalized assessment
8. [ ] Guidance and Principal receive email notifications
9. [ ] SPED Teacher fills PDSP form  uploads signed document  marks as signed
10. [ ] Meeting status = Completed  Process 5 unlocked

---

## KNOWN ISSUES (already fixed in this codebase)

| Issue | Status |
|-------|--------|
| `meeting_time` column not found | Fixed  combined into DATETIME |
| `review_note` missing from enrollment_submissions | Fixed  column in base schema |
| `ADD COLUMN IF NOT EXISTS` MariaDB error | Fixed  INFORMATION_SCHEMA checks removed (clean schema) |
| Assessment documents not saving | Fixed  correct upload path |
| Meeting edit not saving | Fixed  allowedFields in IEPMeetingModel |
| iep_meetings status enum missing rescheduled | Fixed  in base schema definition |
| Assessment index showing flat list | Fixed  grouped by student |
| Duplicate iep_meetings table definition | Fixed  single definition in clean schema |
| Orphaned iep_meeting_calendars table | Removed  replaced by user_availability |
| Orphaned iep_p2_documents / iep_p2_reviews | Removed  replaced by pdsp_records |
| Old iep_documents / iep_signatures tables | Removed  replaced by iep_p3_documents |
| learner_iep FK pointed to old iep_documents | Fixed  now points to iep_p3_documents |
| Missing db_version 33 | Fixed  all versions 20-38 inserted on fresh install |

---

## SCHEMA CHANGES SUMMARY (clean schema vs old schema)

| Change | Reason |
|--------|--------|
| Removed `iep_meeting_calendars` | Replaced by `user_availability` (recurring/exception model) |
| Removed `iep_p2_documents` + `iep_p2_reviews` | Replaced by `pdsp_records` + `pdsp_domains` |
| Removed `iep_documents` + `iep_signatures` | Replaced by `iep_p3_documents` + `iep_p3_signatures` |
| Removed duplicate `iep_meetings` block | Single authoritative definition kept |
| Removed all migration ALTER TABLE blocks | All columns now in base table definitions |
| `learner_iep.iep_p3_id` (was `iep_id`) | FK now points to `iep_p3_documents` |
| `iep_audit_log.document_type` only `p3` | p2 audit removed (p2 flow removed) |
| `review_note` in `enrollment_submissions` | Now in base definition (was migration v1.36) |
| `deleted_at` + `locked_until` in `users` | Now in base definition (was migration v22) |
| All version markers 20-38 inserted at end | Single INSERT IGNORE block on fresh install |
