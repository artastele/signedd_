-- ============================================================
-- MANUAL MIGRATION: v41 → v43
-- Run this in HeidiSQL on the sped_lms database.
-- Safe to run multiple times (uses IF NOT EXISTS / IF EXISTS).
-- ============================================================

-- ============================================================
-- v41 — Student Documents
-- ============================================================

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

-- ============================================================
-- v42 — Process 6 & 7 LMS Tables
-- ============================================================

CREATE TABLE IF NOT EXISTS lesson_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    iep_id INT NOT NULL,
    student_id INT NULL,
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
    document_path VARCHAR(500) NULL,
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

CREATE TABLE IF NOT EXISTS lesson_materials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lesson_plan_id INT NOT NULL,
    material_type ENUM('file','link','embed') NOT NULL,
    title VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NULL,
    external_url VARCHAR(1000) NULL,
    embed_type ENUM('youtube','gdrive','other') NULL,
    display_order INT DEFAULT 0,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lesson_plan_id) REFERENCES lesson_plans(id) ON DELETE CASCADE,
    INDEX idx_lesson_plan_id (lesson_plan_id),
    INDEX idx_display_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
    activity_data JSON NOT NULL,
    max_score INT NULL,
    due_date DATETIME NULL,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lesson_plan_id) REFERENCES lesson_plans(id) ON DELETE CASCADE,
    INDEX idx_lesson_plan_id (lesson_plan_id),
    INDEX idx_activity_type (activity_type),
    INDEX idx_display_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS lms_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    activity_id INT NOT NULL,
    student_id INT NOT NULL,
    submitted_by INT NOT NULL,
    file_path VARCHAR(500) NULL,
    answers JSON NULL,
    auto_score INT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (activity_id) REFERENCES lms_activities(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES student_records(id) ON DELETE CASCADE,
    FOREIGN KEY (submitted_by) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_submission (activity_id, student_id),
    INDEX idx_activity_id (activity_id),
    INDEX idx_student_id (student_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

-- ============================================================
-- v43 — Gamification Tables
-- ============================================================

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

-- ============================================================
-- Done. Verify with:
-- SELECT * FROM db_version ORDER BY version;
-- SHOW TABLES LIKE 'lesson%';
-- SHOW TABLES LIKE 'lms%';
-- SHOW TABLES LIKE 'learner%';
-- SHOW TABLES LIKE 'activity_stars';
-- SHOW TABLES LIKE 'student_documents';
-- ============================================================
