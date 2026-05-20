---
inclusion: always
---

# SPED LMS — Development Workflow

## Core rule: one feature at a time
This applies to BOTH DFD processes AND security modules. No exceptions. Never bundle features.

## ⛔ LOCKED — Processes 1, 2, 3 & 4 (100% Complete — Do Not Touch)
The following files are LOCKED and must NOT be modified without explicit user approval:

**Process 1 — Enrollment Submission (Parent)**
- `app/Controllers/EnrollmentController.php`
- `app/Models/EnrollmentModel.php`
- `app/Views/enrollment/` (all files)
- `app/Views/dashboard/parent.php`

**Process 2 — Enrollment Verification (SPED Teacher)**
- `app/Controllers/VerificationController.php`
- `app/Models/StudentModel.php`
- `app/Views/verification/` (all files)
- `app/Views/students/` (all files)

**Process 3 — Initial Assessment (SPED Teacher)**
- `app/Controllers/AssessmentController.php`
- `app/Models/AssessmentModel.php`
- `app/Models/AssessmentServiceModel.php`
- `app/Views/assessment/` (all files)

**Process 4 — IEP Meeting (SPED Teacher, Guidance, Principal)**
- `app/Controllers/IEPMeetingController.php`
- `app/Models/IEPMeetingModel.php`
- `app/Models/PDSPModel.php`
- `app/Views/iep_meeting/` (all files)

**Shared Infrastructure (serves all processes — treat as locked)**
- `app/Controllers/FileController.php` — secure file serving for all processes
- `config/schema.sql` — single source of truth for all DB schema
- `config/permissions.php` — RBAC permission map
- `config/db.php` — database connection
- `app/Middleware/RoleMiddleware.php` — RBAC enforcement
- `app/Middleware/SessionMiddleware.php` — session security

**Rules:**
- If a bug is found in Processes 1, 2, 3, or 4: **describe it to the user first, wait for explicit approval, then fix.**
- Never silently edit these files as part of another feature's work.
- Never touch these files during Process 5, 6, or 7 work.
- Schema changes that affect these processes must also be approved first.
- `FileController.php` changes require explicit approval — it serves files for ALL processes.

## Documentation rule: minimize MD files
- **DO NOT** create temporary/redundant MD files (e.g., PART-A-SUMMARY.md, FIX-SUMMARY.md, etc.)
- **DO NOT** create test PHP files unless explicitly requested by user
- **ONLY** update existing documentation files:
  - `CHANGELOG.md` - for version history
  - `README.md` - for main documentation
  - `SETUP-AND-TESTING-GUIDE.md` - for setup/testing instructions
  - `PROCESS-N-TEST-CHECKLIST.md` - for testing checklists (one per process)
- **NEVER** create multiple summary files for the same feature
- **NEVER** create temporary fix documentation files
- **NEVER** create status/progress MD files unless user explicitly asks
- Keep documentation clean and consolidated
- **Exception:** Only create MD or test files when user explicitly says "create a test file" or "create documentation"

## The cycle (never skip steps)

### Step 1 — DESCRIBE (before writing any code)
Present a description of what will be built:
- What UI components will appear (form, table, modal, card, etc.)
- What controller methods will be created
- What model queries will be written
- What DB tables or columns will be added to `schema.sql`
- What security rules apply to this specific feature
- Which DFD process or security module this belongs to

### Step 2 — ASK
After describing, always ask:
> "Shall I proceed with this feature? Any changes before I build?"

Wait for explicit approval ("yes", "go", "proceed", or similar) before writing any code.

### Step 3 — PLAN MODE
Before writing code, design:
- Architecture and component structure
- DB schema changes (update `schema.sql` diff first)
- Controller/model/view breakdown

### Step 4 — BUILD
Write the code. One feature only.

### Step 5 — SELF-VERIFY
Run through every item in the self-verification checklist (see below). Fix silently before presenting.

### Step 6 — PRESENT & TEST
Present the code and say:
> "Ready to test. Confirm when tested and I'll mark it approved in CHANGELOG.md."

### Step 7 — APPROVE & LOG
On approval:
- Update `CHANGELOG.md`
- Describe the next feature
- Ask for approval before building it

## Self-verification checklist (run after every code block)
- [ ] DFD flow preserved exactly — no inputs, outputs, or actors missed
- [ ] MVC layers respected — no SQL in controllers/views, no logic in views
- [ ] `schema.sql` is the ONLY place DB changes were made
- [ ] SchemaManager migration block added with correct version number
- [ ] Prepared statements used everywhere (PDO)
- [ ] RBAC middleware applied to this route
- [ ] Permissions checked against `/config/permissions.php`
- [ ] Color scheme (#a01422, #1e4072) applied correctly in the view
- [ ] Bootstrap customized — no default blue/gray components
- [ ] No hardcoded credentials, API keys, or sensitive data
- [ ] File header comment added with process/module reference
- [ ] `CHANGELOG.md` flagged for update on approval

State **"Self-check: passed ✓"** at the end of every code response.
If any item fails — fix it silently before presenting.

## Standard response format
```
Feature:        [name and process/module number]
Description:    [UI, controller, model, schema changes]
Schema diff:    [new tables/columns going into schema.sql]
Implementation: [code — only after approval]
Self-check:     passed ✓
Next step:      [next feature — ask for approval]
```
