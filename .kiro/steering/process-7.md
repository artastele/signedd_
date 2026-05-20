---
inclusion: auto
description: Process 7 — Learner Engaging in Learning Activities. Child-friendly gamified LMS learner dashboard. Use when working on learner dashboard, lesson cards, activities, quiz, submissions, stars, XP points, badges, or progress tracking.
---

# Process 7 — Learner LMS (Child-Friendly + Gamified)

> DO NOT ALTER WITHOUT APPROVAL — Process 7
> One feature at a time. Describe → ask → build → test → approve.
> Processes 1–6 are LOCKED. Never touch their files during Process 7 work.
> Locked files include ALL controllers, models, views, schema tables, middleware,
> and config files from Processes 1–6 and all security modules.

---

## Trigger
Accessible after teacher publishes a lesson plan in Process 6.
Learner must have `role = learner` OR parent accessing via parent dashboard.

---

## Design philosophy

The learner UI must feel like a purposeful educational space — clean,
encouraging, and slightly playful. Think Google Classroom meets light
gamification. NOT over-the-top cartoon. Rules:

- Rounded corners everywhere: 16px cards, 20px buttons, 12px badges
- Bold clear headings — friendly but readable
- Crimson (#a01422) for all primary actions
- Navy (#1e4072) for sidebar, domain badges, secondary elements
- Amber (#ef9f27) for stars only
- Green (#3b6d11) for completed states
- Soft shadows on cards: `box-shadow: 0 2px 8px rgba(0,0,0,0.07)`
  (only exception to the no-shadow rule — functional for card depth)
- NO gradients, NO mascots, NO animations, NO characters
- Color scheme: #a01422 crimson + #1e4072 navy — never default Bootstrap blue
- Bootstrap customized via custom.css — never default components

---

## Layout structure

### Sidebar (use the same layout as other processes)
- SPED LMS logo/name at top
- Learner avatar (initials circle, crimson bg) + learner first name
- XP points display: large number + "XP" label in a soft card
- Navigation links with Tabler icons:
  - My Lessons (ti-book-open) — active state: crimson bg highlight
  - My Progress (ti-chart-bar)
  - My Badges (ti-medal)
  - Logout (ti-logout)
- Active link: crimson background (#a01422 at 35% opacity), white text
- Inactive links: #a0b4cc text, hover slightly lighter
- On mobile: sidebar collapses to hamburger (same pattern as rest of system)

### Top bar
- Page title: "My Learning"
- Right side: two quick-glance pills always visible:
  - Stars pill: amber bg, ti-star icon + "[N] stars"
  - XP pill: green bg, ti-trophy icon + "[N] XP"

### Main content area
- Max width 900px, centered
- Padding 20px
- Background: #f5f5f5

---

## Page sections (top to bottom)

### Section 1 — Greeting + overall progress
```
Hello, [First name]!
Here is your learning overview.

[====crimson progress bar====] 35%
4 of 11 activities complete
```
- Greeting: 22px, font-weight 500
- Progress bar: full width, 12px height, crimson fill, rounded, #e9e9e9 track
- Progress text: 13px, color-text-secondary

### Section 2 — 3 stat cards (grid, 3 columns)
- Activities done: number / total
- Stars earned: number / total possible
- Avg score: percentage across all graded activities
- Card style: white bg, 0.5px border, 8px radius, soft shadow
- Label: 10px uppercase muted, Value: 22px font-weight 500

### Section 3 — Tabs
Three tabs on same page, JS-driven (no page reload):
- My Lessons
- My Progress
- My Badges
- Active tab: crimson underline border (2px), crimson text
- Inactive: gray text, no border

### Tab 1 — My Lessons

**Section heading:** "My Lesson Plans"

**2-column card grid** (1 column on mobile):
Each lesson card contains:
- Colored top border (4px):
  Green (#3b6d11) = complete
  Amber (#854f0b) = in progress
  Gray = not started
- Lesson title: 14px font-weight 500
- PDSP domain badge: navy bg (#1e4072), white text, 20px radius pill
- Activity count with ti-star icon (amber)
- Stars row (1–3): filled amber ti-star / empty gray ti-star
  Stars shown after at least one activity is graded
  Empty stars shown before any grading
- Mini progress bar: 6px height, crimson fill, #e9e9e9 track
- Status badge:
  Done: green bg, ti-check icon, "Done"
  In progress: amber bg, ti-clock icon, "In progress"
  Not started: gray bg, ti-circle icon, "Not started"
- Action button (full width, 44px height, 20px radius):
  Not started: "Start" — crimson bg, white text
  In progress: "Continue" — crimson bg, white text
  Done: "View lesson" — green bg, white text

### Tab 2 — My Progress

Overall stats only (no per-domain breakdown — this is for children):

- Large progress ring or bar showing overall % complete
- Total XP earned (large number, crimson)
- Total stars earned vs total possible
- Activities completed vs total
- Average score across all graded activities
- Simple message based on progress:
  0%: "Let's get started! Your lessons are ready."
  1–49%: "You are doing great! Keep going!"
  50–99%: "Almost there! You are doing amazing!"
  100%: "You finished everything! You are a superstar!"
- No per-domain breakdown — keep it simple for learners

### Tab 3 — My Badges

**Section heading:** "My Badges"

Badge shelf — flex row, wraps on overflow:
Each badge: 60px circle icon + name below (10px, centered, max 2 lines)

**Fixed badge set (defined in system — teacher cannot create custom badges):**

| Badge key | Icon (Tabler) | Name | Trigger |
|---|---|---|---|
| first_activity | ti-trophy | First activity! | First activity submitted |
| lesson_complete | ti-medal | Lesson done! | All activities in one lesson complete |
| perfect_score | ti-star | Perfect score! | 100% on any quiz |
| five_in_a_row | ti-flame | 5 in a row! | 5 activities submitted total |
| all_done | ti-award | All done! | All published lesson plans complete |
| star_collector | ti-sparkles | Star collector! | 10+ stars earned |

- Earned: amber bg (#faeeda), amber icon (#854f0b), full opacity
- Locked: gray bg, gray icon, 40% opacity, tooltip "Keep going to unlock!"
- Badge earned animation: none (keep simple)
- Earned badges shown first, locked after

---

## Gamification rules

### Stars (auto-calculated — no teacher manual assignment)
Calculated when teacher grades an activity:
- 90–100% score → 3 stars
- 70–89% → 2 stars
- Below 70% → 1 star
- View-only activities: no stars (completion only)
- Stored in `activity_stars` table

### XP points (fixed system — teacher cannot change values)
Awarded automatically on these events:
- View-only activity opened for first time: +5 XP
- File submission submitted: +10 XP
- Quiz submitted: score% × max_score points (e.g. 80% on 10pt quiz = 8 XP)
- Lesson plan fully completed: +20 XP bonus
- Badge earned: +15 XP bonus
- Stored in `learner_points` table with reason

### Badges
Checked and awarded automatically after every activity grade or submission.
Never shown as a popup — appear in badge shelf on next dashboard load.
Locked badges always visible so learner knows what to work toward.

---

## Activity submission (learner side)

### View only
- "View lesson plan" button → embedded PDF/image viewer (view only, no download)
- Materials: embedded per type (PDF viewer, video player, iframe for YouTube/Drive)
- YouTube: use youtube-nocookie.com embed, no autoplay
- System logs activity_records on first open
- Optional "I read it!" button for learner awareness (does not affect grade)
- Opening triggers +5 XP (first open only)

### File submission
- Instructions shown in a friendly card (light navy bg, rounded 12px)
- Upload zone: dashed crimson border, rounded 16px, full width
  "Choose a file or take a photo"
- File + camera buttons (44px height, rounded 12px):
  "Choose file" (navy outline) and "Take photo" (crimson)
- After selecting: preview shown + filename
- "Send to teacher!" button (crimson, full width, 44px, rounded 12px)
- After submit: success card (green bg, white text):
  "Your answer has been sent! Your teacher will check it soon."
- After grading: score shown + stars earned + teacher remarks in speech-bubble card
- Submission triggers +10 XP

### Quiz (step by step — one question at a time)
- Progress pill: "Question X of Y" (crimson bg, white text, rounded)
- Question text: 18px, centered, bold
- Multiple choice: full-width tappable cards (min 56px height, rounded 12px)
  Navy border default, crimson border + light crimson bg on selected
  After submit: green card = correct (ti-check), red card = wrong (ti-x)
- Short answer: large textarea (min 120px, 16px text, crimson focus ring)
  Placeholder: "Write your answer here..."
- "Next" button (crimson, full width, 44px)
- "Back" button (navy outline, full width, 44px)
- Last question: "Submit my answers!" (crimson, full width, 44px)
- Confirmation: simple modal "Ready to send your answers?"
  "Yes, send!" (crimson) / "Not yet" (navy outline)
- MC after submit: score shown immediately in large format
  3 stars: "Amazing! You got [X]/[Y]!"
  2 stars: "Great job! You got [X]/[Y]!"
  1 star: "Good try! You got [X]/[Y]! Keep practicing."
- Short answer after submit: "Sent! Your teacher will check it soon."
- Quiz triggers XP = score% × max_score

---

## Learner access modes

### Direct login
Learner has own account (`role = learner`).
Teacher sets this when publishing lesson plan.
Learner gets credentials via PHPMailer email.
Uses the full child-friendly UI described above.

### Parent-managed
No separate learner account.
Parent sees "My child's lessons" tab in existing parent dashboard.
Uses standard system UI (not child-friendly) — parent is an adult.
Parent completes activities on behalf of learner.
Progress and XP still tracked against the student record.

---

## Parent progress view (from parent dashboard)

"My child's progress" tab:
- Overall % (large, crimson)
- Total XP and stars earned
- Scores per graded activity (lesson title, activity, score/max, date)
- Does NOT show lesson content, materials, or activity instructions
- Simple encouraging note based on overall progress

---

## Notifications

| Event | Who notified | Method |
|---|---|---|
| Lesson plan published | Learner or Parent | In-system only |
| Activity submitted | SPED Teacher | In-system only |
| Activity graded | Learner + Parent | In-system only |
| Badge earned | Learner | In-system only |
| Learner account created | Learner | PHPMailer (credentials email) |

---

## New DB tables (schema.sql only — show migration diff first)

```sql
learner_points  — id, student_id, points, reason (varchar),
                  source_type (view/submission/quiz/lesson_bonus/badge_bonus),
                  source_id (nullable), earned_at

learner_badges  — id, student_id, badge_key (varchar), earned_at
                  (badge_key values: first_activity, lesson_complete,
                  perfect_score, five_in_a_row, all_done, star_collector)

activity_stars  — id, submission_id, student_id, stars (1/2/3),
                  calculated_at
```

No changes to any existing tables from Processes 1–6.

---

## File paths

```
/uploads/activity_submissions/{student_id}/ — learner file submissions
```

All learner submissions also inserted into `student_documents` with
`process_name = 'activity_submission'`.

---

## Build order (one feature at a time — describe and ask before building)

1. schema.sql migration — learner_points, learner_badges, activity_stars tables
2. Learner sidebar + top bar + layout shell
3. Dashboard greeting + overall progress bar + 3 stat cards
4. Tab system — My Lessons / My Progress / My Badges (JS-driven)
5. My Lessons tab — 2-column lesson card grid
6. My Progress tab — overall stats + progress message
7. My Badges tab — badge shelf (earned + locked)
8. Lesson viewer — materials display (PDF, video, YouTube nocookie, Drive embed)
9. View-only activity — auto-log + I read it button + XP award
10. File submission activity — upload zone (file + camera) + success card + XP
11. Quiz activity — step-by-step, MC tappable cards, short answer, score reveal + XP
12. Stars calculation — auto on grade (score% → 1/2/3 stars) → activity_stars
13. XP calculation — auto on events → learner_points
14. Badge calculation — auto-check after every submission/grade → learner_badges
15. Learner account creation + PHPMailer credentials email
16. Parent-managed mode — "My child's lessons" tab in parent dashboard
17. Parent progress view — overall stats + scores, no content

---

## Self-check (run before presenting any code)

- [ ] Processes 1–6 files not touched — no exceptions
- [ ] New DB tables only: learner_points, learner_badges, activity_stars
- [ ] No changes to existing tables from Processes 1–6
- [ ] schema.sql is the only place for DB changes
- [ ] Sidebar: dark navy #1a3560, crimson active state, hamburger on mobile
- [ ] Top bar: stars + XP pills always visible
- [ ] Lesson cards: 2-column grid, color-coded top border, stars, progress bar
- [ ] Stars auto-calculated from score% — never manual
- [ ] XP auto-awarded per event — never manual
- [ ] Badges auto-checked after every submission and grade
- [ ] Locked badges visible in badge shelf (grayed out)
- [ ] Quiz: step-by-step, MC auto-scored, short answer manual
- [ ] YouTube embeds: youtube-nocookie.com, no autoplay
- [ ] File + camera on all upload zones (reusable component)
- [ ] All submissions inserted into student_documents
- [ ] Parent sees scores/progress only — no lesson content
- [ ] Rounded corners: 16px cards, 20px buttons, 12px badges
- [ ] Soft card shadows: 0 2px 8px rgba(0,0,0,0.07)
- [ ] Color scheme #a01422 and #1e4072 — no default Bootstrap blue or gray
- [ ] No gradients, no mascots, no animations, no characters
- [ ] Prepared statements for all DB operations
- [ ] RBAC middleware on every route
- [ ] No blank pages or raw errors shown to learner — always friendly message
