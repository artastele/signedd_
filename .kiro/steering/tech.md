---
inclusion: always
---

# SPED LMS — Technology Stack

## Stack
- **Backend:** PHP (strict OOP/MVC) — no procedural spaghetti, no mixed HTML/PHP logic
- **Database:** MySQL 8+ — PDO only, never mysqli_
- **Frontend:** Bootstrap 5 — heavily customized (see UI rules below), never default styles
- **Email:** PHPMailer — for IEP meeting notifications, role approval/rejection emails
- **APIs:** REST — all endpoints must check RBAC permissions before processing

## Database rules
- PDO with prepared statements everywhere — no raw string queries, ever
- All schema changes go ONLY into `/config/schema.sql` — never inside controllers, models, or setup scripts
- Every table uses `CREATE TABLE IF NOT EXISTS`
- Column additions use `ALTER TABLE ... ADD COLUMN IF NOT EXISTS`
- `schema.sql` must be self-contained — running it fresh on an empty DB produces the full schema
- SchemaManager class auto-applies migrations on app boot using a `db_version` table
- Migration blocks wrapped as: `-- MIGRATION: v{N}` ... `-- END MIGRATION: v{N}`

## UI design rules — custom Bootstrap (not generic)

Override Bootstrap in `/public/css/custom.css`:
```css
--bs-primary: #a01422;
--bs-secondary: #1e4072;
--bs-body-bg: #f5f5f5;
--bs-border-radius: 6px;
```

Component rules:
- **Buttons:** #a01422 crimson = primary actions | #1e4072 navy = secondary — NEVER default Bootstrap blue
- **Navbar:** #1e4072 navy bg, white text, #a01422 crimson active indicator
- **Cards:** white bg, subtle left-border accent in crimson or navy — no default gray shadow cards
- **Tables:** striped #f9f9f9, navy (#1e4072) header row with white text
- **Badges:** crimson = alerts/pending | navy = info | #3b6d11 green = success | amber = warning
- **Forms:** floating labels, crimson focus ring
- **Sidebar:** dark navy #1a3560, white links, crimson active state
- **BANNED:** default Bootstrap blue `.btn-primary`, default gray cards, unstyled tables

Every view must feel like a single cohesive institutional design system — not a default Bootstrap template.
