---
inclusion: always
---

# SPED LMS — Project Structure

## Directory layout (never deviate)
```
/app
  /Controllers     — one controller per process (e.g. EnrollmentController.php)
  /Models          — one model per entity (e.g. StudentModel.php)
  /Views           — one folder per process (e.g. /views/enrollment/index.php)
/config
  db.php           — PDO connection only
  schema.sql       — ONLY place for ALL DB schema and migrations
  permissions.php  — ONLY place for role-permission map
/public
  /css
    custom.css     — all Bootstrap overrides and custom styles
  /js
/routes
  web.php          — all route definitions
/logs
  activity.log     — admin actions
  login.log        — login attempts
CHANGELOG.md       — updated after every approved feature
```

## Layer rules (enforce strictly — flag violations)
- **Controllers:** HTTP logic only — no SQL, no HTML, no business rules
- **Models:** all DB queries — no raw SQL outside of models
- **Views:** HTML + minimal PHP `echo` only — zero business logic
- **Routes:** URL → controller method mapping only

## File naming
- Controllers: `PascalCase` + `Controller.php` suffix (e.g. `EnrollmentController.php`)
- Models: `PascalCase` + `Model.php` suffix (e.g. `StudentModel.php`)
- Views: `snake_case.php` inside process-named folders (e.g. `views/enrollment/index.php`)
- Config files: `snake_case.php`

## File header (required on every PHP file)
```php
<?php
// DO NOT ALTER WITHOUT APPROVAL — [Process N or Security Module N]
// Last modified: YYYY-MM-DD
// Part of: SPED LMS — [feature name]
```

## CHANGELOG.md format (update after every approved feature)
```markdown
## [vN] — Feature Name (Process N or Security Module N)
- **Built:** [what was created]
- **Tables added/modified:** [schema.sql changes if any]
- **Tested:** [what was verified]
- **Status:** Approved
- **Date:** YYYY-MM-DD
```
