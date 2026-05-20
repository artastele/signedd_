-- SPED LMS  Database Schema (Clean Version)
-- DO NOT ALTER WITHOUT APPROVAL
-- Last modified: 2026-05-13
-- All tables use CREATE TABLE IF NOT EXISTS (idempotent, safe to re-run)
-- All ALTER TABLE use INFORMATION_SCHEMA checks (MariaDB compatible)
--
-- Migration blocks: v39 .. v46 (see db_version). Latest: v46 (iep_steps.step_domain).
-- New machine: ensure app boots once (public/index.php runs SchemaManager) OR import this file;
-- MySQL < 8.0.12 / MariaDB without ADD COLUMN IF NOT EXISTS: use IEPModel::ensurePartOneSaveSchema at runtime
-- plus config/manual_migration_v41_v43.sql for gaps as needed.

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================
-- MIGRATION TRACKING
-- ============================================
CREATE TABLE IF NOT EXISTS db_version (
    version INT PRIMARY KEY,
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- SECURITY  Authentication & Authorization
-- ============================================

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    first_name VARCHAR(100),
    middle_name VARCHAR(100),
    last_name VARCHAR(100),
    suffix VARCHAR(20),
    email VARCHAR(255) UNIQUE NOT NULL,
    contact_number VARCHAR(20),
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user','parent','sped_teacher','guidance','principal','master_teacher','learner','admin') DEFAULT 'user',
    status ENUM('active','inactive','pending') DEFAULT 'active',
    email_verified BOOLEAN DEFAULT FALSE,
    email_verification_token VARCHAR(10),
    email_verification_expires DATETIME,
    verification_attempts INT DEFAULT 0,
    google_id VARCHAR(255) UNIQUE,
    profile_picture VARCHAR(255),
    auth_provider ENUM('local','google') DEFAULT 'local',
    deleted_at TIMESTAMP NULL,
    locked_until TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_deleted_at (deleted_at),
    INDEX idx_locked_until (locked_until)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS role_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    requested_role ENUM('sped_teacher','guidance','principal','master_teacher') NOT NULL,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    approver_role ENUM('admin','principal') DEFAULT 'principal',
    submitted_docs JSON,
    reviewed_by INT,
    review_note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_user_id (user_id),
    INDEX idx_approver_role (approver_role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS role_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_request_id INT NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_type VARCHAR(100),
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_request_id) REFERENCES role_requests(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PROCESS 1  Parent Complying Enrollment Requirements
-- ============================================

CREATE TABLE IF NOT EXISTS enrollment_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    parent_id INT NOT NULL,
    enrollment_type ENUM('new','transfer','returning') NOT NULL,
    school_year VARCHAR(20) NOT NULL,
    previous_enrollment_id INT NULL,
    is_draft BOOLEAN DEFAULT TRUE,
    status ENUM('draft','pending','verified','rejected') DEFAULT 'draft',
    lrn VARCHAR(12),
    last_name VARCHAR(100) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100),
    extension_name VARCHAR(20),
    birth_date DATE NOT NULL,
    sex ENUM('Male','Female') NOT NULL,
    age INT,
    birth_place VARCHAR(255),
    mother_tongue VARCHAR(100),
    is_indigenous_people BOOLEAN DEFAULT FALSE,
    indigenous_group VARCHAR(100),
    is_4ps_beneficiary BOOLEAN DEFAULT FALSE,
    fourps_household_id VARCHAR(50),
    disability_visual BOOLEAN DEFAULT FALSE,
    disability_hearing BOOLEAN DEFAULT FALSE,
    disability_learning BOOLEAN DEFAULT FALSE,
    disability_speech BOOLEAN DEFAULT FALSE,
    disability_intellectual BOOLEAN DEFAULT FALSE,
    disability_physical BOOLEAN DEFAULT FALSE,
    disability_emotional BOOLEAN DEFAULT FALSE,
    disability_chronic_illness BOOLEAN DEFAULT FALSE,
    disability_others BOOLEAN DEFAULT FALSE,
    disability_others_specify VARCHAR(255),
    current_house_no VARCHAR(255),
    current_barangay VARCHAR(100),
    current_city VARCHAR(100),
    current_province VARCHAR(100),
    current_zip_code VARCHAR(10),
    same_as_current_address BOOLEAN DEFAULT FALSE,
    permanent_house_no VARCHAR(255),
    permanent_barangay VARCHAR(100),
    permanent_city VARCHAR(100),
    permanent_province VARCHAR(100),
    permanent_zip_code VARCHAR(10),
    father_last_name VARCHAR(100),
    father_first_name VARCHAR(100),
    father_middle_name VARCHAR(100),
    father_contact_number VARCHAR(20),
    mother_maiden_last_name VARCHAR(100),
    mother_first_name VARCHAR(100),
    mother_middle_name VARCHAR(100),
    mother_contact_number VARCHAR(20),
    guardian_last_name VARCHAR(100),
    guardian_first_name VARCHAR(100),
    guardian_middle_name VARCHAR(100),
    guardian_contact_number VARCHAR(20),
    previous_school_id VARCHAR(50),
    previous_school_name VARCHAR(255),
    previous_school_address TEXT,
    previous_grade_level VARCHAR(50),
    previous_school_year VARCHAR(20),
    previous_school_type ENUM('Public','Private'),
    grade_level_to_enroll VARCHAR(50) NOT NULL,
    is_balik_aral BOOLEAN DEFAULT FALSE,
    is_pept_passer BOOLEAN DEFAULT FALSE,
    pept_rating VARCHAR(20),
    is_als_passer BOOLEAN DEFAULT FALSE,
    als_rating VARCHAR(20),
    shs_track VARCHAR(50),
    shs_strand VARCHAR(100),
    shs_semester ENUM('1st','2nd'),
    modality_modular_print BOOLEAN DEFAULT FALSE,
    modality_modular_digital BOOLEAN DEFAULT FALSE,
    modality_online BOOLEAN DEFAULT FALSE,
    modality_educational_tv BOOLEAN DEFAULT FALSE,
    modality_radio BOOLEAN DEFAULT FALSE,
    modality_blended BOOLEAN DEFAULT FALSE,
    modality_face_to_face BOOLEAN DEFAULT FALSE,
    preferred_distance_modality VARCHAR(50),
    signature_data TEXT,
    date_signed DATE,
    draft_saved_at TIMESTAMP NULL,
    submitted_at TIMESTAMP NULL,
    verified_by INT,
    verified_at TIMESTAMP NULL,
    review_note TEXT,
    learner_account_created BOOLEAN DEFAULT FALSE,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (previous_enrollment_id) REFERENCES enrollment_submissions(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_parent_id (parent_id),
    INDEX idx_enrollment_type (enrollment_type),
    INDEX idx_school_year (school_year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS enrollment_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    enrollment_id INT NOT NULL,
    document_type ENUM('psa_birth_cert','pwd_id','medical_record','beef_form') NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    reviewed_by INT,
    review_note TEXT,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reviewed_at TIMESTAMP NULL,
    FOREIGN KEY (enrollment_id) REFERENCES enrollment_submissions(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_enrollment_id (enrollment_id),
    INDEX idx_status (status),
    INDEX idx_document_type (document_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PROCESS 2  Verifying Enrollment Requirements
-- ============================================

CREATE TABLE IF NOT EXISTS student_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    enrollment_id INT NOT NULL,
    lrn VARCHAR(12) UNIQUE,
    student_name VARCHAR(255) NOT NULL,
    date_of_birth DATE,
    disability_type VARCHAR(255),
    psa_number VARCHAR(100),
    pwd_id_number VARCHAR(100),
    verified_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (enrollment_id) REFERENCES enrollment_submissions(id) ON DELETE CASCADE,
    FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_student_name (student_name),
    INDEX idx_lrn (lrn)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS education_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    previous_school VARCHAR(255),
    grade_level VARCHAR(50),
    year_attended VARCHAR(50),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PROCESS 3  Conducting Initial Assessment
-- ============================================

CREATE TABLE IF NOT EXISTS assessment_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    parent_id INT,
    assessed_by INT NOT NULL,
    conducted_by INT,
    assessment_type VARCHAR(100),
    assessment_data JSON,
    submitted_data JSON,
    education_history JSON,
    assessment_info JSON,
    section_a_data JSON,
    services_checked JSON,
    screening_types JSON,
    status ENUM('draft','finalized','pending','approved','rejected') DEFAULT 'draft',
    reviewed_by INT,
    review_note TEXT,
    quarter VARCHAR(2),
    submitted_at TIMESTAMP NULL,
    reviewed_at TIMESTAMP NULL,
    version INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (assessed_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (conducted_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_student_id (student_id),
    INDEX idx_version (version),
    INDEX idx_assessment_status (status),
    INDEX idx_assessment_quarter (quarter),
    INDEX idx_assessment_submitted (submitted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS assessment_services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assessment_id INT NOT NULL,
    service_name VARCHAR(255) NOT NULL,
    mdt_members JSON,
    date_of_assessment DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (assessment_id) REFERENCES assessment_records(id) ON DELETE CASCADE,
    INDEX idx_assessment_id (assessment_id),
    INDEX idx_service_name (service_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS assessment_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assessment_service_id INT NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_type VARCHAR(10) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (assessment_service_id) REFERENCES assessment_services(id) ON DELETE CASCADE,
    INDEX idx_service_id (assessment_service_id),
    INDEX idx_file_type (file_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS assessment_checklists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assessment_id INT NOT NULL,
    service_type VARCHAR(255) NOT NULL,
    checked BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (assessment_id) REFERENCES assessment_records(id) ON DELETE CASCADE,
    INDEX idx_assessment_id (assessment_id),
    INDEX idx_service_type (service_type),
    UNIQUE KEY unique_assessment_service (assessment_id, service_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PROCESS 4  Facilitating IEP Meeting
-- ============================================
-- NOTE: iep_meetings uses DATETIME (combined date+time) and meeting_location.
-- The duplicate definition that existed in migration v1.27 has been removed.
-- The v1.27 block used separate DATE + TIME columns and "venue"  that was
-- superseded by this definition. This is the single authoritative definition.

CREATE TABLE IF NOT EXISTS iep_meetings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    assessment_id INT NOT NULL,
    scheduled_by INT,
    meeting_date DATETIME NOT NULL,
    meeting_location VARCHAR(255),
    agenda TEXT,
    guidance_id INT,
    principal_id INT,
    status ENUM('scheduled','rescheduled','completed','cancelled') DEFAULT 'scheduled',
    notes TEXT,
    reschedule_reason TEXT,
    cancellation_reason TEXT,
    scheduled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    cancelled_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
    FOREIGN KEY (assessment_id) REFERENCES assessment_records(id) ON DELETE CASCADE,
    FOREIGN KEY (scheduled_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (guidance_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (principal_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_meeting_date (meeting_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS meeting_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    meeting_id INT NOT NULL,
    user_id INT NOT NULL,
    notified_via ENUM('email','system','both') DEFAULT 'both',
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (meeting_id) REFERENCES iep_meetings(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_meeting_user (meeting_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User availability (recurring weekly schedule + exception dates)
-- Replaces the old iep_meeting_calendars file-upload approach (removed)
CREATE TABLE IF NOT EXISTS user_availability (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('recurring','exception') NOT NULL,
    day_of_week TINYINT NULL COMMENT '0=Sunday ... 6=Saturday (recurring only)',
    specific_date DATE NULL COMMENT 'Exception dates only',
    is_available BOOLEAN NOT NULL DEFAULT TRUE,
    note VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_type (user_id, type),
    INDEX idx_specific_date (specific_date),
    UNIQUE KEY unique_recurring (user_id, type, day_of_week),
    UNIQUE KEY unique_exception (user_id, type, specific_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- PDSP records (Part II  filled during IEP meeting)
CREATE TABLE IF NOT EXISTS pdsp_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    meeting_id INT NOT NULL,
    student_id INT NOT NULL,
    filled_by INT NOT NULL,
    status ENUM('draft','signed') DEFAULT 'draft',
    signed_document_path VARCHAR(255) NULL,
    signatories TEXT NULL,
    uploaded_image_path VARCHAR(500) NULL,
    completed_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (meeting_id) REFERENCES iep_meetings(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
    FOREIGN KEY (filled_by) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_meeting (meeting_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pdsp_domains (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pdsp_id INT NOT NULL,
    domain_name VARCHAR(100) NOT NULL,
    sub_domain VARCHAR(200) NULL,
    skills_description TEXT NULL,
    mastered BOOLEAN DEFAULT FALSE,
    educational_recommendation TEXT NULL,
    q1_level ENUM('Beginning','Developing','Approaching Proficiency','Proficient','Advanced') NULL,
    q2_level ENUM('Beginning','Developing','Approaching Proficiency','Proficient','Advanced') NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pdsp_id) REFERENCES pdsp_records(id) ON DELETE CASCADE,
    INDEX idx_pdsp_domain (pdsp_id, domain_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Normalized signatory list per PDSP record
CREATE TABLE IF NOT EXISTS pdsp_signatories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pdsp_id INT NOT NULL,
    signatory_role ENUM('sped_teacher','gen_ed_teacher','school_head','ilrc_supervisor',
                        'parent_guardian','medical_allied_1','medical_allied_2','medical_allied_3') NOT NULL,
    signatory_name VARCHAR(200) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pdsp_id) REFERENCES pdsp_records(id) ON DELETE CASCADE,
    INDEX idx_pdsp_id (pdsp_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================
-- PROCESS 4 (continued) -- IEP P2 Documents and PDSP Signatures
-- Actively used by IEPP2DocumentModel, IEPDocumentController, FileController
-- ============================================

CREATE TABLE IF NOT EXISTS iep_p2_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    meeting_id INT NOT NULL,
    student_id INT NOT NULL,
    iep_data JSON NOT NULL,
    pdf_path VARCHAR(500),
    status ENUM('draft','pending_review','reviewed_signed') DEFAULT 'draft',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (meeting_id) REFERENCES iep_meetings(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_meeting_id (meeting_id),
    INDEX idx_student_id (student_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS iep_p2_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    iep_p2_id INT NOT NULL,
    reviewer_id INT NOT NULL,
    reviewer_role ENUM('guidance','principal','parent','sped_teacher','school_head','ilrc_supervisor') NOT NULL,
    feedback TEXT,
    signature_data TEXT,
    reviewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (iep_p2_id) REFERENCES iep_p2_documents(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_reviewer_per_p2 (iep_p2_id, reviewer_id),
    INDEX idx_reviewer_role (reviewer_role),
    INDEX idx_reviewed_at (reviewed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- PDSP digital signatures (canvas-based per signatory)
-- Used by IEPMeetingController::saveSignature()
CREATE TABLE IF NOT EXISTS pdsp_signatures (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pdsp_id INT NOT NULL,
    signatory_role ENUM('sped_teacher','gen_ed_teacher','school_head','ilrc_supervisor',
                        'parent_guardian','medical_allied_1','medical_allied_2','medical_allied_3') NOT NULL,
    signatory_name VARCHAR(200) NOT NULL,
    signature_image_path VARCHAR(500) NOT NULL,
    signed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pdsp_id) REFERENCES pdsp_records(id) ON DELETE CASCADE,
    UNIQUE KEY unique_pdsp_signatory (pdsp_id, signatory_role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PROCESS 5  Generating IEP (P3 Documents)
-- ============================================

CREATE TABLE IF NOT EXISTS iep_p3_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    meeting_id INT NOT NULL,
    student_id INT NOT NULL,
    iep_data JSON NOT NULL,
    pdf_path VARCHAR(500),
    status ENUM('draft','pending_signatures','signed_approved') DEFAULT 'draft',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (meeting_id) REFERENCES iep_meetings(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_meeting_id (meeting_id),
    INDEX idx_student_id (student_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS iep_p3_signatures (
    id INT AUTO_INCREMENT PRIMARY KEY,
    iep_p3_id INT NOT NULL,
    signer_id INT NOT NULL,
    signer_role ENUM('parent','guidance','teacher','sped_teacher','principal','school_head','ilrc_supervisor') NOT NULL,
    signature_data TEXT,
    remarks TEXT,
    signed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (iep_p3_id) REFERENCES iep_p3_documents(id) ON DELETE CASCADE,
    FOREIGN KEY (signer_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_signer_per_p3 (iep_p3_id, signer_role),
    INDEX idx_signer_role (signer_role),
    INDEX idx_signed_at (signed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS iep_audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    document_type ENUM('p2','p3') NOT NULL,
    document_id INT NOT NULL,
    user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_document (document_type, document_id),
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PROCESS 6  Implementing IEP
-- ============================================

CREATE TABLE IF NOT EXISTS learner_iep (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    iep_p3_id INT NOT NULL,
    teacher_id INT NOT NULL,
    implementation_status ENUM('not_started','in_progress','completed') DEFAULT 'not_started',
    start_date DATE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
    FOREIGN KEY (iep_p3_id) REFERENCES iep_p3_documents(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_student_id (student_id),
    INDEX idx_status (implementation_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS learning_materials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    learner_iep_id INT NOT NULL,
    material_name VARCHAR(255) NOT NULL,
    material_type VARCHAR(100),
    file_path VARCHAR(500),
    description TEXT,
    is_assignment BOOLEAN DEFAULT FALSE,
    due_date DATETIME NULL,
    points INT DEFAULT 0,
    uploaded_by INT NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (learner_iep_id) REFERENCES learner_iep(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PROCESS 7  Learner Engaging in Learning Activities
-- ============================================

CREATE TABLE IF NOT EXISTS activity_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    material_id INT NOT NULL,
    activity_type ENUM('multiple_choice','true_false','fill_blanks','matching',
                       'drag_drop_sort','image_label','sequencing','flashcards') NOT NULL,
    instructions TEXT,
    activity_data JSON NOT NULL,
    total_points INT DEFAULT 0,
    time_limit_minutes INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (material_id) REFERENCES learning_materials(id) ON DELETE CASCADE,
    INDEX idx_material_id (material_id),
    INDEX idx_activity_type (activity_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS activity_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    activity_id INT NOT NULL,
    student_id INT NOT NULL,
    attempt_number INT DEFAULT 1,
    answers JSON NOT NULL,
    score INT DEFAULT 0,
    total_points INT DEFAULT 0,
    percentage DECIMAL(5,2) DEFAULT 0,
    time_spent_minutes INT DEFAULT 0,
    completed BOOLEAN DEFAULT FALSE,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (activity_id) REFERENCES activity_templates(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
    INDEX idx_activity_id (activity_id),
    INDEX idx_student_id (student_id),
    INDEX idx_completed (completed)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS assignment_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    material_id INT NOT NULL,
    student_id INT NOT NULL,
    submission_type ENUM('file','text','both') NOT NULL,
    file_path VARCHAR(500),
    text_answer TEXT,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    graded BOOLEAN DEFAULT FALSE,
    grade INT,
    teacher_feedback TEXT,
    graded_at TIMESTAMP NULL,
    graded_by INT,
    FOREIGN KEY (material_id) REFERENCES learning_materials(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
    FOREIGN KEY (graded_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_material_id (material_id),
    INDEX idx_student_id (student_id),
    INDEX idx_graded (graded)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS learner_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    material_id INT NOT NULL,
    status ENUM('not_started','in_progress','completed') DEFAULT 'not_started',
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    time_spent_minutes INT DEFAULT 0,
    stars_earned INT DEFAULT 0,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
    FOREIGN KEY (material_id) REFERENCES learning_materials(id) ON DELETE CASCADE,
    UNIQUE KEY unique_student_material (student_id, material_id),
    INDEX idx_student_id (student_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS activity_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    learner_iep_id INT NOT NULL,
    activity_type VARCHAR(100),
    activity_data JSON,
    performance_notes TEXT,
    recorded_by INT NOT NULL,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
    FOREIGN KEY (learner_iep_id) REFERENCES learner_iep(id) ON DELETE CASCADE,
    FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_student_id (student_id),
    INDEX idx_recorded_at (recorded_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS module_access_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    module_name VARCHAR(255),
    accessed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    duration_minutes INT,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
    INDEX idx_student_id (student_id),
    INDEX idx_accessed_at (accessed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SECURITY  Logging, Notifications, CSRF, Rate Limiting, DLP
-- ============================================

CREATE TABLE IF NOT EXISTS login_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    email VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45),
    result ENUM('success','failure') NOT NULL,
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_email (email),
    INDEX idx_user_id (user_id),
    INDEX idx_attempted_at (attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action_type VARCHAR(100) NOT NULL,
    affected_table VARCHAR(100),
    affected_record_id INT,
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_action_type (action_type),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    data JSON,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_read (user_id, is_read),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS encryption_audit (
    id INT AUTO_INCREMENT PRIMARY KEY,
    table_name VARCHAR(100) NOT NULL,
    record_id INT NOT NULL,
    field_name VARCHAR(100) NOT NULL,
    action ENUM('encrypted','decrypted') NOT NULL,
    performed_by INT,
    performed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (performed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_table_record (table_name, record_id),
    INDEX idx_performed_at (performed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS csrf_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    user_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    used BOOLEAN DEFAULT FALSE,
    used_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_session_id (session_id),
    INDEX idx_token (token),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS rate_limit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255),
    ip_address VARCHAR(45),
    attempt_type ENUM('login','registration','password_reset') NOT NULL,
    success BOOLEAN DEFAULT FALSE,
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email_time (email, attempted_at),
    INDEX idx_ip_time (ip_address, attempted_at),
    INDEX idx_attempted_at (attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS dlp_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_setting_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT NOT NULL,
    category VARCHAR(50) NOT NULL,
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- DEFAULT SEED DATA
-- ============================================

-- Default admin account (password: password)
INSERT IGNORE INTO users (id, name, email, password_hash, role, status, email_verified, auth_provider)
VALUES (1, 'System Admin', 'admin@spedlms.local',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'admin', 'active', TRUE, 'local');

INSERT IGNORE INTO dlp_settings (setting_key, setting_value, description) VALUES
('dlp_enable_watermark',        'true',                    'Enable watermark on sensitive documents'),
('dlp_enable_screenshot_block', 'true',                    'Block screenshot attempts'),
('dlp_enable_copy_block',       'true',                    'Block copy/paste on sensitive pages'),
('dlp_enable_print_block',      'true',                    'Block printing of sensitive documents'),
('dlp_enable_export_block',     'true',                    'Block export functionality'),
('dlp_watermark_format',        '{user} | {timestamp} | {ip}', 'Watermark format string'),
('dlp_sensitive_pages',         'iep,assessment,student_records', 'Comma-separated sensitive page types');

INSERT IGNORE INTO system_settings (setting_key, setting_value, category, description) VALUES
('session_timeout',    '15', 'security', 'Session timeout in minutes'),
('max_login_attempts', '5',  'security', 'Maximum failed login attempts before lockout'),
('lockout_duration',   '15', 'security', 'Account lockout duration in minutes'),
('otp_expiration',     '10', 'security', 'OTP expiration time in minutes'),
('logout_warning',     '2',  'security', 'Show logout warning X minutes before timeout');

-- Mark all migrations as applied on fresh install
INSERT IGNORE INTO db_version (version) VALUES
(20),(21),(22),(23),(24),(25),(26),(27),(28),(29),
(30),(31),(32),(33),(34),(35),(36),(37),(38);

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- MIGRATION: v39 -- Process 5 IEP Tables
-- ============================================

CREATE TABLE IF NOT EXISTS iep_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    pdsp_id INT NOT NULL,
    drafted_by INT NOT NULL,
    school_year VARCHAR(20) NOT NULL,
    status ENUM('draft','signing','signed','locked') DEFAULT 'draft',
    signing_method ENUM('print_upload','digital') NULL,
    signed_document_path VARCHAR(500) NULL,
    re_evaluation_date DATE NULL,
    locked_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
    FOREIGN KEY (pdsp_id) REFERENCES pdsp_records(id) ON DELETE CASCADE,
    FOREIGN KEY (drafted_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_student_id (student_id),
    INDEX idx_status (status),
    INDEX idx_school_year (school_year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS iep_domains (
    id INT AUTO_INCREMENT PRIMARY KEY,
    iep_id INT NOT NULL,
    domain_name VARCHAR(200) NOT NULL,
    display_order INT DEFAULT 0,
    FOREIGN KEY (iep_id) REFERENCES iep_records(id) ON DELETE CASCADE,
    INDEX idx_iep_id (iep_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS iep_core (
    id INT AUTO_INCREMENT PRIMARY KEY,
    iep_id INT NOT NULL,
    developmental_domain TEXT NULL,
    priority_needs TEXT NULL,
    terminal_objectives TEXT NULL,
    FOREIGN KEY (iep_id) REFERENCES iep_records(id) ON DELETE CASCADE,
    UNIQUE KEY unique_iep_core (iep_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS iep_steps (
    id INT AUTO_INCREMENT PRIMARY KEY,
    iep_id INT NOT NULL,
    step_number INT NOT NULL,
    objectives TEXT NULL,
    observation TEXT NULL,
    activities TEXT NULL,
    materials TEXT NULL,
    evaluation TEXT NULL,
    duration_lp VARCHAR(100) NULL,
    FOREIGN KEY (iep_id) REFERENCES iep_records(id) ON DELETE CASCADE,
    INDEX idx_iep_id (iep_id),
    INDEX idx_step_number (step_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS iep_signatories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    iep_id INT NOT NULL,
    signatory_role ENUM('parent_guardian','guidance_counselor','teacher',
                        'sned_teacher','school_head','ilrc_supervisor') NOT NULL,
    signatory_name VARCHAR(200) NOT NULL,
    signature_image_path VARCHAR(500) NULL,
    signed_at TIMESTAMP NULL,
    FOREIGN KEY (iep_id) REFERENCES iep_records(id) ON DELETE CASCADE,
    INDEX idx_iep_id (iep_id),
    INDEX idx_role (signatory_role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS iep_copies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    iep_id INT NOT NULL,
    sent_to INT NOT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    viewed_at TIMESTAMP NULL,
    FOREIGN KEY (iep_id) REFERENCES iep_records(id) ON DELETE CASCADE,
    FOREIGN KEY (sent_to) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_iep_id (iep_id),
    INDEX idx_sent_to (sent_to)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO db_version (version) VALUES (39);

-- END MIGRATION: v39

-- ============================================
-- MIGRATION: v40 - Simplify Process 5 IEP Generation
-- Remove complex digital form, keep simple upload system
-- ============================================

-- Drop tables no longer needed
DROP TABLE IF EXISTS iep_domains;
DROP TABLE IF EXISTS iep_core; 
DROP TABLE IF EXISTS iep_steps;

-- Remove signing_method column from iep_records
ALTER TABLE iep_records DROP COLUMN IF EXISTS signing_method;

-- Ensure required columns exist (some may already exist)
ALTER TABLE iep_records 
ADD COLUMN IF NOT EXISTS signed_document_path VARCHAR(500) NULL AFTER status,
ADD COLUMN IF NOT EXISTS re_evaluation_date DATE NULL AFTER signed_document_path,
ADD COLUMN IF NOT EXISTS locked_at TIMESTAMP NULL AFTER re_evaluation_date;

-- Update iep_signatories enum to match your 6 roles
ALTER TABLE iep_signatories 
MODIFY COLUMN signatory_role ENUM('parent_guardian', 'guidance_counselor', 'teacher', 'sned_teacher', 'school_head', 'ilrc_supervisor') NOT NULL;

INSERT IGNORE INTO db_version (version) VALUES (40);

-- END MIGRATION: v40

-- ============================================
-- MIGRATION: v41 - Student Documents Centralized Storage
-- Mobile responsive preparation - centralized document tracking
-- ============================================

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
    is_hidden BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_student_process (student_id, process_name),
    INDEX idx_document_type (document_type),
    INDEX idx_uploaded_at (uploaded_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO db_version (version) VALUES (41);

-- END MIGRATION: v41

-- ============================================
-- MIGRATION: v42 - Process 6 & 7 LMS Tables
-- Lesson plans, materials, 8-type activities,
-- submissions, grading, learner access
-- ============================================

-- --------------------------------------------
-- Lesson Plans
-- Linked to iep_records (signed IEP from P5)
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS lesson_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    iep_id INT NOT NULL,
    student_id INT NULL,                          -- NULL when assignment_type = 'shared'
    created_by INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    pdsp_domain ENUM(
        'perceptuo_cognitive',
        'psychosocial',
        'socio_emotional',
        'psychomotor',
        'daily_living_skills',
        'communication_language'
    ) NOT NULL,
    assignment_type ENUM('individual','shared') DEFAULT 'individual',
    document_path VARCHAR(500) NULL,              -- uploaded lesson plan file
    status ENUM('draft','published') DEFAULT 'draft',
    published_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (iep_id) REFERENCES iep_records(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_iep_id (iep_id),
    INDEX idx_created_by (created_by),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------
-- Lesson Assignments
-- Maps lesson plans to individual learners
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS lesson_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lesson_plan_id INT NOT NULL,
    student_id INT NOT NULL,
    assigned_by INT NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lesson_plan_id) REFERENCES lesson_plans(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_assignment (lesson_plan_id, student_id),
    INDEX idx_lesson_plan_id (lesson_plan_id),
    INDEX idx_student_id (student_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------
-- Lesson Materials
-- 3 types: file upload, external link, embed
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS lesson_materials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lesson_plan_id INT NOT NULL,
    material_type ENUM('file','link','embed') NOT NULL,
    title VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NULL,                  -- for type = 'file'
    external_url VARCHAR(1000) NULL,              -- for type = 'link' or 'embed'
    embed_type ENUM('youtube','gdrive','other') NULL, -- for type = 'embed'
    display_order INT DEFAULT 0,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lesson_plan_id) REFERENCES lesson_plans(id) ON DELETE CASCADE,
    INDEX idx_lesson_plan_id (lesson_plan_id),
    INDEX idx_display_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------
-- LMS Activities
-- 8 types — all content stored in activity_data JSON
--
-- activity_type values and their activity_data shape:
--
-- multiple_choice:
--   { "options": [{"text":"...", "is_correct": true/false}], "points": N }
--
-- true_false:
--   { "correct_answer": "true"|"false", "points": N }
--
-- fill_in_blanks:
--   { "sentences": [{"text":"The ___ is red","answers":["apple"]}], "points": N }
--
-- matching:
--   { "pairs": [{"left":"...","right":"..."}], "points": N }
--
-- drag_drop_sort:
--   { "items": ["step1","step2","step3"], "correct_order": [0,1,2], "points": N }
--
-- image_label:
--   { "image_path":"...", "labels":[{"x":10,"y":20,"answer":"..."}], "points": N }
--
-- flashcards:
--   { "cards": [{"front":"...","back":"..."}] }
--   (no scoring — view only)
--
-- sequencing:
--   { "items": ["event1","event2","event3"], "correct_order": [2,0,1], "points": N }
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS lms_activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lesson_plan_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    instructions TEXT NULL,
    activity_type ENUM(
        'multiple_choice',
        'true_false',
        'fill_in_blanks',
        'matching',
        'drag_drop_sort',
        'image_label',
        'flashcards',
        'sequencing'
    ) NOT NULL,
    activity_data JSON NOT NULL,                  -- type-specific content (see above)
    max_score INT NULL,                           -- NULL for flashcards
    due_date DATETIME NULL,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lesson_plan_id) REFERENCES lesson_plans(id) ON DELETE CASCADE,
    INDEX idx_lesson_plan_id (lesson_plan_id),
    INDEX idx_activity_type (activity_type),
    INDEX idx_display_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------
-- Activity Submissions
-- Learner answers stored per activity
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS lms_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    activity_id INT NOT NULL,
    student_id INT NOT NULL,
    submitted_by INT NOT NULL,                    -- user_id (learner or parent)
    file_path VARCHAR(500) NULL,                  -- for file_submission type (future)
    answers JSON NULL,                            -- learner answers for scored types
    auto_score INT NULL,                          -- computed on submit for auto-scored types
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (activity_id) REFERENCES lms_activities(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
    FOREIGN KEY (submitted_by) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_submission (activity_id, student_id), -- one submission per learner per activity
    INDEX idx_activity_id (activity_id),
    INDEX idx_student_id (student_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------
-- Activity Grades
-- Teacher manual score override + remarks
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS lms_grades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    submission_id INT NOT NULL,
    graded_by INT NOT NULL,
    score INT NULL,
    max_score INT NULL,
    is_complete TINYINT(1) DEFAULT 0,
    remarks TEXT NULL,
    graded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (submission_id) REFERENCES lms_submissions(id) ON DELETE CASCADE,
    FOREIGN KEY (graded_by) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_grade (submission_id),
    INDEX idx_graded_at (graded_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------
-- LMS Activity Logs
-- Tracks opened / submitted / graded events
-- Separate from system activity_log
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS lms_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    activity_id INT NULL,
    material_id INT NULL,
    action ENUM('opened','submitted','graded') NOT NULL,
    performed_by INT NOT NULL,
    performed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
    FOREIGN KEY (performed_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_student_id (student_id),
    INDEX idx_action (action),
    INDEX idx_performed_at (performed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------
-- Learner Access Mode
-- direct = learner has own login
-- parent_managed = parent completes on behalf
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS learner_access (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    access_mode ENUM('direct','parent_managed') DEFAULT 'parent_managed',
    assigned_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_student_access (student_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO db_version (version) VALUES (42);

-- END MIGRATION: v42

-- ============================================
-- MIGRATION: v43 — Process 7 Gamification Tables
-- learner_points, learner_badges, activity_stars
-- No changes to any existing Process 1–6 tables.
-- ============================================

-- --------------------------------------------
-- Learner Points
-- XP ledger — one row per earning event.
-- source_type maps to XP rules in process-7.md:
--   view         → +5 XP (first open of view-only activity)
--   submission   → +10 XP (file submission sent)
--   quiz         → score% × max_score XP
--   lesson_bonus → +20 XP (lesson plan fully completed)
--   badge_bonus  → +15 XP (badge earned)
-- source_id is nullable: activity id, lesson id,
-- or badge row id depending on source_type.
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS learner_points (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    student_id  INT NOT NULL,
    points      INT NOT NULL DEFAULT 0,
    reason      VARCHAR(255) NOT NULL,
    source_type ENUM('view','submission','quiz','lesson_bonus','badge_bonus') NOT NULL,
    source_id   INT NULL,
    earned_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
    INDEX idx_student_id  (student_id),
    INDEX idx_source_type (source_type),
    INDEX idx_earned_at   (earned_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------
-- Learner Badges
-- One row per badge earned per learner.
-- badge_key ENUM matches the fixed set defined
-- in process-7.md — teacher cannot create custom badges.
-- UNIQUE KEY prevents duplicate badge awards.
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS learner_badges (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    badge_key  ENUM(
        'first_activity',
        'lesson_complete',
        'perfect_score',
        'five_in_a_row',
        'all_done',
        'star_collector'
    ) NOT NULL,
    earned_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
    UNIQUE KEY unique_student_badge (student_id, badge_key),
    INDEX idx_student_id (student_id),
    INDEX idx_badge_key  (badge_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------
-- Activity Stars
-- Auto-calculated when teacher grades a submission.
-- Star rule (from process-7.md):
--   90–100% → 3 stars
--   70–89%  → 2 stars
--   <70%    → 1 star
--   View-only activities: no stars row created.
-- submission_id → lms_submissions (P7 graded activities only).
-- UNIQUE KEY: one star record per submission.
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS activity_stars (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    submission_id INT NOT NULL,
    student_id    INT NOT NULL,
    stars         TINYINT NOT NULL,
    calculated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (submission_id) REFERENCES lms_submissions(id)  ON DELETE CASCADE,
    FOREIGN KEY (student_id)   REFERENCES student_records(id)   ON DELETE CASCADE,
    UNIQUE KEY unique_submission_stars (submission_id),
    INDEX idx_student_id (student_id),
    CONSTRAINT chk_stars CHECK (stars IN (1, 2, 3))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO db_version (version) VALUES (43);

-- END MIGRATION: v43

-- ============================================
-- MIGRATION: v44 — Process 5 IEP (living document) + P5–P6–P7 links
-- Restores iep_domains / iep_core / iep_steps (dropped in v40),
-- removes locked state, restores signing_method, junction + edit log tables.
-- ============================================

UPDATE iep_records SET status = 'signed' WHERE status = 'locked';

ALTER TABLE iep_records DROP COLUMN IF EXISTS locked_at;

ALTER TABLE iep_records
    MODIFY COLUMN status ENUM('draft','signing','signed') NOT NULL DEFAULT 'draft';

ALTER TABLE iep_records
    ADD COLUMN IF NOT EXISTS signing_method ENUM('print_upload','digital') NULL AFTER status;

CREATE TABLE IF NOT EXISTS iep_domains (
    id INT AUTO_INCREMENT PRIMARY KEY,
    iep_id INT NOT NULL,
    domain_name VARCHAR(200) NOT NULL,
    display_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (iep_id) REFERENCES iep_records(id) ON DELETE CASCADE,
    INDEX idx_iep_id (iep_id),
    INDEX idx_display_order (iep_id, display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS iep_core (
    id INT AUTO_INCREMENT PRIMARY KEY,
    iep_id INT NOT NULL,
    developmental_domain TEXT NULL,
    priority_needs TEXT NULL,
    terminal_objectives TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (iep_id) REFERENCES iep_records(id) ON DELETE CASCADE,
    UNIQUE KEY unique_iep_core (iep_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS iep_steps (
    id INT AUTO_INCREMENT PRIMARY KEY,
    iep_id INT NOT NULL,
    step_number INT NOT NULL,
    step_objective TEXT NULL,
    duration_lp VARCHAR(255) NULL,
    instructional_evaluation TEXT NULL,
    observation TEXT NULL,
    observation_unlocked TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (iep_id) REFERENCES iep_records(id) ON DELETE CASCADE,
    INDEX idx_iep_id (iep_id),
    INDEX idx_step_number (iep_id, step_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS iep_step_lesson_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    iep_step_id INT NOT NULL,
    lesson_plan_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (iep_step_id) REFERENCES iep_steps(id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_plan_id) REFERENCES lesson_plans(id) ON DELETE CASCADE,
    UNIQUE KEY unique_step_lesson (iep_step_id, lesson_plan_id),
    INDEX idx_lesson_plan_id (lesson_plan_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS iep_step_materials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    iep_step_id INT NOT NULL,
    material_id INT NOT NULL,
    linked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (iep_step_id) REFERENCES iep_steps(id) ON DELETE CASCADE,
    FOREIGN KEY (material_id) REFERENCES lesson_materials(id) ON DELETE CASCADE,
    UNIQUE KEY unique_step_material (iep_step_id, material_id),
    INDEX idx_material_id (material_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS iep_edit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    iep_id INT NOT NULL,
    edited_by INT NOT NULL,
    field_name VARCHAR(191) NOT NULL,
    old_value TEXT NULL,
    new_value TEXT NULL,
    edited_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (iep_id) REFERENCES iep_records(id) ON DELETE CASCADE,
    FOREIGN KEY (edited_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_iep_edited (iep_id, edited_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE iep_signatories
    ADD COLUMN IF NOT EXISTS send_status ENUM('not_sent','pending','signed') NOT NULL DEFAULT 'not_sent' AFTER signatory_name;

ALTER TABLE iep_signatories
    ADD COLUMN IF NOT EXISTS signature_request_sent_at TIMESTAMP NULL AFTER send_status;

UPDATE iep_signatories SET send_status = 'signed' WHERE signed_at IS NOT NULL;

INSERT IGNORE INTO db_version (version) VALUES (44);

-- END MIGRATION: v44

-- ============================================
-- MIGRATION: v45 — Process 5 IEP header overrides (Section 2 editable snapshot)
-- ============================================

ALTER TABLE iep_records
    ADD COLUMN IF NOT EXISTS header_learner_name VARCHAR(255) NULL AFTER re_evaluation_date,
    ADD COLUMN IF NOT EXISTS header_learner_age VARCHAR(50) NULL AFTER header_learner_name,
    ADD COLUMN IF NOT EXISTS header_lrn VARCHAR(32) NULL AFTER header_learner_age,
    ADD COLUMN IF NOT EXISTS header_section VARCHAR(120) NULL AFTER header_lrn,
    ADD COLUMN IF NOT EXISTS header_teacher_name VARCHAR(255) NULL AFTER header_section,
    ADD COLUMN IF NOT EXISTS header_school_name VARCHAR(255) NULL AFTER header_teacher_name,
    ADD COLUMN IF NOT EXISTS header_grade_level VARCHAR(100) NULL AFTER header_school_name;

INSERT IGNORE INTO db_version (version) VALUES (45);

-- END MIGRATION: v45

-- ============================================
-- MIGRATION: v46 — IEP step domain label (Section 5)
-- ============================================

ALTER TABLE iep_steps
    ADD COLUMN IF NOT EXISTS step_domain VARCHAR(191) NULL AFTER step_number;

INSERT IGNORE INTO db_version (version) VALUES (46);

-- END MIGRATION: v46
-- (End of versioned migrations in this file — keep db_version in sync when adding v47+.)
