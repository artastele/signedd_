---
inclusion: manual
---

# Processes 1, 2 & 3 — LOCKED (100% Complete)

> ⛔ DO NOT ALTER — Processes 1, 2 & 3 are marked 100% COMPLETE and APPROVED.
> These files must NOT be modified under any circumstance without explicit user approval.

## Process 1 — Enrollment Submission (Parent)
- `app/Controllers/EnrollmentController.php`
- `app/Models/EnrollmentModel.php`
- `app/Views/enrollment/` (all files)
- `app/Views/dashboard/parent.php`

## Process 2 — Enrollment Verification (SPED Teacher)
- `app/Controllers/VerificationController.php`
- `app/Models/StudentModel.php`
- `app/Views/verification/` (all files)
- `app/Views/students/` (all files)

## Process 3 — Initial Assessment (SPED Teacher)
- `app/Controllers/AssessmentController.php`
- `app/Models/AssessmentModel.php`
- `app/Models/AssessmentServiceModel.php`
- `app/Views/assessment/` (all files)

## Rule
Any bug found in Processes 1, 2, or 3 must be **described to the user first and approved before any code is touched.**
Never modify these files as a side effect of working on Process 4, 5, 6, or 7.

## Overview
Process 1 is the entry point for the SPED LMS. Parents submit enrollment documents and learner information through a multi-step form. This process captures all required documents (PSA, PWD ID/Medical Record, BEEF) and stores the submission for verification by SPED Teachers in Process 2.

## DFD Specification
- **Actor:** Parent
- **Inputs:** 
  - PSA (Philippine Statistics Authority birth certificate)
  - PWD ID or Medical Record
  - BEEF (Learner Educational Assessment Form)
  - Learner biographical information
- **Output:** Enrollment submission → Process 2 (Verifying Enrollment Requirements)
- **Data Store:** `enrollment_submissions` table

## User Flow
1. Parent logs in or registers
2. Parent navigates to enrollment form
3. Parent completes multi-step form:
   - Step 1: Learner Information (name, birthdate, gender, etc.)
   - Step 2: Current Address
   - Step 3: Parent/Guardian Information
   - Step 4: Previous School Information
   - Step 5: Enrollment Details (grade level, special needs category)
   - Step 6: Learning Modality (in-person, online, hybrid)
   - Step 7: Document Upload & Signature
4. Parent reviews submission
5. Parent submits enrollment
6. System stores submission in `enrollment_submissions`
7. Parent receives confirmation email
8. Submission moves to Process 2 queue for teacher verification

## Database Schema Requirements

### Table: `enrollment_submissions`
```sql
CREATE TABLE IF NOT EXISTS enrollment_submissions (
  id INT PRIMARY KEY AUTO_INCREMENT,
  parent_id INT NOT NULL,
  learner_first_name VARCHAR(100) NOT NULL,
  learner_middle_name VARCHAR(100),
  learner_last_name VARCHAR(100) NOT NULL,
  learner_birthdate DATE NOT NULL,
  learner_gender ENUM('Male', 'Female', 'Other') NOT NULL,
  learner_pwd_category VARCHAR(100),
  current_address_street VARCHAR(255),
  current_address_barangay VARCHAR(100),
  current_address_city VARCHAR(100),
  current_address_province VARCHAR(100),
  current_address_postal_code VARCHAR(10),
  parent_guardian_name VARCHAR(100) NOT NULL,
  parent_guardian_relationship VARCHAR(50),
  parent_guardian_contact VARCHAR(20),
  previous_school_name VARCHAR(255),
  previous_school_year_from INT,
  previous_school_year_to INT,
  enrollment_grade_level VARCHAR(50),
  enrollment_special_needs_category VARCHAR(100),
  learning_modality ENUM('In-person', 'Online', 'Hybrid') NOT NULL,
  psa_document_path VARCHAR(255),
  pwd_id_document_path VARCHAR(255),
  beef_document_path VARCHAR(255),
  parent_signature_path VARCHAR(255),
  submission_status ENUM('Draft', 'Submitted', 'Verified', 'Rejected') DEFAULT 'Draft',
  submitted_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (parent_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_parent_id (parent_id),
  INDEX idx_submission_status (submission_status)
);
```

### Table: `enrollment_documents`
```sql
CREATE TABLE IF NOT EXISTS enrollment_documents (
  id INT PRIMARY KEY AUTO_INCREMENT,
  enrollment_submission_id INT NOT NULL,
  document_type ENUM('PSA', 'PWD_ID', 'Medical_Record', 'BEEF') NOT NULL,
  file_path VARCHAR(255) NOT NULL,
  file_size INT,
  mime_type VARCHAR(50),
  uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (enrollment_submission_id) REFERENCES enrollment_submissions(id) ON DELETE CASCADE,
  INDEX idx_enrollment_submission_id (enrollment_submission_id)
);
```

## Controller: `EnrollmentController.php`

### Methods Required
- `showForm()` — Display enrollment form (GET /enrollment)
- `saveDraft()` — Save form as draft (POST /enrollment/draft)
- `submitEnrollment()` — Submit completed enrollment (POST /enrollment/submit)
- `getStatus()` — Check submission status (GET /enrollment/status)
- `uploadDocument()` — Handle file uploads (POST /enrollment/upload)

### Security Rules
- **RBAC:** Only `parent` role can access enrollment form
- **Middleware:** Apply `RoleMiddleware` to verify parent role
- **Permissions:** Check `config/permissions.php` for `enrollment.submit` permission
- **File Upload:** Validate file types (PDF, JPG, PNG only), max 5MB per file
- **Session:** Verify parent is logged in before allowing form access

## Model: `EnrollmentModel.php`

### Methods Required
- `createSubmission($parentId, $data)` — Insert new enrollment submission
- `updateSubmission($submissionId, $data)` — Update draft submission
- `getSubmissionById($submissionId)` — Retrieve submission details
- `getSubmissionsByParent($parentId)` — Get all submissions for a parent
- `submitEnrollment($submissionId)` — Mark submission as submitted
- `uploadDocument($submissionId, $documentType, $filePath)` — Store document reference
- `getDocuments($submissionId)` — Retrieve all documents for a submission

### Database Queries
- All queries use **PDO prepared statements**
- No raw SQL strings
- Validate all inputs before querying
- Use transactions for multi-step operations (e.g., submit + upload documents)

## Views: `/app/Views/enrollment/`

### Files Required
- `form.php` — Main enrollment form container
- `steps/step1_learner_info.php` — Learner biographical information
- `steps/step2_current_address.php` — Current residential address
- `steps/step3_parent_guardian.php` — Parent/guardian contact information
- `steps/step4_previous_school.php` — Previous school history
- `steps/step5_enrollment_details.php` — Grade level and special needs category
- `steps/step6_learning_modality.php` — Learning modality selection
- `steps/step7_documents_signature.php` — Document upload and parent signature
- `status.php` — Submission status display
- `review_detail.php` — Review submission before final submit

### UI Requirements
- **Color Scheme:** Primary #a01422 (crimson), Secondary #1e4072 (navy)
- **Layout:** Multi-step form with progress indicator
- **Buttons:** 
  - "Save Draft" (secondary, #1e4072)
  - "Next Step" (primary, #a01422)
  - "Submit Enrollment" (primary, #a01422)
  - "Cancel" (secondary)
- **Form Fields:** Floating labels, crimson focus ring
- **Validation:** Client-side (HTML5) + server-side (PHP)
- **Error Messages:** Display in alert boxes (crimson background for errors)
- **Success Messages:** Display in alert boxes (green background)

## File Upload Handling

### Requirements
- Store files in `/public/uploads/enrollment/` directory
- Organize by parent ID: `/public/uploads/enrollment/{parent_id}/`
- Allowed file types: PDF, JPG, PNG
- Max file size: 5MB per file
- Rename files to prevent conflicts: `{document_type}_{timestamp}.{ext}`
- Validate MIME type server-side
- Scan for malicious content before storing

### Security
- Never store files in web-accessible directory without access control
- Implement download endpoint that checks permissions before serving files
- Log all file uploads for audit trail

## Email Notifications

### Confirmation Email (on successful submission)
- **To:** Parent email
- **Subject:** "Enrollment Submission Received — SPED LMS"
- **Content:**
  - Confirmation of submission
  - Submission reference number
  - List of documents received
  - Next steps (teacher verification timeline)
  - Link to check status

### Implementation
- Use `MailHelper.php` with PHPMailer
- Send asynchronously if possible (queue system)
- Log all email sends in activity log

## Validation Rules

### Learner Information
- First name: Required, max 100 chars, letters only
- Middle name: Optional, max 100 chars
- Last name: Required, max 100 chars, letters only
- Birthdate: Required, must be reasonable age (3-25 years old)
- Gender: Required, select from dropdown
- PWD Category: Optional, select from predefined list

### Address Information
- Street: Required, max 255 chars
- Barangay: Required, select from dropdown (linked to city)
- City: Required, select from dropdown (linked to province)
- Province: Required, select from dropdown
- Postal Code: Optional, max 10 chars

### Parent/Guardian Information
- Name: Required, max 100 chars
- Relationship: Required, select from dropdown
- Contact Number: Required, valid Philippine phone format

### Documents
- PSA: Required, PDF/JPG/PNG, max 5MB
- PWD ID or Medical Record: Required, PDF/JPG/PNG, max 5MB
- BEEF: Required, PDF, max 5MB
- Parent Signature: Required, image file, max 5MB

## Error Handling

### Common Errors
- **Missing required fields:** Display field-level error messages
- **Invalid file type:** Show error with allowed types
- **File too large:** Show error with max size
- **Upload failure:** Log error, show generic message to user
- **Database error:** Log error, show generic message to user

### Logging
- Log all submissions (successful and failed) in activity log
- Log all file uploads with file names and sizes
- Log all validation errors for debugging

## Testing Checklist (Process 1)

- [ ] Parent can access enrollment form
- [ ] Form displays all 7 steps correctly
- [ ] Draft save works without submitting
- [ ] Form validation catches missing required fields
- [ ] File upload accepts valid file types
- [ ] File upload rejects invalid file types
- [ ] File upload rejects files over 5MB
- [ ] Parent can review submission before final submit
- [ ] Submission creates record in `enrollment_submissions` table
- [ ] Documents are stored in correct directory
- [ ] Confirmation email is sent to parent
- [ ] Submission status shows as "Submitted"
- [ ] Parent can check submission status
- [ ] Non-parent users cannot access enrollment form
- [ ] Session timeout redirects to login

## Integration Points

### With Process 2
- Submitted enrollments appear in teacher verification queue
- Teacher can view all documents uploaded in Process 1
- Teacher can reject submission back to parent for corrections

### With Authentication
- Parent must be logged in to access enrollment form
- Parent role verified via RBAC middleware
- Session must be active throughout form completion

### With Notifications
- Confirmation email sent via MailHelper
- Activity log records all submissions
- Admin dashboard shows enrollment submission metrics

## Notes
- This process is the foundation for all subsequent processes
- Data quality here directly impacts teacher verification in Process 2
- Document storage must be secure and backed up regularly
- Consider implementing draft auto-save to prevent data loss
