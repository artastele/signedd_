# Mobile Responsiveness + Camera Upload + Student Record Storage
> Add this to your Kiro steering files as `/kiro/steering/mobile-responsive.md`

---
inclusion: auto
description: System-wide mobile responsiveness, hamburger navigation, camera upload option on all upload zones, and centralized student document storage. Use when working on any UI, navigation, upload feature, or student records.
---

# System-wide changes — mobile, camera, and document storage

> CRITICAL RULE: These are UI and storage changes ONLY.
> NO process logic, DFD flows, data stores, actor names, or DB schema
> outside of what is explicitly listed here may be altered.
> Before touching any file, list every file that will be changed
> and ask for approval. Never alter a process and never assume.

---

## Change 1 — Hamburger navigation (sidebar → mobile menu)

### Desktop behavior (unchanged):
- Sidebar remains fully visible at all times on screens wider than 1024px
- No changes to sidebar content, links, or structure on desktop

### Mobile behavior (screens 1024px and below):
- Sidebar is hidden by default
- Hamburger icon (three horizontal lines) appears top-left of the navbar
- Tapping hamburger → sidebar slides in from the left as a full-height overlay
- Overlay background (semi-transparent dark) covers the page content
- Tapping overlay or a menu item → sidebar closes
- Active menu item: crimson (#a01422) background highlight, white text
- Sidebar overlay: dark navy (#1a3560) background, white links
- Hamburger icon: white, 24px, uses Tabler `ti-menu-2` icon
- Close button (×) appears inside the open sidebar top-right: Tabler `ti-x`
- Notification bell stays visible in navbar on mobile at all times
- Page title stays visible in navbar center on mobile

### Implementation:
- Add class `sidebar-open` to `<body>` when hamburger tapped
- CSS: `.sidebar` default `transform: translateX(-100%)` on mobile,
  `body.sidebar-open .sidebar { transform: translateX(0); }`
- Overlay: `<div class="sidebar-overlay">` added to base layout,
  hidden by default, visible when `sidebar-open` class active
- JS: hamburger click → toggle `sidebar-open`, overlay click → remove it
- No jQuery — vanilla JS only
- Transition: `transform 0.25s ease` — smooth slide

### Files to update:
- Base layout file (navbar + sidebar wrapper)
- `/public/css/custom.css` — mobile sidebar styles
- `/public/js/sidebar.js` — hamburger toggle logic (new file)

---

## Change 2 — Camera option on ALL upload zones

Every upload zone in the system gets two buttons side by side:
- Button 1: `<i class="ti ti-upload">` + "Choose file" — standard file picker
- Button 2: `<i class="ti ti-camera">` + "Take photo" — opens device camera

### Implementation:
Two separate `<input>` elements per upload zone:

```html
<!-- File picker (all devices) -->
<input type="file" accept=".jpg,.jpeg,.png,.pdf" class="upload-input-file" style="display:none">

<!-- Camera (mobile only — hidden on desktop via CSS) -->
<input type="file" accept="image/*" capture="environment" class="upload-input-camera" style="display:none">

<div class="upload-zone">
  <p class="upload-hint">Drag and drop or choose a file</p>
  <p class="upload-formats">Accepted: jpg, png, pdf · Max 10MB</p>
  <div class="upload-buttons">
    <button type="button" class="btn-upload-file">
      <i class="ti ti-upload" aria-hidden="true"></i> Choose file
    </button>
    <button type="button" class="btn-upload-camera">
      <i class="ti ti-camera" aria-hidden="true"></i> Take photo
    </button>
  </div>
</div>
```

### Desktop behavior:
- Camera button hidden on screens wider than 1024px via CSS:
  `.btn-upload-camera { display: none; } @media(max-width:1024px) { .btn-upload-camera { display: inline-flex; } }`
- Only file picker button shown on desktop

### Mobile behavior:
- Both buttons shown side by side
- "Take photo" button opens device camera directly (rear camera preferred via `capture="environment"`)
- On iOS/Android: camera app opens, photo taken, returned to the form
- Accepted format for camera: jpg/png only (camera never produces PDF)

### After file selected (both methods):
- Show filename, file type badge, file size, and remove button (×)
- Validate: wrong type → inline crimson error below zone
- Validate: over 10MB → inline crimson error below zone
- Valid file: show green checkmark badge, file ready for submit

### Upload zones to update (ALL of these — no exceptions):
- Process 1: PSA / PWD ID / BEEF document upload
- Process 2: any document review upload (if applicable)
- Process 3 Section B: supporting documents per MDT service
- Process 4: PDSP signed document upload
- Process 5: IEP signed document upload
- Process 6: learning materials upload
- RBAC: role verification document upload (ID + designation letter)
- Student records: any additional document upload

### Shared upload component:
Create a reusable partial: `/app/Views/components/upload-zone.php`
Accepts parameters: field_name, accepted_types, max_size, show_camera
Include this partial in every view that has an upload — do not duplicate upload logic across views.

---

## Change 3 — All documents stored in student records

Every file uploaded anywhere in the system must be linked to a student record in a central `student_documents` table. No file is stored in isolation.

### New table (schema.sql only):
```sql
-- MIGRATION: v{N}
CREATE TABLE IF NOT EXISTS student_documents (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  process_name VARCHAR(50) NOT NULL,
  document_type VARCHAR(100) NOT NULL,
  file_path VARCHAR(500) NOT NULL,
  file_name VARCHAR(255) NOT NULL,
  file_type VARCHAR(20) NOT NULL,
  file_size INT NOT NULL,
  uploaded_by INT NOT NULL,
  uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (student_id) REFERENCES student_records(id),
  FOREIGN KEY (uploaded_by) REFERENCES users(id)
);
-- END MIGRATION: v{N}
```

### `process_name` values (use exactly these strings):
- `enrollment` — Process 1 documents (PSA, PWD ID, BEEF)
- `assessment` — Process 3 supporting documents
- `pdsp` — Process 4 signed PDSP upload
- `iep` — Process 5 signed IEP upload
- `learning_material` — Process 6 materials
- `role_verification` — RBAC role request documents

### `document_type` values (examples):
- `psa`, `pwd_id`, `beef`, `eccd_checklist`, `anecdotal_record`,
  `doctor_assessment`, `signed_pdsp`, `signed_iep`,
  `learning_module`, `government_id`, `designation_letter`

### How every upload must work:
1. File uploaded → saved to `/uploads/{process_name}/{student_id}/` folder
2. Row inserted into the existing process table (e.g. `assessment_documents`)
3. Row ALSO inserted into `student_documents` — same file, linked to student
4. Never save a file without inserting into `student_documents`

### Student records page — documents tab:
Add a "Documents" tab to the student profile page showing all files:
- Filter by process (All / Enrollment / Assessment / PDSP / IEP / Materials)
- Each row: document type badge, filename, process name, uploaded by, date, view + download buttons
- Viewable (embedded) and downloadable by: sped_teacher, guidance, principal
- Parent: view only — no download
- Documents never deleted — soft delete only (`is_hidden` flag, hidden from UI but kept in storage)

---

## Mobile responsiveness rules (apply to ALL views)

### Breakpoints:
- Desktop: 1024px and above — no changes to existing layout
- Tablet: 768px–1023px — sidebar collapses, content fills full width
- Mobile: below 768px — single column, everything stacks

### Global CSS rules (add to `/public/css/custom.css`):

```css
/* Mobile base */
@media (max-width: 1024px) {
  .sidebar { position: fixed; top: 0; left: 0; height: 100vh;
             transform: translateX(-100%); transition: transform 0.25s ease;
             z-index: 1000; width: 260px; }
  body.sidebar-open .sidebar { transform: translateX(0); }
  .sidebar-overlay { display: none; position: fixed; inset: 0;
                     background: rgba(0,0,0,0.45); z-index: 999; }
  body.sidebar-open .sidebar-overlay { display: block; }
  .main-content { margin-left: 0 !important; width: 100%; }
  .btn-upload-camera { display: inline-flex; }
}

@media (min-width: 1025px) {
  .hamburger-btn { display: none; }
  .sidebar-overlay { display: none !important; }
  .btn-upload-camera { display: none; }
}

/* Stack grids on mobile */
@media (max-width: 768px) {
  .row, .grid-cols-2, .grid-cols-3 { flex-direction: column !important;
                                      grid-template-columns: 1fr !important; }
  .col, [class*="col-"] { width: 100% !important; max-width: 100% !important; }
  table { display: block; overflow-x: auto; white-space: nowrap; }
  .btn { width: 100%; justify-content: center; }
  .card { margin-bottom: 12px; }
  .upload-buttons { flex-direction: row; gap: 8px; }
  .upload-buttons button { flex: 1; }
  h1 { font-size: 18px; }
  h2 { font-size: 16px; }
  .page-header { flex-direction: column; align-items: flex-start; gap: 8px; }
  .page-header .badge-group { flex-wrap: wrap; }
}
```

### Per-process mobile rules:

**Process 1 — Enrollment form:**
- Form fields stack to single column (100% width each)
- Upload zone full width, file + camera buttons side by side
- Submit button full width

**Process 2 — Verification:**
- Student list becomes stacked cards (not a table)
- Each card: student name, LRN, submission date, status badge
- Approve/reject buttons full width inside each card

**Process 3 — Assessment:**
- Section A fields stack to single column
- Services checklist: two columns on tablet, one column on mobile
- Section B MDT table: horizontal scroll (`overflow-x: auto`)
- Each service upload zone: file + camera buttons

**Process 4 — IEP Meeting:**
- Availability calendar: stays as grid, days smaller on mobile (32px each)
- Suggested dates: stacked cards, full width
- PDSP form: all domain cards stack full width
- Upload zone: file + camera buttons

**Process 5 — IEP Upload:**
- Upload zone full width
- Re-evaluation date picker full width
- Signatory checkboxes + name fields stack vertically
- Name input appears directly below its checkbox when checked
- Submit button full width

**Process 6 — IEP Implementation:**
- Materials list: stacked cards
- Upload zone: file + camera buttons

**Process 7 — Learning Activities:**
- Module cards: stack full width
- Progress bar visible on each card
- No layout changes to data or logic

**RBAC dashboards:**
- Role selection cards: 2 columns on tablet, 1 column on mobile
- Role verification form: fields stack to single column
- Document upload: file + camera buttons

---

## Build order (one at a time — describe and ask before building)

1. schema.sql migration — add `student_documents` table
2. Reusable upload component — `/app/Views/components/upload-zone.php`
3. Hamburger navigation — CSS + JS, desktop sidebar unchanged
4. Global mobile CSS — breakpoints, stacking rules, table overflow
5. Process 1 mobile layout — form stacking + camera upload
6. Process 2 mobile layout — stacked cards + full-width buttons
7. Process 3 mobile layout — checklist stacking + MDT table scroll + camera
8. Process 4 mobile layout — calendar + PDSP form stacking + camera
9. Process 5 mobile layout — upload + checkboxes stacking + camera
10. Process 6 mobile layout — materials cards + camera
11. Process 7 mobile layout — module cards + progress bars
12. RBAC mobile layout — role cards + verification form stacking
13. Student documents tab — documents list with filter, view, download per role
14. Wire all existing uploads to also insert into `student_documents`

---

## Self-check (run before presenting any code)

**Navigation:**
- [ ] Desktop sidebar completely unchanged — no layout shift, no missing links
- [ ] Hamburger only visible on screens 1024px and below
- [ ] Sidebar slides in smoothly with 0.25s transition
- [ ] Overlay closes sidebar on tap
- [ ] Active menu item highlighted in crimson

**Camera upload:**
- [ ] Camera button hidden on desktop (1025px+)
- [ ] Camera button visible on mobile (1024px and below)
- [ ] Camera input uses `capture="environment"` (rear camera)
- [ ] Both file and camera inputs trigger the same validation and preview
- [ ] Reusable upload component used — no duplicate upload logic
- [ ] Wrong file type → inline crimson error
- [ ] Over 10MB → inline crimson error

**Student documents:**
- [ ] `student_documents` table created in schema.sql only
- [ ] Every upload in every process inserts a row into `student_documents`
- [ ] `process_name` uses exact values defined above
- [ ] Student documents tab shows all files filtered by process
- [ ] Parent sees view only — no download button rendered
- [ ] Files never hard-deleted — soft delete only

**Mobile layout:**
- [ ] No process logic, DFD flows, or DB queries altered
- [ ] Every form stacks to single column on mobile
- [ ] Tables scroll horizontally — never overflow or clip
- [ ] All buttons full width on mobile
- [ ] No horizontal scroll on the page itself (only inside tables)
- [ ] Color scheme #a01422 and #1e4072 preserved on mobile
- [ ] No default Bootstrap blue or gray components introduced

**General:**
- [ ] schema.sql is the only place for DB changes
- [ ] Prepared statements for all DB operations
- [ ] No blank pages or raw errors shown to user
- [ ] Reusable upload component used in every upload zone
