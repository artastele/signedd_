-- SPED LMS Database Schema - PostgreSQL Version
-- Converted from MySQL schema.sql for Supabase PostgreSQL
-- Last modified: 2026-05-21
-- All tables use CREATE TABLE IF NOT EXISTS (idempotent, safe to re-run)

-- Create ENUM types for PostgreSQL
CREATE TYPE user_role AS ENUM ('user','parent','sped_teacher','guidance','principal','master_teacher','learner','admin');
CREATE TYPE user_status AS ENUM ('active','inactive','pending');
CREATE TYPE auth_provider_type AS ENUM ('local','google');
CREATE TYPE enrollment_type_enum AS ENUM ('new','transfer','returning');
CREATE TYPE enrollment_status AS ENUM ('draft','pending','verified','rejected');
CREATE TYPE sex_enum AS ENUM ('Male','Female');
CREATE TYPE modality_type AS ENUM ('modular_print','modular_digital','online','educational_tv','radio','blended','face_to_face');
CREATE TYPE requested_role AS ENUM ('sped_teacher','guidance','principal','master_teacher');
CREATE TYPE role_request_status AS ENUM ('pending','approved','rejected');
CREATE TYPE approver_role_type AS ENUM ('admin','principal');
CREATE TYPE document_type_enum AS ENUM ('psa_birth_cert','pwd_id','medical_record','beef_form');
CREATE TYPE document_status AS ENUM ('pending','approved','rejected');
CREATE TYPE assessment_status AS ENUM ('draft','finalized','pending','approved','rejected');
CREATE TYPE meeting_status AS ENUM ('scheduled','rescheduled','completed','cancelled');
CREATE TYPE pdsp_status AS ENUM ('draft','signed');
CREATE TYPE proficiency_level AS ENUM ('Beginning','Developing','Approaching Proficiency','Proficient','Advanced');
CREATE TYPE iep_p2_status AS ENUM ('draft','pending_review','reviewed_signed');
CREATE TYPE iep_p3_status AS ENUM ('draft','pending_signatures','signed_approved');
CREATE TYPE document_enum AS ENUM ('p2','p3');
CREATE TYPE implementation_status AS ENUM ('not_started','in_progress','completed');
CREATE TYPE activity_type_enum AS ENUM ('multiple_choice','true_false','fill_blanks','matching','drag_drop_sort','image_label','sequencing','flashcards');
CREATE TYPE submission_type AS ENUM ('file','text','both');
CREATE TYPE progress_status AS ENUM ('not_started','in_progress','completed');
CREATE TYPE login_result AS ENUM ('success','failure');
CREATE TYPE attempt_type AS ENUM ('login','registration','password_reset');
CREATE TYPE action_enum AS ENUM ('encrypted','decrypted');
CREATE TYPE iep_status AS ENUM ('draft','signing','signed','locked');
CREATE TYPE signatory_role_enum AS ENUM ('sped_teacher','gen_ed_teacher','school_head','ilrc_supervisor','parent_guardian','medical_allied_1','medical_allied_2','medical_allied_3');
CREATE TYPE iep_signatory_role AS ENUM ('parent_guardian', 'guidance_counselor', 'teacher', 'sned_teacher', 'school_head', 'ilrc_supervisor');
CREATE TYPE reviewer_role AS ENUM ('guidance','principal','parent','sped_teacher','school_head','ilrc_supervisor');
CREATE TYPE iep_p3_signer_role AS ENUM ('parent','guidance','teacher','sped_teacher','principal','school_head','ilrc_supervisor');
CREATE TYPE availability_type AS ENUM ('recurring','exception');
CREATE TYPE notification_via_type AS ENUM ('email','system','both');
CREATE TYPE school_type AS ENUM ('Public','Private');

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
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    first_name VARCHAR(100),
    middle_name VARCHAR(100),
    last_name VARCHAR(100),
    suffix VARCHAR(20),
    email VARCHAR(255) UNIQUE NOT NULL,
    contact_number VARCHAR(20),
    password_hash VARCHAR(255) NOT NULL,
    role user_role DEFAULT 'user',
    status user_status DEFAULT 'active',
    email_verified BOOLEAN DEFAULT FALSE,
    email_verification_token VARCHAR(10),
    email_verification_expires TIMESTAMP,
    verification_attempts INT DEFAULT 0,
    google_id VARCHAR(255) UNIQUE,
    profile_picture VARCHAR(255),
    auth_provider auth_provider_type DEFAULT 'local',
    deleted_at TIMESTAMP NULL,
    locked_until TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_email ON users(email);
CREATE INDEX IF NOT EXISTS idx_role ON users(role);
CREATE INDEX IF NOT EXISTS idx_deleted_at ON users(deleted_at);
CREATE INDEX IF NOT EXISTS idx_locked_until ON users(locked_until);

CREATE TABLE IF NOT EXISTS role_requests (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL,
    requested_role requested_role NOT NULL,
    status role_request_status DEFAULT 'pending',
    approver_role approver_role_type DEFAULT 'principal',
    submitted_docs JSONB,
    reviewed_by INT,
    review_note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX IF NOT EXISTS idx_role_request_status ON role_requests(status);
CREATE INDEX IF NOT EXISTS idx_role_request_user_id ON role_requests(user_id);
CREATE INDEX IF NOT EXISTS idx_approver_role ON role_requests(approver_role);

CREATE TABLE IF NOT EXISTS role_documents (
    id SERIAL PRIMARY KEY,
    role_request_id INT NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_type VARCHAR(100),
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_request_id) REFERENCES role_requests(id) ON DELETE CASCADE
);

-- ============================================
-- PROCESS 1  Parent Complying Enrollment Requirements
-- ============================================

CREATE TABLE IF NOT EXISTS enrollment_submissions (
    id SERIAL PRIMARY KEY,
    parent_id INT NOT NULL,
    enrollment_type enrollment_type_enum NOT NULL,
    school_year VARCHAR(20) NOT NULL,
    previous_enrollment_id INT NULL,
    is_draft BOOLEAN DEFAULT TRUE,
    status enrollment_status DEFAULT 'draft',
    lrn VARCHAR(12),
    last_name VARCHAR(100) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100),
    extension_name VARCHAR(20),
    birth_date DATE NOT NULL,
    sex sex_enum NOT NULL,
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
    previous_school_type school_type,
    grade_level_to_enroll VARCHAR(50) NOT NULL,
    is_balik_aral BOOLEAN DEFAULT FALSE,
    is_pept_passer BOOLEAN DEFAULT FALSE,
    pept_rating VARCHAR(20),
    is_als_passer BOOLEAN DEFAULT FALSE,
    als_rating VARCHAR(20),
    shs_track VARCHAR(50),
    shs_strand VARCHAR(100),
    shs_semester VARCHAR(10),
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
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (previous_enrollment_id) REFERENCES enrollment_submissions(id) ON DELETE SET NULL
);

CREATE INDEX IF NOT EXISTS idx_enrollment_status ON enrollment_submissions(status);
CREATE INDEX IF NOT EXISTS idx_enrollment_parent_id ON enrollment_submissions(parent_id);
CREATE INDEX IF NOT EXISTS idx_enrollment_type ON enrollment_submissions(enrollment_type);
CREATE INDEX IF NOT EXISTS idx_enrollment_school_year ON enrollment_submissions(school_year);

CREATE TABLE IF NOT EXISTS enrollment_documents (
    id SERIAL PRIMARY KEY,
    enrollment_id INT NOT NULL,
    document_type document_type_enum NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    status document_status DEFAULT 'pending',
    reviewed_by INT,
    review_note TEXT,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reviewed_at TIMESTAMP NULL,
    FOREIGN KEY (enrollment_id) REFERENCES enrollment_submissions(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX IF NOT EXISTS idx_enrollment_doc_id ON enrollment_documents(enrollment_id);
CREATE INDEX IF NOT EXISTS idx_enrollment_doc_status ON enrollment_documents(status);
CREATE INDEX IF NOT EXISTS idx_enrollment_doc_type ON enrollment_documents(document_type);

-- ============================================
-- PROCESS 2  Verifying Enrollment Requirements
-- ============================================

CREATE TABLE IF NOT EXISTS student_records (
    id SERIAL PRIMARY KEY,
    enrollment_id INT NOT NULL,
    lrn VARCHAR(12) UNIQUE,
    student_name VARCHAR(255) NOT NULL,
    date_of_birth DATE,
    disability_type VARCHAR(255),
    psa_number VARCHAR(100),
    pwd_id_number VARCHAR(100),
    verified_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (enrollment_id) REFERENCES enrollment_submissions(id) ON DELETE CASCADE,
    FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_student_name ON student_records(student_name);
CREATE INDEX IF NOT EXISTS idx_lrn ON student_records(lrn);

CREATE TABLE IF NOT EXISTS education_history (
    id SERIAL PRIMARY KEY,
    student_id INT NOT NULL,
    previous_school VARCHAR(255),
    grade_level VARCHAR(50),
    year_attended VARCHAR(50),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE
);

-- ============================================
-- PROCESS 3  Conducting Initial Assessment
-- ============================================

CREATE TABLE IF NOT EXISTS assessment_records (
    id SERIAL PRIMARY KEY,
    student_id INT NOT NULL,
    parent_id INT,
    assessed_by INT NOT NULL,
    conducted_by INT,
    assessment_type VARCHAR(100),
    assessment_data JSONB,
    submitted_data JSONB,
    education_history JSONB,
    assessment_info JSONB,
    section_a_data JSONB,
    services_checked JSONB,
    screening_types JSONB,
    status assessment_status DEFAULT 'draft',
    reviewed_by INT,
    review_note TEXT,
    quarter VARCHAR(2),
    submitted_at TIMESTAMP NULL,
    reviewed_at TIMESTAMP NULL,
    version INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (assessed_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (conducted_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX IF NOT EXISTS idx_assessment_student_id ON assessment_records(student_id);
CREATE INDEX IF NOT EXISTS idx_assessment_version ON assessment_records(version);
CREATE INDEX IF NOT EXISTS idx_assessment_status ON assessment_records(status);
CREATE INDEX IF NOT EXISTS idx_assessment_quarter ON assessment_records(quarter);
CREATE INDEX IF NOT EXISTS idx_assessment_submitted ON assessment_records(submitted_at);

CREATE TABLE IF NOT EXISTS assessment_services (
    id SERIAL PRIMARY KEY,
    assessment_id INT NOT NULL,
    service_name VARCHAR(255) NOT NULL,
    mdt_members JSONB,
    date_of_assessment DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (assessment_id) REFERENCES assessment_records(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_assessment_service_id ON assessment_services(assessment_id);
CREATE INDEX IF NOT EXISTS idx_assessment_service_name ON assessment_services(service_name);

CREATE TABLE IF NOT EXISTS assessment_documents (
    id SERIAL PRIMARY KEY,
    assessment_service_id INT NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_type VARCHAR(10) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (assessment_service_id) REFERENCES assessment_services(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_assessment_doc_service_id ON assessment_documents(assessment_service_id);
CREATE INDEX IF NOT EXISTS idx_assessment_doc_type ON assessment_documents(file_type);

CREATE TABLE IF NOT EXISTS assessment_checklists (
    id SERIAL PRIMARY KEY,
    assessment_id INT NOT NULL,
    service_type VARCHAR(255) NOT NULL,
    checked BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (assessment_id) REFERENCES assessment_records(id) ON DELETE CASCADE,
    UNIQUE (assessment_id, service_type)
);

CREATE INDEX IF NOT EXISTS idx_assessment_checklist_id ON assessment_checklists(assessment_id);
CREATE INDEX IF NOT EXISTS idx_assessment_checklist_service ON assessment_checklists(service_type);

-- ============================================
-- PROCESS 4  Facilitating IEP Meeting
-- ============================================

CREATE TABLE IF NOT EXISTS iep_meetings (
    id SERIAL PRIMARY KEY,
    student_id INT NOT NULL,
    assessment_id INT NOT NULL,
    scheduled_by INT,
    meeting_date TIMESTAMP NOT NULL,
    meeting_location VARCHAR(255),
    agenda TEXT,
    guidance_id INT,
    principal_id INT,
    status meeting_status DEFAULT 'scheduled',
    notes TEXT,
    reschedule_reason TEXT,
    cancellation_reason TEXT,
    scheduled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    cancelled_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
    FOREIGN KEY (assessment_id) REFERENCES assessment_records(id) ON DELETE CASCADE,
    FOREIGN KEY (scheduled_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (guidance_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (principal_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX IF NOT EXISTS idx_iep_meeting_status ON iep_meetings(status);
CREATE INDEX IF NOT EXISTS idx_iep_meeting_date ON iep_meetings(meeting_date);

CREATE TABLE IF NOT EXISTS meeting_notifications (
    id SERIAL PRIMARY KEY,
    meeting_id INT NOT NULL,
    user_id INT NOT NULL,
    notified_via notification_via_type DEFAULT 'both',
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (meeting_id) REFERENCES iep_meetings(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_meeting_notification ON meeting_notifications(meeting_id, user_id);

CREATE TABLE IF NOT EXISTS user_availability (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL,
    type availability_type NOT NULL,
    day_of_week SMALLINT NULL,
    specific_date DATE NULL,
    is_available BOOLEAN NOT NULL DEFAULT TRUE,
    note VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_user_availability_type ON user_availability(user_id, type);
CREATE INDEX IF NOT EXISTS idx_user_availability_date ON user_availability(specific_date);
CREATE UNIQUE INDEX IF NOT EXISTS idx_user_availability_recurring ON user_availability(user_id, type, day_of_week) WHERE type = 'recurring';
CREATE UNIQUE INDEX IF NOT EXISTS idx_user_availability_exception ON user_availability(user_id, type, specific_date) WHERE type = 'exception';

CREATE TABLE IF NOT EXISTS pdsp_records (
    id SERIAL PRIMARY KEY,
    meeting_id INT NOT NULL,
    student_id INT NOT NULL,
    filled_by INT NOT NULL,
    status pdsp_status DEFAULT 'draft',
    signed_document_path VARCHAR(255) NULL,
    signatories TEXT NULL,
    uploaded_image_path VARCHAR(500) NULL,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (meeting_id) REFERENCES iep_meetings(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
    FOREIGN KEY (filled_by) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE (meeting_id)
);

CREATE TABLE IF NOT EXISTS pdsp_domains (
    id SERIAL PRIMARY KEY,
    pdsp_id INT NOT NULL,
    domain_name VARCHAR(100) NOT NULL,
    sub_domain VARCHAR(200) NULL,
    skills_description TEXT NULL,
    mastered BOOLEAN DEFAULT FALSE,
    educational_recommendation TEXT NULL,
    q1_level proficiency_level NULL,
    q2_level proficiency_level NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pdsp_id) REFERENCES pdsp_records(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_pdsp_domain ON pdsp_domains(pdsp_id, domain_name);

CREATE TABLE IF NOT EXISTS pdsp_signatories (
    id SERIAL PRIMARY KEY,
    pdsp_id INT NOT NULL,
    signatory_role signatory_role_enum NOT NULL,
    signatory_name VARCHAR(200) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pdsp_id) REFERENCES pdsp_records(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_pdsp_signatory_id ON pdsp_signatories(pdsp_id);

CREATE TABLE IF NOT EXISTS iep_p2_documents (
    id SERIAL PRIMARY KEY,
    meeting_id INT NOT NULL,
    student_id INT NOT NULL,
    iep_data JSONB NOT NULL,
    pdf_path VARCHAR(500),
    status iep_p2_status DEFAULT 'draft',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (meeting_id) REFERENCES iep_meetings(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_iep_p2_meeting_id ON iep_p2_documents(meeting_id);
CREATE INDEX IF NOT EXISTS idx_iep_p2_student_id ON iep_p2_documents(student_id);
CREATE INDEX IF NOT EXISTS idx_iep_p2_status ON iep_p2_documents(status);

CREATE TABLE IF NOT EXISTS iep_p2_reviews (
    id SERIAL PRIMARY KEY,
    iep_p2_id INT NOT NULL,
    reviewer_id INT NOT NULL,
    reviewer_role reviewer_role NOT NULL,
    feedback TEXT,
    signature_data TEXT,
    reviewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (iep_p2_id) REFERENCES iep_p2_documents(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE (iep_p2_id, reviewer_id)
);

CREATE INDEX IF NOT EXISTS idx_iep_p2_review_role ON iep_p2_reviews(reviewer_role);
CREATE INDEX IF NOT EXISTS idx_iep_p2_review_date ON iep_p2_reviews(reviewed_at);

CREATE TABLE IF NOT EXISTS pdsp_signatures (
    id SERIAL PRIMARY KEY,
    pdsp_id INT NOT NULL,
    signatory_role signatory_role_enum NOT NULL,
    signatory_name VARCHAR(200) NOT NULL,
    signature_image_path VARCHAR(500) NOT NULL,
    signed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pdsp_id) REFERENCES pdsp_records(id) ON DELETE CASCADE,
    UNIQUE (pdsp_id, signatory_role)
);

-- ============================================
-- PROCESS 5  Generating IEP (P3 Documents)
-- ============================================

CREATE TABLE IF NOT EXISTS iep_p3_documents (
    id SERIAL PRIMARY KEY,
    meeting_id INT NOT NULL,
    student_id INT NOT NULL,
    iep_data JSONB NOT NULL,
    pdf_path VARCHAR(500),
    status iep_p3_status DEFAULT 'draft',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (meeting_id) REFERENCES iep_meetings(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_iep_p3_meeting_id ON iep_p3_documents(meeting_id);
CREATE INDEX IF NOT EXISTS idx_iep_p3_student_id ON iep_p3_documents(student_id);
CREATE INDEX IF NOT EXISTS idx_iep_p3_status ON iep_p3_documents(status);

CREATE TABLE IF NOT EXISTS iep_p3_signatures (
    id SERIAL PRIMARY KEY,
    iep_p3_id INT NOT NULL,
    signer_id INT NOT NULL,
    signer_role iep_p3_signer_role NOT NULL,
    signature_data TEXT,
    remarks TEXT,
    signed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (iep_p3_id) REFERENCES iep_p3_documents(id) ON DELETE CASCADE,
    FOREIGN KEY (signer_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE (iep_p3_id, signer_role)
);

CREATE INDEX IF NOT EXISTS idx_iep_p3_signer_role ON iep_p3_signatures(signer_role);
CREATE INDEX IF NOT EXISTS idx_iep_p3_signed_at ON iep_p3_signatures(signed_at);

CREATE TABLE IF NOT EXISTS iep_audit_log (
    id SERIAL PRIMARY KEY,
    document_type document_enum NOT NULL,
    document_id INT NOT NULL,
    user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_iep_audit_document ON iep_audit_log(document_type, document_id);
CREATE INDEX IF NOT EXISTS idx_iep_audit_user_id ON iep_audit_log(user_id);
CREATE INDEX IF NOT EXISTS idx_iep_audit_action ON iep_audit_log(action);
CREATE INDEX IF NOT EXISTS idx_iep_audit_created ON iep_audit_log(created_at);

-- ============================================
-- PROCESS 6  Implementing IEP
-- ============================================

CREATE TABLE IF NOT EXISTS learner_iep (
    id SERIAL PRIMARY KEY,
    student_id INT NOT NULL,
    iep_p3_id INT NOT NULL,
    teacher_id INT NOT NULL,
    implementation_status implementation_status DEFAULT 'not_started',
    start_date DATE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
    FOREIGN KEY (iep_p3_id) REFERENCES iep_p3_documents(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_learner_iep_student_id ON learner_iep(student_id);
CREATE INDEX IF NOT EXISTS idx_learner_iep_status ON learner_iep(implementation_status);

CREATE TABLE IF NOT EXISTS learning_materials (
    id SERIAL PRIMARY KEY,
    learner_iep_id INT NOT NULL,
    material_name VARCHAR(255) NOT NULL,
    material_type VARCHAR(100),
    file_path VARCHAR(500),
    description TEXT,
    is_assignment BOOLEAN DEFAULT FALSE,
    due_date TIMESTAMP NULL,
    points INT DEFAULT 0,
    uploaded_by INT NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (learner_iep_id) REFERENCES learner_iep(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================
-- PROCESS 7  Learner Engaging in Learning Activities
-- ============================================

CREATE TABLE IF NOT EXISTS activity_templates (
    id SERIAL PRIMARY KEY,
    material_id INT NOT NULL,
    activity_type activity_type_enum NOT NULL,
    instructions TEXT,
    activity_data JSONB NOT NULL,
    total_points INT DEFAULT 0,
    time_limit_minutes INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (material_id) REFERENCES learning_materials(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_activity_template_material ON activity_templates(material_id);
CREATE INDEX IF NOT EXISTS idx_activity_template_type ON activity_templates(activity_type);

CREATE TABLE IF NOT EXISTS activity_attempts (
    id SERIAL PRIMARY KEY,
    activity_id INT NOT NULL,
    student_id INT NOT NULL,
    attempt_number INT DEFAULT 1,
    answers JSONB NOT NULL,
    score INT DEFAULT 0,
    total_points INT DEFAULT 0,
    percentage DECIMAL(5,2) DEFAULT 0,
    time_spent_minutes INT DEFAULT 0,
    completed BOOLEAN DEFAULT FALSE,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (activity_id) REFERENCES activity_templates(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_activity_attempt_id ON activity_attempts(activity_id);
CREATE INDEX IF NOT EXISTS idx_activity_attempt_student ON activity_attempts(student_id);
CREATE INDEX IF NOT EXISTS idx_activity_attempt_completed ON activity_attempts(completed);

CREATE TABLE IF NOT EXISTS assignment_submissions (
    id SERIAL PRIMARY KEY,
    material_id INT NOT NULL,
    student_id INT NOT NULL,
    submission_type submission_type NOT NULL,
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
    FOREIGN KEY (graded_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX IF NOT EXISTS idx_assignment_material ON assignment_submissions(material_id);
CREATE INDEX IF NOT EXISTS idx_assignment_student ON assignment_submissions(student_id);
CREATE INDEX IF NOT EXISTS idx_assignment_graded ON assignment_submissions(graded);

CREATE TABLE IF NOT EXISTS learner_progress (
    id SERIAL PRIMARY KEY,
    student_id INT NOT NULL,
    material_id INT NOT NULL,
    status progress_status DEFAULT 'not_started',
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    time_spent_minutes INT DEFAULT 0,
    stars_earned INT DEFAULT 0,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
    FOREIGN KEY (material_id) REFERENCES learning_materials(id) ON DELETE CASCADE,
    UNIQUE (student_id, material_id)
);

CREATE INDEX IF NOT EXISTS idx_learner_progress_student ON learner_progress(student_id);
CREATE INDEX IF NOT EXISTS idx_learner_progress_status ON learner_progress(status);

CREATE TABLE IF NOT EXISTS activity_records (
    id SERIAL PRIMARY KEY,
    student_id INT NOT NULL,
    learner_iep_id INT NOT NULL,
    activity_type VARCHAR(100),
    activity_data JSONB,
    performance_notes TEXT,
    recorded_by INT NOT NULL,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
    FOREIGN KEY (learner_iep_id) REFERENCES learner_iep(id) ON DELETE CASCADE,
    FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_activity_record_student ON activity_records(student_id);
CREATE INDEX IF NOT EXISTS idx_activity_record_time ON activity_records(recorded_at);

CREATE TABLE IF NOT EXISTS module_access_logs (
    id SERIAL PRIMARY KEY,
    student_id INT NOT NULL,
    module_name VARCHAR(255),
    accessed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    duration_minutes INT,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_module_access_student ON module_access_logs(student_id);
CREATE INDEX IF NOT EXISTS idx_module_access_time ON module_access_logs(accessed_at);

-- ============================================
-- SECURITY  Logging, Notifications, CSRF, Rate Limiting, DLP
-- ============================================

CREATE TABLE IF NOT EXISTS login_log (
    id SERIAL PRIMARY KEY,
    user_id INT NULL,
    email VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45),
    result login_result NOT NULL,
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX IF NOT EXISTS idx_login_email ON login_log(email);
CREATE INDEX IF NOT EXISTS idx_login_user_id ON login_log(user_id);
CREATE INDEX IF NOT EXISTS idx_login_attempted_at ON login_log(attempted_at);

CREATE TABLE IF NOT EXISTS activity_log (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL,
    action_type VARCHAR(100) NOT NULL,
    affected_table VARCHAR(100),
    affected_record_id INT,
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_activity_log_user ON activity_log(user_id);
CREATE INDEX IF NOT EXISTS idx_activity_log_action ON activity_log(action_type);
CREATE INDEX IF NOT EXISTS idx_activity_log_created ON activity_log(created_at);

CREATE TABLE IF NOT EXISTS notifications (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    data JSONB,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_notification_user_read ON notifications(user_id, is_read);
CREATE INDEX IF NOT EXISTS idx_notification_created ON notifications(created_at);

CREATE TABLE IF NOT EXISTS encryption_audit (
    id SERIAL PRIMARY KEY,
    table_name VARCHAR(100) NOT NULL,
    record_id INT NOT NULL,
    field_name VARCHAR(100) NOT NULL,
    action action_enum NOT NULL,
    performed_by INT,
    performed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (performed_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX IF NOT EXISTS idx_encryption_audit_table ON encryption_audit(table_name, record_id);
CREATE INDEX IF NOT EXISTS idx_encryption_audit_performed ON encryption_audit(performed_at);

CREATE TABLE IF NOT EXISTS csrf_tokens (
    id SERIAL PRIMARY KEY,
    session_id VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    user_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    used BOOLEAN DEFAULT FALSE,
    used_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_csrf_session ON csrf_tokens(session_id);
CREATE INDEX IF NOT EXISTS idx_csrf_token ON csrf_tokens(token);
CREATE INDEX IF NOT EXISTS idx_csrf_expires ON csrf_tokens(expires_at);

CREATE TABLE IF NOT EXISTS rate_limit_log (
    id SERIAL PRIMARY KEY,
    email VARCHAR(255),
    ip_address VARCHAR(45),
    attempt_type attempt_type NOT NULL,
    success BOOLEAN DEFAULT FALSE,
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_rate_limit_email ON rate_limit_log(email, attempted_at);
CREATE INDEX IF NOT EXISTS idx_rate_limit_ip ON rate_limit_log(ip_address, attempted_at);
CREATE INDEX IF NOT EXISTS idx_rate_limit_time ON rate_limit_log(attempted_at);

CREATE TABLE IF NOT EXISTS dlp_settings (
    id SERIAL PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_dlp_setting_key ON dlp_settings(setting_key);

CREATE TABLE IF NOT EXISTS system_settings (
    id SERIAL PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT NOT NULL,
    category VARCHAR(50) NOT NULL,
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_system_setting_category ON system_settings(category);
CREATE INDEX IF NOT EXISTS idx_system_setting_key ON system_settings(setting_key);

-- ============================================
-- DEFAULT SEED DATA
-- ============================================

INSERT INTO users (id, name, email, password_hash, role, status, email_verified, auth_provider)
VALUES (1, 'System Admin', 'admin@spedlms.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active', TRUE, 'local')
ON CONFLICT (email) DO NOTHING;

INSERT INTO dlp_settings (setting_key, setting_value, description) VALUES
('dlp_enable_watermark',        'true',                    'Enable watermark on sensitive documents'),
('dlp_enable_screenshot_block', 'true',                    'Block screenshot attempts'),
('dlp_enable_copy_block',       'true',                    'Block copy/paste on sensitive pages'),
('dlp_enable_print_block',      'true',                    'Block printing of sensitive documents'),
('dlp_enable_export_block',     'true',                    'Block export functionality'),
('dlp_watermark_format',        '{user} | {timestamp} | {ip}', 'Watermark format string'),
('dlp_sensitive_pages',         'iep,assessment,student_records', 'Comma-separated sensitive page types')
ON CONFLICT (setting_key) DO NOTHING;

INSERT INTO system_settings (setting_key, setting_value, category, description) VALUES
('session_timeout',    '15', 'security', 'Session timeout in minutes'),
('max_login_attempts', '5',  'security', 'Maximum failed login attempts before lockout'),
('lockout_duration',   '15', 'security', 'Account lockout duration in minutes'),
('otp_expiration',     '10', 'security', 'OTP expiration time in minutes'),
('logout_warning',     '2',  'security', 'Show logout warning X minutes before timeout')
ON CONFLICT (setting_key) DO NOTHING;

-- Mark migration versions as applied
INSERT INTO db_version (version) VALUES
(20),(21),(22),(23),(24),(25),(26),(27),(28),(29),
(30),(31),(32),(33),(34),(35),(36),(37),(38),(39),(40),(41),(42),(43),(44),(45),(46)
ON CONFLICT (version) DO NOTHING;

-- ============================================
-- MIGRATION: v46 (latest - IEP Steps with Domain)
-- ============================================

CREATE TABLE IF NOT EXISTS iep_records (
    id SERIAL PRIMARY KEY,
    student_id INT NOT NULL,
    pdsp_id INT NOT NULL,
    drafted_by INT NOT NULL,
    school_year VARCHAR(20) NOT NULL,
    status iep_status DEFAULT 'draft',
    signed_document_path VARCHAR(500) NULL,
    re_evaluation_date DATE NULL,
    locked_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
    FOREIGN KEY (pdsp_id) REFERENCES pdsp_records(id) ON DELETE CASCADE,
    FOREIGN KEY (drafted_by) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_iep_record_student ON iep_records(student_id);
CREATE INDEX IF NOT EXISTS idx_iep_record_status ON iep_records(status);
CREATE INDEX IF NOT EXISTS idx_iep_record_school_year ON iep_records(school_year);

CREATE TABLE IF NOT EXISTS iep_signatories (
    id SERIAL PRIMARY KEY,
    iep_id INT NOT NULL,
    signatory_role iep_signatory_role NOT NULL,
    signatory_name VARCHAR(200) NOT NULL,
    signature_image_path VARCHAR(500) NULL,
    signed_at TIMESTAMP NULL,
    FOREIGN KEY (iep_id) REFERENCES iep_records(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_iep_signatory ON iep_records(id);
CREATE INDEX IF NOT EXISTS idx_iep_signatory_role ON iep_signatories(signatory_role);

CREATE TABLE IF NOT EXISTS iep_copies (
    id SERIAL PRIMARY KEY,
    iep_id INT NOT NULL,
    sent_to INT NOT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    viewed_at TIMESTAMP NULL,
    FOREIGN KEY (iep_id) REFERENCES iep_records(id) ON DELETE CASCADE,
    FOREIGN KEY (sent_to) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_iep_copy_id ON iep_copies(iep_id);
CREATE INDEX IF NOT EXISTS idx_iep_copy_sent_to ON iep_copies(sent_to);

CREATE TABLE IF NOT EXISTS iep_steps (
    id SERIAL PRIMARY KEY,
    iep_id INT NOT NULL,
    step_number INT NOT NULL,
    step_domain VARCHAR(100),
    objectives TEXT NULL,
    observation TEXT NULL,
    activities TEXT NULL,
    materials TEXT NULL,
    evaluation TEXT NULL,
    duration_lp VARCHAR(100) NULL,
    FOREIGN KEY (iep_id) REFERENCES iep_records(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_iep_step_id ON iep_steps(iep_id);
CREATE INDEX IF NOT EXISTS idx_iep_step_number ON iep_steps(step_number);

-- END MIGRATION v46
