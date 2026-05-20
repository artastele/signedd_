---
inclusion: auto
description: Role-based access control, user roles, permissions, and post-login flow. Use when creating or modifying auth, routes, middleware, dashboards, or role-related features.
---

# SPED LMS — RBAC & Authorization

## Roles (mapped from DFD — do not rename or remove)

| Role | DFD Processes | Access Level |
|---|---|---|
| `admin` | All | Full system — DB-assigned only, never self-selectable |
| `sped_teacher` | 2, 3, 6, 7 | Assessment, IEP implementation, student records |
| `guidance` | 4, 5 | IEP meeting facilitation, insights, signature |
| `principal` | 5 | IEP signing, remarks, final sign-off |
| `master_teacher` | 9 (future) | Class observation, COT result submission |
| `parent` | 1, 7 | Enrollment submission, child progress viewing |
| `user` | None | Default on login — role selection only |

> "staff" is NOT a valid role. Every role maps to a specific DFD actor.

## Post-login flow

1. **All users** land on General Dashboard with `role = user`
   - Visible: welcome message, role selection prompt, account settings only
   - No process features accessible

2. **Role selection screen** — one card per role:
   `[ SPED Teacher ]` `[ Guidance ]` `[ Principal ]` `[ Master Teacher ]` `[ Parent ]`

3a. **SPED Teacher / Guidance / Principal / Master Teacher:**
   - Role verification form shown:
     - Upload government-issued ID
     - Upload proof of designation (appointment letter, DepEd order, school ID)
     - Optional: employee/DepEd number
   - On submit: `role_requests` entry created with `status = pending`
   - User stays on General Dashboard — "Verification pending — [role]" banner shown
   - Admin notified via PHPMailer
   - On approval: role updated, user notified via email, redirected to role dashboard
   - On rejection: user notified with reason, can resubmit
   - While pending: treated as `user` for ALL access checks

3b. **Parent:**
   - No documents required
   - Role updated immediately to `parent`
   - Redirected to Parent Dashboard:
     - Submit/track enrollment documents (Process 1)
     - View child's progress and activity records (Process 7)
     - Receive IEP meeting notifications (Process 4)

## Middleware
- Every route passes through `RoleMiddleware.php`
- Permissions source of truth: `/config/permissions.php`
- `pending` role = same access as `user`
- Unauthorized: return 403 — never silently redirect or expose data
- `admin` cannot be self-selected — assigned only via DB or existing admin

## Permissions map (`/config/permissions.php`)
```php
return [
  'user'           => ['dashboard.general', 'account.settings', 'role.select'],
  'parent'         => ['dashboard.parent', 'enrollment.submit', 'progress.view', 'notifications.iep'],
  'sped_teacher'   => ['dashboard.teacher', 'student.records', 'assessment.manage',
                       'iep.implement', 'learning.materials', 'activity.logs'],
  'guidance'       => ['dashboard.guidance', 'iep.meeting', 'iep.sign', 'iep.insights'],
  'principal'      => ['dashboard.principal', 'iep.sign', 'iep.remarks', 'iep.approve'],
  'master_teacher' => ['dashboard.master', 'observation.conduct', 'cot.submit'],
  'admin'          => ['*'],
];
```

## DB tables (add to schema.sql only)
```sql
-- users: id, name, email, password_hash, role, status, created_at
-- role_requests: id, user_id, requested_role, status (pending/approved/rejected),
--                submitted_docs (JSON), reviewed_by, review_note, created_at, updated_at
-- role_documents: id, role_request_id, file_path, file_type, uploaded_at
```

## UI rules
- Role selection: one card per role, navy border, name + description + badge
- Parent card badge: "Instant access" (green pill)
- Teacher/Guidance/Principal/Master Teacher badge: "Requires verification" (amber pill)
- Pending banner: crimson bg (#a01422), white text, dismissible
- Current role in navbar: pill badge — navy = active, crimson = pending
- Admin approval panel: table with requester, role, viewable docs, approve/reject + note
