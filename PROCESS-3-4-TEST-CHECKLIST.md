# Process 3 & 4 — Testing Checklist

> **Purpose:** Comprehensive testing guide for Process 3 (Conducting Initial Assessment) and Process 4 (Facilitating IEP Meeting)  
> **Last Updated:** 2026-05-07  
> **Status:** Ready for Testing

---

## Pre-Testing Setup

### Required Test Accounts
- [ ] **SPED Teacher** account with `sped_teacher` role
- [ ] **Guidance** account with `guidance` role
- [ ] **Principal** account with `principal` role
- [ ] **Parent** account with verified enrollment (from Process 2)
- [ ] At least 1 verified student record in `student_records` table

### Database Verification
- [ ] All migrations v1.22-v1.28 applied successfully
- [ ] Tables exist: `assessment_records`, `assessment_services`, `assessment_documents`, `assessment_checklists`
- [ ] Tables exist: `user_availability`, `iep_meetings`, `meeting_notifications`
- [ ] Tables exist: `pdsp_records`, `pdsp_domains`, `pdsp_signatures`
- [ ] Upload directories exist: `uploads/assessments/`, `uploads/signatures/`
- [ ] Directories are writable (chmod 755 or 777)

### Browser Setup
- [ ] Test in Chrome (primary)
- [ ] Test in Firefox (secondary)
- [ ] Test on mobile device (responsive check)
- [ ] JavaScript enabled
- [ ] Cookies enabled

---

## PROCESS 3 — Conducting Initial Assessment

### Feature 1: Student Selection & Auto-Fill (Section A)

#### Test Case 3.1.1: Student Selector
- [ ] Login as SPED Teacher
- [ ] Navigate to "Conduct Assessment" from sidebar
- [ ] Verify page loads without errors
- [ ] Verify student dropdown shows all verified students
- [ ] Select a student from dropdown
- [ ] Verify student data loads via AJAX

#### Test Case 3.1.2: Auto-Fill Behavior
- [ ] After selecting student, verify these fields auto-fill:
  - [ ] Last Name, First Name, Middle Name, Name Extension
  - [ ] Date of Birth, Age (calculated), Sex, Religion
  - [ ] Home Address (complete)
  - [ ] LRN, School, School Year
  - [ ] Name of Adviser
  - [ ] Father: Name, Contact, Occupation
  - [ ] Mother: Name, Contact, Occupation
  - [ ] Guardian: Name, Contact, Occupation
  - [ ] Previous School Attended, Grade Level
  - [ ] With IEP (yes/no), With support service/s (yes/no)
  - [ ] Support services detail
  - [ ] Education History
- [ ] Verify all auto-filled fields are EDITABLE
- [ ] Edit an auto-filled field and verify it saves

#### Test Case 3.1.3: Services Checklist
- [ ] Verify all 8 service checkboxes render:
  - [ ] Occupational Therapy
  - [ ] Physical Therapy
  - [ ] Behavioral Therapy
  - [ ] Psychosocial Intervention
  - [ ] Speech and Language Therapy
  - [ ] Daily Living Skills
  - [ ] Skills Development
  - [ ] Others (with text input)
- [ ] Check "Occupational Therapy"
- [ ] Verify checkbox state persists
- [ ] Check "Others" and enter custom text
- [ ] Verify custom text saves

#### Test Case 3.1.4: Screening Types
- [ ] Verify screening checkboxes render:
  - [ ] MFAT
  - [ ] ECCD Checklist
  - [ ] Psycho-Educational
- [ ] Check at least one screening type
- [ ] Verify selection saves

### Feature 2: Dynamic MDT Table (Section B)

#### Test Case 3.2.1: Service-Driven Table
- [ ] Start with NO services checked
- [ ] Verify MDT table is empty or shows "No services selected"
- [ ] Check "Occupational Therapy" in Section A
- [ ] Verify MDT table immediately shows 1 row for "Occupational Therapy"
- [ ] Check "Speech and Language Therapy"
- [ ] Verify MDT table now shows 2 rows
- [ ] Uncheck "Occupational Therapy"
- [ ] Verify MDT table removes that row (only Speech row remains)

#### Test Case 3.2.2: MDT Members Management
- [ ] For a service row, click "Add MDT Member" button
- [ ] Verify input fields appear: Name, Designation
- [ ] Enter member name: "Dr. Juan Dela Cruz"
- [ ] Enter designation: "Occupational Therapist"
- [ ] Click "Add" or save
- [ ] Verify member appears in list
- [ ] Add a second member to same service
- [ ] Verify both members show
- [ ] Remove a member
- [ ] Verify member is removed from list

#### Test Case 3.2.3: Date of Assessment
- [ ] For a service row, click date picker
- [ ] Select a date
- [ ] Verify date displays correctly
- [ ] Try selecting future date
- [ ] Verify system accepts or rejects based on business rules

#### Test Case 3.2.4: File Upload per Service
- [ ] For a service row, click "Upload Document" button
- [ ] Select a valid JPG file (< 10MB)
- [ ] Verify upload succeeds, filename displays
- [ ] Try uploading invalid file type (.txt)
- [ ] Verify error message: "Only jpg, png, pdf allowed"
- [ ] Try uploading oversized file (> 10MB)
- [ ] Verify error message: "File too large"
- [ ] Upload a PDF file
- [ ] Verify upload succeeds
- [ ] Upload a PNG file
- [ ] Verify upload succeeds

### Feature 3: Draft Saving

#### Test Case 3.3.1: Save Draft
- [ ] Fill in partial assessment data
- [ ] Click "Save Draft" button
- [ ] Verify success message appears
- [ ] Verify page redirects or stays with confirmation
- [ ] Check database: `assessment_records` has status = 'draft'
- [ ] Logout and login again
- [ ] Navigate back to "Conduct Assessment"
- [ ] Select same student
- [ ] Verify draft data loads (all fields populated)

#### Test Case 3.3.2: Multiple Drafts
- [ ] Save draft for Student A
- [ ] Navigate away
- [ ] Start new assessment for Student B
- [ ] Save draft for Student B
- [ ] Verify both drafts exist in database
- [ ] Load Student A's draft
- [ ] Verify correct data loads (not Student B's data)

### Feature 4: Assessment Submission & Versioning

#### Test Case 3.4.1: Submit Assessment
- [ ] Complete all required fields
- [ ] Check at least 1 service
- [ ] Add MDT members for checked service
- [ ] Upload at least 1 document
- [ ] Click "Submit Assessment" button
- [ ] Verify confirmation prompt appears
- [ ] Confirm submission
- [ ] Verify success message
- [ ] Check database: `assessment_records` has status = 'finalized'
- [ ] Verify version = 1 for first assessment

#### Test Case 3.4.2: Versioning
- [ ] Submit assessment for Student A (version 1)
- [ ] Navigate back to "Conduct Assessment"
- [ ] Select same Student A
- [ ] Fill in NEW assessment data (different from v1)
- [ ] Submit assessment
- [ ] Verify version = 2 in database
- [ ] Check database: both v1 and v2 exist (v1 NOT overwritten)
- [ ] Verify v1 data is preserved
- [ ] Verify v2 data is separate

#### Test Case 3.4.3: Linked Files Preservation
- [ ] Submit assessment v1 with 2 uploaded files
- [ ] Submit assessment v2 with 1 uploaded file
- [ ] Check database: `assessment_documents` table
- [ ] Verify v1 files still linked to v1 assessment_service_id
- [ ] Verify v2 file linked to v2 assessment_service_id
- [ ] Verify v1 files are NOT deleted

### Feature 5: Assessment History

#### Test Case 3.5.1: History View
- [ ] Navigate to Student Records
- [ ] Click on a student with multiple assessments
- [ ] Click "Assessment History" tab or link
- [ ] Verify all versions display in timeline
- [ ] Verify each version shows: version number, date, conducted by, status

#### Test Case 3.5.2: View Version Details
- [ ] In assessment history, click "View" on version 1
- [ ] Verify all Section A data displays
- [ ] Verify all Section B data displays (services, MDT, dates)
- [ ] Verify uploaded documents show with download links
- [ ] Click "Download" on a document
- [ ] Verify file downloads correctly
- [ ] Go back and view version 2
- [ ] Verify version 2 data is different from version 1

---

## PROCESS 4 — Facilitating IEP Meeting

### Feature 1: Availability Calendar

#### Test Case 4.1.1: Set Recurring Availability (SPED Teacher)
- [ ] Login as SPED Teacher
- [ ] Navigate to "My Availability" from sidebar
- [ ] Verify calendar page loads
- [ ] Verify "Set Weekly Schedule" panel displays
- [ ] Check Monday, Wednesday, Friday checkboxes
- [ ] Click "Save Weekly Schedule"
- [ ] Verify success message
- [ ] Check database: `user_availability` has 3 rows (type='recurring', day_of_week=1,3,5)
- [ ] Refresh page
- [ ] Verify checkboxes remain checked

#### Test Case 4.1.2: Set Recurring Availability (Guidance)
- [ ] Login as Guidance
- [ ] Navigate to "My Availability"
- [ ] Check Tuesday, Thursday checkboxes
- [ ] Save
- [ ] Verify success message
- [ ] Check database: 2 rows for guidance user

#### Test Case 4.1.3: Set Recurring Availability (Principal)
- [ ] Login as Principal
- [ ] Navigate to "My Availability"
- [ ] Check Monday, Tuesday, Wednesday checkboxes
- [ ] Save
- [ ] Verify success message

#### Test Case 4.1.4: Calendar View
- [ ] After setting recurring schedule, view monthly calendar
- [ ] Verify available days highlighted in navy (#1e4072)
- [ ] Verify unavailable days in light gray
- [ ] Navigate to next month
- [ ] Verify recurring pattern applies to next month

#### Test Case 4.1.5: Exception Dates (Unavailable Override)
- [ ] Find a date that is normally available (e.g., Monday)
- [ ] Click on that date in calendar
- [ ] Select "Mark as Unavailable" or toggle
- [ ] Verify date now shows as unavailable (gray)
- [ ] Verify small crimson dot indicator appears
- [ ] Check database: `user_availability` has row with type='exception', specific_date=that date, is_available=0
- [ ] Refresh page
- [ ] Verify exception persists

#### Test Case 4.1.6: Exception Dates (Available Override)
- [ ] Find a date that is normally unavailable (e.g., Saturday)
- [ ] Click on that date
- [ ] Select "Mark as Available"
- [ ] Verify date now shows as available (navy)
- [ ] Verify crimson dot indicator appears
- [ ] Check database: exception row with is_available=1

### Feature 2: Meeting Scheduling

#### Test Case 4.2.1: Suggested Dates
- [ ] Login as SPED Teacher
- [ ] Navigate to "IEP Meetings" → "Schedule Meeting"
- [ ] Select a student with finalized assessment
- [ ] Verify "Suggested Dates" section displays
- [ ] Verify suggested dates are dates where ALL THREE roles (SPED Teacher, Guidance, Principal) are available
- [ ] Verify suggested dates highlighted in green or with checkmark icon
- [ ] Verify next 60 days are checked

#### Test Case 4.2.2: Schedule Meeting (Suggested Date)
- [ ] Select a suggested date from list
- [ ] Enter time: "10:00 AM"
- [ ] Select venue: "Guidance Office"
- [ ] Enter agenda notes: "Discuss IEP goals for Q1"
- [ ] Click "Schedule Meeting"
- [ ] Verify success message
- [ ] Check database: `iep_meetings` has new row with status='scheduled'

#### Test Case 4.2.3: Schedule Meeting (Manual Override)
- [ ] Select a date that is NOT suggested (not all available)
- [ ] Verify "Override Reason" field appears
- [ ] Enter reason: "Urgent meeting, parent requested this date"
- [ ] Fill in time, venue, agenda
- [ ] Submit
- [ ] Verify success message
- [ ] Check database: override reason saved

#### Test Case 4.2.4: Online Meeting Link
- [ ] Schedule a meeting
- [ ] Toggle to "Online Meeting"
- [ ] Verify "Venue" field hides
- [ ] Verify "Online Link" field appears
- [ ] Enter link: "https://meet.google.com/abc-defg-hij"
- [ ] Submit
- [ ] Verify link saved in database

#### Test Case 4.2.5: Email Notifications
- [ ] Schedule a meeting
- [ ] Check email inbox for Guidance user
- [ ] Verify email received with:
  - [ ] Student name
  - [ ] Meeting date and time
  - [ ] Venue or online link
  - [ ] Agenda notes
  - [ ] Scheduled by (SPED Teacher name)
- [ ] Check email for Principal
- [ ] Verify same email received
- [ ] Check email for Parent (linked to student)
- [ ] Verify parent received email

#### Test Case 4.2.6: In-System Notifications
- [ ] Login as Guidance
- [ ] Check notification bell icon
- [ ] Verify notification appears: "New IEP Meeting scheduled for [Student Name]"
- [ ] Click notification
- [ ] Verify redirects to meeting details page

### Feature 3: Rescheduling

#### Test Case 4.3.1: Reschedule Meeting
- [ ] Login as SPED Teacher
- [ ] Navigate to "IEP Meetings"
- [ ] Find a scheduled meeting
- [ ] Click "Reschedule" button
- [ ] Verify suggested dates re-calculated
- [ ] Select new date
- [ ] Enter reason: "Principal unavailable on original date"
- [ ] Submit
- [ ] Verify success message
- [ ] Check database: status changed to 'rescheduled'

#### Test Case 4.3.2: Reschedule Notifications
- [ ] After rescheduling, check Guidance email
- [ ] Verify email received: "IEP Meeting Rescheduled"
- [ ] Verify new date, time, reason displayed
- [ ] Check Principal email
- [ ] Verify same notification
- [ ] Check Parent email
- [ ] Verify parent notified

### Feature 4: PDSP Form (Part II)

#### Test Case 4.4.1: Access PDSP Form
- [ ] Login as SPED Teacher
- [ ] Navigate to "IEP Meetings"
- [ ] Find a scheduled meeting
- [ ] Click "Fill PDSP Form" button
- [ ] Verify PDSP form page loads
- [ ] Verify student name displays at top
- [ ] Verify meeting details display

#### Test Case 4.4.2: Manual Form Fill
- [ ] Verify 6 domain sections display:
  1. [ ] Perceptuo-Cognitive
  2. [ ] Psychosocial
  3. [ ] Socio-Emotional
  4. [ ] Psychomotor
  5. [ ] Daily Living Skills
  6. [ ] Communication and Language
- [ ] For Domain 1 (Perceptuo-Cognitive):
  - [ ] Enter Sub-Domain: "Visual Perception"
  - [ ] Enter Skills Description: "Can identify colors and shapes"
  - [ ] Toggle Mastered: Yes
  - [ ] Enter Educational Recommendation: "Continue with advanced visual tasks"
  - [ ] Select Q1 Level: "Proficient"
  - [ ] Select Q2 Level: "Advanced"
- [ ] Click "Add Row" to add another sub-domain
- [ ] Verify new row appears
- [ ] Fill in second row
- [ ] Repeat for at least 2 more domains

#### Test Case 4.4.3: Save PDSP Draft
- [ ] After filling partial data, click "Save Draft"
- [ ] Verify success message
- [ ] Check database: `pdsp_records` has status='draft'
- [ ] Check database: `pdsp_domains` has rows for filled domains
- [ ] Navigate away
- [ ] Come back to same meeting
- [ ] Click "Fill PDSP Form"
- [ ] Verify draft data loads (all fields populated)

#### Test Case 4.4.4: AI Extraction (Claude Vision)
- [ ] Have a handwritten or printed PDSP form ready (photo or PDF)
- [ ] Click "Upload for AI Extraction" button
- [ ] Select image file (JPG or PNG)
- [ ] Verify upload progress indicator
- [ ] Wait for AI processing (may take 5-10 seconds)
- [ ] Verify success message: "AI extraction complete. Please review all fields."
- [ ] Verify form fields auto-populated with extracted data
- [ ] Review each field for accuracy
- [ ] Correct any misread fields
- [ ] Save form

#### Test Case 4.4.5: AI Extraction Failure Handling
- [ ] Upload a completely blank image
- [ ] Verify AI returns empty or minimal data
- [ ] Verify friendly message: "AI extraction unavailable. Please fill manually."
- [ ] Verify form remains editable
- [ ] Verify no crash or raw error displayed
- [ ] Check logs: error logged to `logs/activity.log`

#### Test Case 4.4.6: Submit Complete PDSP
- [ ] Fill all 6 domains completely
- [ ] Click "Submit PDSP Form"
- [ ] Verify confirmation prompt
- [ ] Confirm submission
- [ ] Verify success message
- [ ] Check database: `pdsp_records` status still 'draft' (not complete until all signatures)
- [ ] Verify redirect to signature page or meeting list

### Feature 5: PDSP Signature Pad

#### Test Case 4.5.1: Access Signature Page
- [ ] After submitting PDSP form, verify "Sign PDSP" button appears
- [ ] Click "Sign PDSP"
- [ ] Verify signature page loads
- [ ] Verify PDSP form data displays (read-only)
- [ ] Verify 8 signature slots display:
  1. [ ] SPED Teacher
  2. [ ] General Education Teacher
  3. [ ] School Head
  4. [ ] ILRC Supervisor
  5. [ ] Parent/Guardian
  6. [ ] Medical/Allied Professional 1
  7. [ ] Medical/Allied Professional 2
  8. [ ] Medical/Allied Professional 3

#### Test Case 4.5.2: Sign as SPED Teacher
- [ ] Login as SPED Teacher
- [ ] Navigate to signature page
- [ ] Verify SPED Teacher signature slot is active (not grayed out)
- [ ] Verify canvas signature pad displays
- [ ] Draw signature using mouse
- [ ] Verify signature appears on canvas
- [ ] Click "Clear" button
- [ ] Verify canvas clears
- [ ] Draw signature again
- [ ] Click "Save Signature"
- [ ] Verify success message
- [ ] Check database: `pdsp_signatures` has row with signatory_role='sped_teacher'
- [ ] Verify signature image saved to `uploads/signatures/`
- [ ] Refresh page
- [ ] Verify signature slot now shows saved signature image (read-only)

#### Test Case 4.5.3: Sign as Guidance
- [ ] Login as Guidance
- [ ] Navigate to "IEP Meetings" → Find meeting with PDSP
- [ ] Click "Sign PDSP"
- [ ] Verify Guidance signature slot is active
- [ ] Draw and save signature
- [ ] Verify success message
- [ ] Check database: signature saved

#### Test Case 4.5.4: Sign as Principal
- [ ] Login as Principal
- [ ] Navigate to signature page
- [ ] Draw and save signature
- [ ] Verify success message

#### Test Case 4.5.5: Sign as Parent
- [ ] Login as Parent (linked to student)
- [ ] Navigate to "IEP Meetings" or dashboard
- [ ] Find notification: "PDSP ready for your signature"
- [ ] Click notification or "Sign PDSP" button
- [ ] Draw and save signature
- [ ] Verify success message

#### Test Case 4.5.6: Mobile Signature (Touch)
- [ ] Open signature page on mobile device or tablet
- [ ] Verify canvas is touch-responsive
- [ ] Draw signature using finger
- [ ] Verify signature appears smoothly
- [ ] Save signature
- [ ] Verify success

#### Test Case 4.5.7: Signature Order (Any Order)
- [ ] Verify signatures can be added in ANY order
- [ ] Sign as Principal BEFORE Guidance
- [ ] Verify no error
- [ ] Sign as Parent BEFORE SPED Teacher
- [ ] Verify no error
- [ ] Verify no sequence enforcement

#### Test Case 4.5.8: Auto-Completion Trigger
- [ ] Ensure 7 out of 8 signatures are saved
- [ ] Login as the 8th signatory
- [ ] Save final signature
- [ ] Verify success message includes: "PDSP Complete!"
- [ ] Check database: `pdsp_records` status changed to 'complete'
- [ ] Check database: `iep_meetings` status changed to 'completed'
- [ ] Verify notification sent to SPED Teacher: "PDSP for [Student] is complete"

#### Test Case 4.5.9: Cannot Re-Sign
- [ ] After signing, try to access signature page again
- [ ] Verify signature slot shows saved signature (read-only)
- [ ] Verify no canvas pad appears
- [ ] Verify message: "You have already signed this document"

### Feature 6: Document Passing & Access

#### Test Case 4.6.1: Document Passing
- [ ] Login as SPED Teacher
- [ ] After submitting PDSP form, click "Pass to Guidance"
- [ ] Verify confirmation prompt
- [ ] Confirm
- [ ] Verify success message
- [ ] Check database: notification created for Guidance user
- [ ] Login as Guidance
- [ ] Verify notification appears
- [ ] Click notification
- [ ] Verify redirects to PDSP signature page

#### Test Case 4.6.2: View Access (SPED Teacher)
- [ ] Login as SPED Teacher
- [ ] Navigate to completed PDSP
- [ ] Click "View PDSP"
- [ ] Verify full PDSP displays with all signatures
- [ ] Verify download button appears
- [ ] Click "Download"
- [ ] Verify PDF or image downloads

#### Test Case 4.6.3: View Access (Guidance)
- [ ] Login as Guidance
- [ ] Navigate to completed PDSP
- [ ] Verify can view full document
- [ ] Verify can download

#### Test Case 4.6.4: View Access (Principal)
- [ ] Login as Principal
- [ ] Verify can view and download

#### Test Case 4.6.5: View Access (Parent - Read Only)
- [ ] Login as Parent
- [ ] Navigate to completed PDSP
- [ ] Verify can view document in-system
- [ ] Verify download button is HIDDEN or disabled
- [ ] Verify read-only access

### Feature 7: Process 5 Unlock

#### Test Case 4.7.1: Process 5 Trigger
- [ ] Complete PDSP with all 8 signatures
- [ ] Verify `iep_meetings` status = 'completed'
- [ ] Login as SPED Teacher
- [ ] Navigate to dashboard or IEP section
- [ ] Verify "Generate IEP" button or link appears for this student
- [ ] Verify Process 5 is now accessible
- [ ] Click "Generate IEP"
- [ ] Verify redirects to IEP generation page (Process 5)

---

## UI/UX Testing

### Color Scheme Verification
- [ ] Primary buttons use #a01422 (crimson)
- [ ] Secondary buttons use #1e4072 (navy)
- [ ] Success messages use #4caf50 (green)
- [ ] Danger/error messages use #dc3545 (red)
- [ ] Warning messages use #ffc107 (amber)
- [ ] No default Bootstrap blue buttons
- [ ] No default gray cards

### Responsive Design
- [ ] Test on desktop (1920x1080)
- [ ] Test on laptop (1366x768)
- [ ] Test on tablet (768x1024)
- [ ] Test on mobile (375x667)
- [ ] Verify sidebar collapses on mobile
- [ ] Verify tables scroll horizontally on mobile
- [ ] Verify forms stack vertically on mobile
- [ ] Verify signature canvas resizes properly

### Accessibility
- [ ] All form fields have labels
- [ ] All buttons have descriptive text or aria-labels
- [ ] Color contrast meets WCAG AA standards
- [ ] Keyboard navigation works (Tab, Enter, Escape)
- [ ] Focus indicators visible
- [ ] Error messages are clear and specific

### Print Functionality
- [ ] Open completed PDSP document
- [ ] Click browser Print (Ctrl+P)
- [ ] Verify sidebar and topbar hidden in print preview
- [ ] Verify action buttons hidden
- [ ] Verify document fits on standard paper (8.5x11)
- [ ] Verify signatures display correctly
- [ ] Print to PDF
- [ ] Verify PDF is readable

---

## Error Handling & Edge Cases

### Validation Errors
- [ ] Try submitting assessment without selecting student
- [ ] Verify error: "Please select a student"
- [ ] Try submitting without checking any services
- [ ] Verify error: "Please select at least one service"
- [ ] Try submitting service without MDT members
- [ ] Verify error: "Please add MDT members for [service]"
- [ ] Try submitting without date of assessment
- [ ] Verify error: "Please enter assessment date"

### File Upload Errors
- [ ] Try uploading file > 10MB
- [ ] Verify error: "File size exceeds 10MB limit"
- [ ] Try uploading .exe file
- [ ] Verify error: "Invalid file type. Only jpg, png, pdf allowed"
- [ ] Try uploading with no file selected
- [ ] Verify error: "Please select a file"

### Permission Errors
- [ ] Login as Parent
- [ ] Try accessing `/assessment/conduct` directly via URL
- [ ] Verify 403 Forbidden error
- [ ] Try accessing `/iep/availability` as Parent
- [ ] Verify 403 error

### Database Errors
- [ ] Simulate database connection failure (stop MySQL)
- [ ] Try loading assessment page
- [ ] Verify friendly error message (not raw SQL error)
- [ ] Verify error logged to `logs/php_error.log`

### Session Timeout
- [ ] Login and start filling assessment
- [ ] Wait for session timeout (default 30 minutes)
- [ ] Try submitting form
- [ ] Verify redirect to login page
- [ ] Verify message: "Session expired. Please login again."

---

## Performance Testing

### Page Load Times
- [ ] Assessment conduct page loads in < 2 seconds
- [ ] Availability calendar loads in < 1 second
- [ ] PDSP form loads in < 2 seconds
- [ ] Signature page loads in < 1 second

### AJAX Response Times
- [ ] Student data loads in < 500ms
- [ ] MDT table updates in < 100ms (instant)
- [ ] File upload completes in < 5 seconds (for 5MB file)
- [ ] Signature save completes in < 1 second

### Database Query Performance
- [ ] Check slow query log for queries > 1 second
- [ ] Verify indexes exist on foreign keys
- [ ] Verify no N+1 query problems

---

## Security Testing

### SQL Injection
- [ ] Try entering `' OR '1'='1` in student search
- [ ] Verify no SQL error, no unauthorized data returned
- [ ] Try entering `<script>alert('XSS')</script>` in text fields
- [ ] Verify script does not execute, HTML escaped

### File Upload Security
- [ ] Try uploading PHP file disguised as JPG (rename .php to .jpg)
- [ ] Verify system rejects based on MIME type, not just extension
- [ ] Try uploading file with malicious filename: `../../etc/passwd.jpg`
- [ ] Verify filename sanitized, no directory traversal

### CSRF Protection
- [ ] Verify all forms have CSRF token
- [ ] Try submitting form without CSRF token
- [ ] Verify error: "Invalid CSRF token"
- [ ] Try submitting form with expired CSRF token
- [ ] Verify error

### Authorization
- [ ] Login as Guidance
- [ ] Try accessing SPED Teacher-only endpoint: `/assessment/conduct`
- [ ] Verify 403 Forbidden
- [ ] Try accessing another user's assessment via URL manipulation
- [ ] Verify 403 or 404

---

## Regression Testing

### Process 1 & 2 Still Work
- [ ] Submit new enrollment as Parent
- [ ] Verify enrollment saves correctly
- [ ] Login as SPED Teacher
- [ ] Verify enrollment and approve
- [ ] Verify student record created
- [ ] Verify no errors in Process 1 or 2

### Existing Features Unaffected
- [ ] Login/logout still works
- [ ] Dashboard loads correctly
- [ ] Notifications still work
- [ ] File downloads still work
- [ ] Email notifications still send

---

## Final Checklist

### Documentation
- [ ] CHANGELOG.md updated with v1.29 entry
- [ ] README.md updated if needed
- [ ] Code comments added to complex functions
- [ ] File headers present on all new files

### Code Quality
- [ ] No PHP errors in `logs/php_error.log`
- [ ] No JavaScript errors in browser console
- [ ] No SQL errors in database logs
- [ ] All functions have proper error handling
- [ ] All database queries use prepared statements

### Deployment Readiness
- [ ] All migrations applied successfully
- [ ] All upload directories exist and writable
- [ ] All routes registered in `routes/web.php`
- [ ] All permissions set in `config/permissions.php`
- [ ] .env file configured correctly
- [ ] Database backup created before deployment

---

## Test Results Summary

**Date Tested:** _______________  
**Tested By:** _______________  
**Browser:** _______________  
**Environment:** _______________

### Pass/Fail Summary
- [ ] Process 3 Features: _____ / _____ passed
- [ ] Process 4 Features: _____ / _____ passed
- [ ] UI/UX Tests: _____ / _____ passed
- [ ] Error Handling: _____ / _____ passed
- [ ] Security Tests: _____ / _____ passed
- [ ] Performance Tests: _____ / _____ passed

### Critical Issues Found
1. _______________________________________________
2. _______________________________________________
3. _______________________________________________

### Minor Issues Found
1. _______________________________________________
2. _______________________________________________
3. _______________________________________________

### Recommendations
1. _______________________________________________
2. _______________________________________________
3. _______________________________________________

---

**Status:** [ ] Ready for Production  |  [ ] Needs Fixes  |  [ ] Major Issues

**Approved By:** _______________  
**Date:** _______________
