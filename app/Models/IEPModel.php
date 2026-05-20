<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 5
// Last modified: 2026-05-14
// Part of: SPED LMS — IEP Model (domains, core, header overrides, repository)

require_once __DIR__ . '/../../config/db.php';

class IEPModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * v46 migration may record db_version without adding step_domain (e.g. MySQL without
     * ADD COLUMN IF NOT EXISTS). Ensure column exists before INSERT/UPDATE that reference it.
     */
    private function ensureStepDomainColumnExists(): void {
        static $ok = false;
        if ($ok) {
            return;
        }
        try {
            $q = $this->db->query("
                SELECT COUNT(*) FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'iep_steps'
                  AND COLUMN_NAME = 'step_domain'
            ");
            if ((int) $q->fetchColumn() > 0) {
                $ok = true;
                return;
            }
            $this->db->exec('ALTER TABLE iep_steps ADD COLUMN step_domain VARCHAR(191) NULL AFTER step_number');
            $ok = true;
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            if (stripos($msg, 'Duplicate column name') !== false) {
                $ok = true;
                return;
            }
            error_log('IEPModel::ensureStepDomainColumnExists: ' . $msg);
        }
    }

    /**
     * v45 migration uses ADD COLUMN IF NOT EXISTS (not supported on older MySQL).
     * Ensures header snapshot columns exist so IEP save-part1 does not fail.
     */
    private function ensureIepRecordsHeaderColumns(): void {
        static $ok = false;
        if ($ok) {
            return;
        }
        $cols = [
            'header_learner_name'   => 'VARCHAR(255) NULL',
            'header_learner_age'    => 'VARCHAR(50) NULL',
            'header_lrn'            => 'VARCHAR(32) NULL',
            'header_section'        => 'VARCHAR(120) NULL',
            'header_teacher_name'   => 'VARCHAR(255) NULL',
            'header_school_name'    => 'VARCHAR(255) NULL',
            'header_grade_level'    => 'VARCHAR(100) NULL',
        ];
        try {
            foreach ($cols as $name => $ddl) {
                $chk = $this->db->prepare('
                    SELECT COUNT(*) FROM information_schema.COLUMNS
                    WHERE TABLE_SCHEMA = DATABASE()
                      AND TABLE_NAME = \'iep_records\'
                      AND COLUMN_NAME = ?
                ');
                $chk->execute([$name]);
                if ((int) $chk->fetchColumn() > 0) {
                    continue;
                }
                $this->db->exec('ALTER TABLE iep_records ADD COLUMN `' . $name . '` ' . $ddl);
            }
            $ok = true;
        } catch (\Throwable $e) {
            if (stripos($e->getMessage(), 'Duplicate column name') !== false) {
                $ok = true;
                return;
            }
            error_log('IEPModel::ensureIepRecordsHeaderColumns: ' . $e->getMessage());
        }
    }

    /**
     * Ensure Part I save dependencies exist (domains/core tables + header columns).
     */
    public function ensurePartOneSaveSchema(): void {
        try {
            $this->db->exec("CREATE TABLE IF NOT EXISTS iep_domains (
                id INT AUTO_INCREMENT PRIMARY KEY,
                iep_id INT NOT NULL,
                domain_name VARCHAR(200) NOT NULL,
                display_order INT NOT NULL DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (iep_id) REFERENCES iep_records(id) ON DELETE CASCADE,
                INDEX idx_iep_id (iep_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            $this->db->exec("CREATE TABLE IF NOT EXISTS iep_core (
                id INT AUTO_INCREMENT PRIMARY KEY,
                iep_id INT NOT NULL,
                developmental_domain TEXT NULL,
                priority_needs TEXT NULL,
                terminal_objectives TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (iep_id) REFERENCES iep_records(id) ON DELETE CASCADE,
                UNIQUE KEY unique_iep_core (iep_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } catch (\Throwable $e) {
            error_log('IEPModel::ensurePartOneSaveSchema tables: ' . $e->getMessage());
        }
        $this->ensureIepRecordsHeaderColumns();
    }

    // ============================================================
    // IEP RECORDS
    // ============================================================

    /**
     * Create a new IEP draft
     */
    public function create($studentId, $pdspId, $draftedBy, $schoolYear) {
        $stmt = $this->db->prepare("
            INSERT INTO iep_records (student_id, pdsp_id, drafted_by, school_year, status, created_at, updated_at)
            VALUES (:student_id, :pdsp_id, :drafted_by, :school_year, 'draft', NOW(), NOW())
        ");
        $stmt->execute([
            'student_id'  => $studentId,
            'pdsp_id'     => $pdspId,
            'drafted_by'  => $draftedBy,
            'school_year' => $schoolYear,
        ]);
        return $this->db->lastInsertId();
    }

    /**
     * Find IEP by ID — joins student, pdsp, drafter
     */
    public function findById($iepId) {
        $stmt = $this->db->prepare("
            SELECT ir.*,
                   sr.student_name, sr.lrn, sr.enrollment_id,
                   u.name AS drafted_by_name,
                   pr.status AS pdsp_status, pr.meeting_id,
                   pr.signed_document_path AS pdsp_signed_document_path
            FROM iep_records ir
            JOIN student_records sr ON ir.student_id = sr.id
            JOIN users u            ON ir.drafted_by  = u.id
            JOIN pdsp_records pr    ON ir.pdsp_id     = pr.id
            WHERE ir.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $iepId]);
        return $stmt->fetch();
    }

    /**
     * Get all IEPs for a student (repository — all versions)
     */
    public function getByStudent($studentId) {
        $stmt = $this->db->prepare("
            SELECT ir.*, u.name AS drafted_by_name
            FROM iep_records ir
            JOIN users u ON ir.drafted_by = u.id
            WHERE ir.student_id = :student_id
            ORDER BY ir.created_at DESC
        ");
        $stmt->execute(['student_id' => $studentId]);
        return $stmt->fetchAll();
    }

    /**
     * Get latest IEP for a student (most recent row)
     */
    public function getLatestByStudent($studentId) {
        $stmt = $this->db->prepare("
            SELECT ir.*, u.name AS drafted_by_name
            FROM iep_records ir
            JOIN users u ON ir.drafted_by = u.id
            WHERE ir.student_id = :student_id
            ORDER BY ir.created_at DESC
            LIMIT 1
        ");
        $stmt->execute(['student_id' => $studentId]);
        return $stmt->fetch();
    }

    /**
     * Get all IEPs drafted by a specific teacher
     */
    public function getByTeacher($userId) {
        $stmt = $this->db->prepare("
            SELECT ir.*, sr.student_name, sr.lrn, u.name AS drafted_by_name
            FROM iep_records ir
            JOIN student_records sr ON ir.student_id = sr.id
            JOIN users u ON ir.drafted_by = u.id
            WHERE ir.drafted_by = :user_id
            ORDER BY ir.created_at DESC
        ");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    /**
     * Permanently remove a draft IEP (and cascaded child rows). Returns false if not a draft or not owned.
     */
    public function deleteDraftIep(int $iepId, int $draftedByUserId, bool $allowAdmin = false): bool {
        $iep = $this->findById($iepId);
        if (!$iep || ($iep['status'] ?? '') !== 'draft') {
            return false;
        }
        if (!$allowAdmin && (int) ($iep['drafted_by'] ?? 0) !== $draftedByUserId) {
            return false;
        }
        $stmt = $this->db->prepare('DELETE FROM iep_records WHERE id = :id AND status = :st');
        return $stmt->execute(['id' => $iepId, 'st' => 'draft']) && $stmt->rowCount() > 0;
    }

    /**
     * Get IEPs visible to guidance / principal / parent (completed or in signing)
     */
    public function getSignedForRole($role, $userId) {
        if ($role === 'parent') {
            $stmt = $this->db->prepare("
                SELECT ir.*, sr.student_name, sr.lrn, u.name AS drafted_by_name
                FROM iep_records ir
                JOIN student_records sr ON ir.student_id = sr.id
                JOIN enrollment_submissions es ON sr.enrollment_id = es.id
                JOIN users u ON ir.drafted_by = u.id
                WHERE ir.status IN ('signed','signing')
                AND es.parent_id = :user_id
                ORDER BY ir.created_at DESC
            ");
            $stmt->execute(['user_id' => $userId]);
        } else {
            $stmt = $this->db->prepare("
                SELECT ir.*, sr.student_name, sr.lrn, u.name AS drafted_by_name
                FROM iep_records ir
                JOIN student_records sr ON ir.student_id = sr.id
                JOIN users u ON ir.drafted_by = u.id
                WHERE ir.status IN ('signed','signing')
                ORDER BY ir.created_at DESC
            ");
            $stmt->execute();
        }
        return $stmt->fetchAll();
    }

    /**
     * Update IEP header fields + core data
     */
    public function update($iepId, $data) {
        $allowed = [
            'school_year', 'status', 'signed_document_path', 're_evaluation_date', 'signing_method',
            'header_learner_name', 'header_learner_age', 'header_lrn', 'header_section',
            'header_teacher_name', 'header_school_name', 'header_grade_level',
        ];
        $sets = [];
        $params = ['id' => $iepId];
        foreach ($allowed as $col) {
            if (array_key_exists($col, $data)) {
                $sets[] = "$col = :$col";
                $params[$col] = $data[$col];
            }
        }
        if (empty($sets)) return false;
        $sets[] = "updated_at = NOW()";
        $stmt = $this->db->prepare("UPDATE iep_records SET " . implode(', ', $sets) . " WHERE id = :id");
        return $stmt->execute($params);
    }

    /**
     * Mark IEP as signed (living document — no lock timestamp).
     *
     * @param string $signingMethod print_upload (meeting attestation / scanned doc path) or digital
     */
    public function markSigned($iepId, string $signingMethod = 'digital') {
        try {
            $stmt = $this->db->prepare("
                UPDATE iep_records
                SET status = 'signed',
                    signing_method = :sm,
                    updated_at = NOW()
                WHERE id = :id
            ");
            return $stmt->execute(['id' => $iepId, 'sm' => $signingMethod]);
        } catch (\Throwable $e) {
            $stmt = $this->db->prepare("
                UPDATE iep_records SET status = 'signed', updated_at = NOW() WHERE id = :id
            ");
            return $stmt->execute(['id' => $iepId]);
        }
    }

    /**
     * Lesson plans linked from Process 5 IEP steps (junction iep_step_lesson_plans).
     *
     * @return int[]
     */
    public function getLessonPlanIdsLinkedToIep(int $iepId): array {
        $stmt = $this->db->prepare("
            SELECT DISTINCT j.lesson_plan_id
            FROM iep_step_lesson_plans j
            INNER JOIN iep_steps s ON s.id = j.iep_step_id
            WHERE s.iep_id = :iep_id
        ");
        $stmt->execute(['iep_id' => $iepId]);
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN) ?: []);
    }

    public function insertIepEditLog(int $iepId, int $userId, string $fieldName, ?string $oldValue, ?string $newValue): void {
        $stmt = $this->db->prepare("
            INSERT INTO iep_edit_logs (iep_id, edited_by, field_name, old_value, new_value)
            VALUES (:iep_id, :uid, :fn, :ov, :nv)
        ");
        $stmt->execute([
            'iep_id' => $iepId,
            'uid'    => $userId,
            'fn'     => $fieldName,
            'ov'     => $oldValue,
            'nv'     => $newValue,
        ]);
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function getIepEditLogs(int $iepId): array {
        $stmt = $this->db->prepare("
            SELECT l.field_name, l.old_value, l.new_value, l.edited_at, u.name AS edited_by_name
            FROM iep_edit_logs l
            JOIN users u ON u.id = l.edited_by
            WHERE l.iep_id = :iep_id
            ORDER BY l.edited_at DESC, l.id DESC
            LIMIT 500
        ");
        $stmt->execute(['iep_id' => $iepId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Get signed PDSP for a student (to link when creating IEP)
     */
    public function getSignedPDSP($studentId) {
        $stmt = $this->db->prepare("
            SELECT pr.*
            FROM pdsp_records pr
            JOIN iep_meetings im ON pr.meeting_id = im.id
            WHERE im.student_id = :student_id
            AND pr.status = 'signed'
            ORDER BY pr.created_at DESC
            LIMIT 1
        ");
        $stmt->execute(['student_id' => $studentId]);
        return $stmt->fetch();
    }

    // ============================================================
    // SIGNATORIES (SIMPLIFIED)
    // ============================================================

    private static ?bool $iepSignatoriesHasSendStatus = null;

    private function iepSignatoriesHasSendStatusColumn(): bool {
        if (self::$iepSignatoriesHasSendStatus !== null) {
            return self::$iepSignatoriesHasSendStatus;
        }
        try {
            $q = $this->db->query("
                SELECT COUNT(*) FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'iep_signatories'
                  AND COLUMN_NAME = 'send_status'
            ");
            self::$iepSignatoriesHasSendStatus = ((int) $q->fetchColumn() > 0);
        } catch (\Throwable $e) {
            self::$iepSignatoriesHasSendStatus = false;
        }
        return self::$iepSignatoriesHasSendStatus;
    }

    /**
     * v44 migration may be missing on some DBs. Digital signing UI requires send_status + sent_at.
     */
    public function ensureIepSignatoriesDigitalColumns(): void {
        try {
            $q = $this->db->query("
                SELECT COUNT(*) FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'iep_signatories'
                  AND COLUMN_NAME = 'send_status'
            ");
            if ((int) $q->fetchColumn() === 0) {
                $this->db->exec("
                    ALTER TABLE iep_signatories
                    ADD COLUMN send_status ENUM('not_sent','pending','signed') NOT NULL DEFAULT 'not_sent' AFTER signatory_name
                ");
            }
            $q2 = $this->db->query("
                SELECT COUNT(*) FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'iep_signatories'
                  AND COLUMN_NAME = 'signature_request_sent_at'
            ");
            if ((int) $q2->fetchColumn() === 0) {
                $this->db->exec("
                    ALTER TABLE iep_signatories
                    ADD COLUMN signature_request_sent_at TIMESTAMP NULL AFTER send_status
                ");
            }
        } catch (\Throwable $e) {
            if (stripos($e->getMessage(), 'Duplicate column') === false) {
                error_log('IEPModel::ensureIepSignatoriesDigitalColumns: ' . $e->getMessage());
            }
        }
        self::$iepSignatoriesHasSendStatus = null;
    }

    /**
     * Replace all signatory rows (meeting-record or digital-collect flow).
     *
     * Each row: role, name, send_status ('signed'|'pending'|'not_sent'),
     * optional signature_image_path, optional signed_at (Y-m-d H:i:s or null).
     */
    public function replaceSignatoryRows(int $iepId, array $rows): void {
        $this->db->prepare('DELETE FROM iep_signatories WHERE iep_id = ?')->execute([$iepId]);
        if (empty($rows)) {
            return;
        }
        $extended = $this->iepSignatoriesHasSendStatusColumn();
        if ($extended) {
            $stmt = $this->db->prepare('
                INSERT INTO iep_signatories
                    (iep_id, signatory_role, signatory_name, send_status, signature_image_path, signed_at, signature_request_sent_at)
                VALUES
                    (:iep_id, :role, :name, :send_status, :sig_path, :signed_at, :sent_at)
            ');
        } else {
            $stmt = $this->db->prepare('
                INSERT INTO iep_signatories
                    (iep_id, signatory_role, signatory_name, signed_at, signature_image_path)
                VALUES
                    (:iep_id, :role, :name, :signed_at, :sig_path)
            ');
        }
        foreach ($rows as $r) {
            $signedAt = $r['signed_at'] ?? null;
            $sigPath  = $r['signature_image_path'] ?? null;
            if ($extended) {
                $stmt->execute([
                    'iep_id'      => $iepId,
                    'role'        => $r['role'],
                    'name'        => $r['name'],
                    'send_status' => $r['send_status'] ?? 'signed',
                    'sig_path'    => $sigPath,
                    'signed_at'   => $signedAt,
                    'sent_at'     => $r['signature_request_sent_at'] ?? null,
                ]);
            } else {
                $stmt->execute([
                    'iep_id'    => $iepId,
                    'role'      => $r['role'],
                    'name'      => $r['name'],
                    'signed_at' => $signedAt,
                    'sig_path'  => $sigPath,
                ]);
            }
        }
    }

    /**
     * Save signatories (replaces existing) — meeting record: all attested now.
     */
    public function saveSignatories($iepId, array $signatories) {
        $rows = [];
        $now = date('Y-m-d H:i:s');
        foreach ($signatories as $sig) {
            $rows[] = [
                'role'                 => $sig['role'],
                'name'                 => $sig['name'],
                'send_status'          => 'signed',
                'signature_image_path' => null,
                'signed_at'            => $now,
            ];
        }
        $this->replaceSignatoryRows((int) $iepId, $rows);
        return true;
    }

    public function getSignatoryById(int $signatoryId): ?array {
        $stmt = $this->db->prepare('SELECT * FROM iep_signatories WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $signatoryId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Persist canvas signature (relative path under public/).
     */
    public function saveSignatureImage(int $signatoryId, string $relativePath): bool {
        if ($this->iepSignatoriesHasSendStatusColumn()) {
            $stmt = $this->db->prepare("
                UPDATE iep_signatories
                SET signature_image_path = :path,
                    signed_at = COALESCE(signed_at, NOW()),
                    send_status = 'signed'
                WHERE id = :id
            ");
        } else {
            $stmt = $this->db->prepare("
                UPDATE iep_signatories
                SET signature_image_path = :path,
                    signed_at = COALESCE(signed_at, NOW())
                WHERE id = :id
            ");
        }
        return $stmt->execute(['path' => $relativePath, 'id' => $signatoryId]);
    }

    /**
     * True when every signatory row has a captured path (image or f2f marker).
     */
    public function allSignatoriesSignatureComplete(int $iepId): bool {
        $stmt = $this->db->prepare('
            SELECT COUNT(*) FROM iep_signatories
            WHERE iep_id = :iep_id AND (signature_image_path IS NULL OR TRIM(signature_image_path) = "")
        ');
        $stmt->execute(['iep_id' => $iepId]);
        return (int) $stmt->fetchColumn() === 0;
    }

    public function markSignatoryRequestSent(int $signatoryId): void {
        if (!$this->iepSignatoriesHasSendStatusColumn()) {
            return;
        }
        $stmt = $this->db->prepare('
            UPDATE iep_signatories SET signature_request_sent_at = NOW() WHERE id = :id
        ');
        $stmt->execute(['id' => $signatoryId]);
    }

    /**
     * Get signatories for an IEP
     */
    public function getSignatories($iepId) {
        $stmt = $this->db->prepare("
            SELECT * FROM iep_signatories WHERE iep_id = :iep_id ORDER BY id ASC
        ");
        $stmt->execute(['iep_id' => $iepId]);
        return $stmt->fetchAll();
    }

    // ============================================================
    // STUDENT DATA & ELIGIBILITY
    // ============================================================

    /**
     * Get students eligible for new IEP (have signed PDSP, no active IEP draft)
     */
    public function getEligibleStudents($teacherId) {
        $stmt = $this->db->prepare("
            SELECT DISTINCT sr.id, sr.student_name, sr.lrn,
                   pr.created_at as pdsp_signed_at
            FROM student_records sr
            JOIN iep_meetings im ON sr.id = im.student_id
            JOIN pdsp_records pr ON im.id = pr.meeting_id
            WHERE pr.status = 'signed'
            AND sr.id NOT IN (
                SELECT student_id FROM iep_records 
                WHERE status IN ('draft') 
                AND YEAR(created_at) = YEAR(NOW())
            )
            ORDER BY pr.created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get student auto-fill data for IEP header
     */
    public function getStudentAutoFill($studentId) {
        $stmt = $this->db->prepare("
            SELECT sr.*, es.first_name, es.middle_name, es.last_name, es.birth_date,
                   es.current_house_no, es.current_barangay, es.current_city, es.current_province,
                   es.grade_level_to_enroll, es.school_year AS enrollment_school_year
            FROM student_records sr
            JOIN enrollment_submissions es ON sr.enrollment_id = es.id
            WHERE sr.id = :student_id
            LIMIT 1
        ");
        $stmt->execute(['student_id' => $studentId]);
        return $stmt->fetch();
    }

    // ============================================================
    // IEP DOMAINS, CORE, PDSP REFERENCE (Process 5 form Sections 3–4)
    // ============================================================

    /**
     * PDSP domain rows for read-only reference panel
     */
    public function getPdspDomainRows(int $pdspId): array {
        $stmt = $this->db->prepare("
            SELECT id, domain_name, sub_domain, skills_description, mastered,
                   educational_recommendation, q1_level, q2_level
            FROM pdsp_domains
            WHERE pdsp_id = :pdsp_id
            ORDER BY id ASC
        ");
        $stmt->execute(['pdsp_id' => $pdspId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Suggested priority needs text from PDSP rows not marked mastered
     */
    public function suggestPriorityNeedsFromPdsp(int $pdspId): string {
        $rows = $this->getPdspDomainRows($pdspId);
        $parts = [];
        foreach ($rows as $r) {
            if ((int) ($r['mastered'] ?? 0) === 1) {
                continue;
            }
            $line = trim($r['domain_name'] ?? '');
            if (!empty($r['skills_description'])) {
                $line .= ($line !== '' ? ' — ' : '') . trim($r['skills_description']);
            }
            if ($line !== '') {
                $parts[] = $line;
            }
        }
        return implode("\n", $parts);
    }

    public function getIepDomains(int $iepId): array {
        $stmt = $this->db->prepare("
            SELECT id, domain_name, display_order
            FROM iep_domains
            WHERE iep_id = :iep_id
            ORDER BY display_order ASC, id ASC
        ");
        $stmt->execute(['iep_id' => $iepId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Copy PDSP domain names into iep_domains when the IEP has none yet
     */
    public function seedIepDomainsFromPdspIfEmpty(int $iepId, int $pdspId): void {
        $c = $this->db->prepare("SELECT COUNT(*) FROM iep_domains WHERE iep_id = :iep_id");
        $c->execute(['iep_id' => $iepId]);
        if ((int) $c->fetchColumn() > 0) {
            return;
        }
        $src = $this->db->prepare("
            SELECT domain_name FROM pdsp_domains WHERE pdsp_id = :pdsp_id ORDER BY id ASC
        ");
        $src->execute(['pdsp_id' => $pdspId]);
        $names = $src->fetchAll(PDO::FETCH_COLUMN);
        if (empty($names)) {
            return;
        }
        $ins = $this->db->prepare("
            INSERT INTO iep_domains (iep_id, domain_name, display_order)
            VALUES (:iep_id, :domain_name, :display_order)
        ");
        $ord = 0;
        foreach ($names as $n) {
            $n = trim((string) $n);
            if ($n === '') {
                continue;
            }
            $ins->execute([
                'iep_id'         => $iepId,
                'domain_name'   => $n,
                'display_order' => $ord++,
            ]);
        }
    }

    /**
     * Replace all domain tags for an IEP (prepared statements per row)
     */
    public function replaceIepDomains(int $iepId, array $domainNames): void {
        $this->db->beginTransaction();
        try {
            $del = $this->db->prepare("DELETE FROM iep_domains WHERE iep_id = :iep_id");
            $del->execute(['iep_id' => $iepId]);
            $ins = $this->db->prepare("
                INSERT INTO iep_domains (iep_id, domain_name, display_order)
                VALUES (:iep_id, :domain_name, :display_order)
            ");
            $ord = 0;
            foreach ($domainNames as $raw) {
                $name = trim((string) $raw);
                if ($name === '') {
                    continue;
                }
                $ins->execute([
                    'iep_id'         => $iepId,
                    'domain_name'   => $name,
                    'display_order' => $ord++,
                ]);
            }
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getIepCore(int $iepId): ?array {
        $stmt = $this->db->prepare("SELECT * FROM iep_core WHERE iep_id = :iep_id LIMIT 1");
        $stmt->execute(['iep_id' => $iepId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function upsertIepCore(int $iepId, ?string $developmental, ?string $priority, ?string $terminal): void {
        $stmt = $this->db->prepare("
            INSERT INTO iep_core (iep_id, developmental_domain, priority_needs, terminal_objectives)
            VALUES (:iep_id, :dd, :pn, :to)
            ON DUPLICATE KEY UPDATE
                developmental_domain = VALUES(developmental_domain),
                priority_needs       = VALUES(priority_needs),
                terminal_objectives  = VALUES(terminal_objectives)
        ");
        $stmt->execute([
            'iep_id' => $iepId,
            'dd'     => $developmental,
            'pn'     => $priority,
            'to'     => $terminal,
        ]);
    }

    // ============================================================
    // IEP STEPS (Section 5) + junctions to Process 6 / 7 data
    // ============================================================

    public function ensureDefaultStepForIep(int $iepId): void {
        $this->ensureStepDomainColumnExists();
        $c = $this->db->prepare("SELECT COUNT(*) FROM iep_steps WHERE iep_id = :iep_id");
        $c->execute(['iep_id' => $iepId]);
        if ((int) $c->fetchColumn() > 0) {
            return;
        }
        $ins = $this->db->prepare("
            INSERT INTO iep_steps (iep_id, step_number, step_domain, step_objective, duration_lp, instructional_evaluation, observation, observation_unlocked)
            VALUES (:iep_id, 1, NULL, '', '', '', '', 0)
        ");
        $ins->execute(['iep_id' => $iepId]);
    }

    public function getStepsForIep(int $iepId): array {
        $stmt = $this->db->prepare("
            SELECT s.*
            FROM iep_steps s
            WHERE s.iep_id = :iep_id
            ORDER BY s.step_number ASC, s.id ASC
        ");
        $stmt->execute(['iep_id' => $iepId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function findStepIdByIepAndStepNumber(int $iepId, int $stepNumber): int {
        $stmt = $this->db->prepare("
            SELECT id FROM iep_steps
            WHERE iep_id = :iep_id AND step_number = :n
            LIMIT 1
        ");
        $stmt->execute(['iep_id' => $iepId, 'n' => $stepNumber]);
        $id = $stmt->fetchColumn();
        return $id ? (int) $id : 0;
    }

    public function stepBelongsToIep(int $stepId, int $iepId): bool {
        $stmt = $this->db->prepare("SELECT 1 FROM iep_steps WHERE id = :id AND iep_id = :iep_id LIMIT 1");
        $stmt->execute(['id' => $stepId, 'iep_id' => $iepId]);
        return (bool) $stmt->fetchColumn();
    }

    public function countStepJunctionLinks(int $stepId): int {
        $a = $this->db->prepare("SELECT COUNT(*) FROM iep_step_lesson_plans WHERE iep_step_id = :sid");
        $a->execute(['sid' => $stepId]);
        $b = $this->db->prepare("SELECT COUNT(*) FROM iep_step_materials WHERE iep_step_id = :sid");
        $b->execute(['sid' => $stepId]);
        return (int) $a->fetchColumn() + (int) $b->fetchColumn();
    }

    public function isObservationUnlockedForStep(int $stepId, int $studentId): bool {
        $pub = $this->db->prepare("
            SELECT 1
            FROM iep_step_lesson_plans j
            JOIN lesson_plans lp ON lp.id = j.lesson_plan_id
            WHERE j.iep_step_id = :sid AND lp.status = 'published'
            LIMIT 1
        ");
        $pub->execute(['sid' => $stepId]);
        if (!$pub->fetchColumn()) {
            return false;
        }
        $sub = $this->db->prepare("
            SELECT 1
            FROM iep_step_lesson_plans j
            JOIN lesson_plans lp ON lp.id = j.lesson_plan_id
            JOIN lms_activities a ON a.lesson_plan_id = lp.id
            JOIN lms_submissions s ON s.activity_id = a.id AND s.student_id = :student_id
            WHERE j.iep_step_id = :sid
            LIMIT 1
        ");
        $sub->execute(['sid' => $stepId, 'student_id' => $studentId]);
        return (bool) $sub->fetchColumn();
    }

    public function refreshObservationUnlockedForIep(int $iepId): void {
        $stmt = $this->db->prepare("SELECT student_id FROM iep_records WHERE id = :iep_id LIMIT 1");
        $stmt->execute(['iep_id' => $iepId]);
        $studentId = (int) $stmt->fetchColumn();
        if ($studentId <= 0) {
            return;
        }
        $ids = $this->db->prepare("SELECT id FROM iep_steps WHERE iep_id = :iep_id");
        $ids->execute(['iep_id' => $iepId]);
        $upd = $this->db->prepare("UPDATE iep_steps SET observation_unlocked = :u, updated_at = NOW() WHERE id = :id");
        foreach ($ids->fetchAll(PDO::FETCH_COLUMN) as $sid) {
            $sid = (int) $sid;
            $flag = $this->isObservationUnlockedForStep($sid, $studentId) ? 1 : 0;
            $upd->execute(['u' => $flag, 'id' => $sid]);
        }
    }

    public function getLessonPlansLinkedToStep(int $stepId): array {
        $stmt = $this->db->prepare("
            SELECT lp.id, lp.title, lp.status, lp.assignment_type, lp.pdsp_domain, lp.document_path
            FROM iep_step_lesson_plans j
            JOIN lesson_plans lp ON lp.id = j.lesson_plan_id
            WHERE j.iep_step_id = :sid
            ORDER BY j.id ASC
        ");
        $stmt->execute(['sid' => $stepId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Materials shown for this IEP step: explicit iep_step_materials links plus
     * all lesson_materials on lesson plans linked via iep_step_lesson_plans (workspace path).
     */
    public function getMaterialsLinkedToStep(int $stepId): array {
        $stmt = $this->db->prepare("
            SELECT id, title, material_type, file_path, external_url, uploaded_at
            FROM (
                SELECT lm.id, lm.title, lm.material_type, lm.file_path, lm.external_url, lm.uploaded_at, lm.display_order AS ord
                FROM iep_step_materials j
                JOIN lesson_materials lm ON lm.id = j.material_id
                WHERE j.iep_step_id = :sid
                UNION
                SELECT lm.id, lm.title, lm.material_type, lm.file_path, lm.external_url, lm.uploaded_at, lm.display_order AS ord
                FROM iep_step_lesson_plans j
                JOIN lesson_materials lm ON lm.lesson_plan_id = j.lesson_plan_id
                WHERE j.iep_step_id = :sid2
            ) AS combined
            ORDER BY combined.ord ASC, combined.id ASC
        ");
        $stmt->execute(['sid' => $stepId, 'sid2' => $stepId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function linkLessonPlanToStep(int $stepId, int $lessonPlanId): void {
        $stmt = $this->db->prepare("
            INSERT IGNORE INTO iep_step_lesson_plans (iep_step_id, lesson_plan_id)
            VALUES (:step_id, :lesson_plan_id)
        ");
        $stmt->execute(['step_id' => $stepId, 'lesson_plan_id' => $lessonPlanId]);
    }

    public function unlinkLessonPlanFromStep(int $stepId, int $lessonPlanId): void {
        $stmt = $this->db->prepare("
            DELETE FROM iep_step_lesson_plans WHERE iep_step_id = :sid AND lesson_plan_id = :lpid
        ");
        $stmt->execute(['sid' => $stepId, 'lpid' => $lessonPlanId]);
    }

    public function insertStepRow(int $iepId, int $stepNumber): int {
        $this->ensureStepDomainColumnExists();
        $stmt = $this->db->prepare("
            INSERT INTO iep_steps (iep_id, step_number, step_domain, step_objective, duration_lp, instructional_evaluation, observation, observation_unlocked)
            VALUES (:iep_id, :sn, NULL, '', '', '', '', 0)
        ");
        $stmt->execute(['iep_id' => $iepId, 'sn' => $stepNumber]);
        return (int) $this->db->lastInsertId();
    }

    public function updateStepFields(
        int $stepId,
        ?string $stepDomain,
        string $objective,
        string $duration,
        string $eval,
        ?string $observation,
        bool $observationUnlocked
    ): void {
        $this->ensureStepDomainColumnExists();
        $sd = $stepDomain !== null && $stepDomain !== '' ? $stepDomain : null;
        if ($observationUnlocked) {
            $stmt = $this->db->prepare("
                UPDATE iep_steps
                SET step_domain = :sd, step_objective = :o, duration_lp = :d, instructional_evaluation = :e, observation = :ob, updated_at = NOW()
                WHERE id = :id
            ");
            $stmt->execute([
                'sd' => $sd,
                'o'  => $objective,
                'd'  => $duration,
                'e'  => $eval,
                'ob' => $observation ?? '',
                'id' => $stepId,
            ]);
        } else {
            $stmt = $this->db->prepare("
                UPDATE iep_steps
                SET step_domain = :sd, step_objective = :o, duration_lp = :d, instructional_evaluation = :e, updated_at = NOW()
                WHERE id = :id
            ");
            $stmt->execute([
                'sd' => $sd,
                'o' => $objective,
                'd' => $duration,
                'e' => $eval,
                'id' => $stepId,
            ]);
        }
    }

    public function deleteStepIfAllowed(int $stepId): bool {
        if ($this->countStepJunctionLinks($stepId) > 0) {
            return false;
        }
        $stmt = $this->db->prepare("DELETE FROM iep_steps WHERE id = :id");
        $stmt->execute(['id' => $stepId]);
        return $stmt->rowCount() > 0;
    }

    public function renumberStepsForIep(int $iepId): void {
        $rows = $this->getStepsForIep($iepId);
        $n = 1;
        $upd = $this->db->prepare("UPDATE iep_steps SET step_number = :n WHERE id = :id");
        foreach ($rows as $r) {
            $upd->execute(['n' => $n++, 'id' => (int) $r['id']]);
        }
    }

    /**
     * First domain chip label for this IEP (used when step rows omit per-step domain).
     */
    public function getFirstIepDomainName(int $iepId): string {
        $stmt = $this->db->prepare('SELECT domain_name FROM iep_domains WHERE iep_id = :id ORDER BY id ASC LIMIT 1');
        $stmt->execute(['id' => $iepId]);
        $v = $stmt->fetchColumn();
        return $v ? trim((string) $v) : '';
    }

    /**
     * Sync step rows from ordered payload (ids optional for existing rows).
     *
     * @param array<int,array<string,mixed>> $rows
     */
    public function syncIepStepsFromPayload(int $iepId, array $rows): void {
        $this->db->beginTransaction();
        try {
            $stmtIds = $this->db->prepare("SELECT id FROM iep_steps WHERE iep_id = :iep_id ORDER BY step_number ASC, id ASC");
            $stmtIds->execute(['iep_id' => $iepId]);
            $existing = array_map('intval', $stmtIds->fetchAll(PDO::FETCH_COLUMN));

            $fallbackDomain = $this->getFirstIepDomainName($iepId);

            $kept = [];
            $ord  = 1;
            foreach ($rows as $r) {
                $obj = trim((string) ($r['step_objective'] ?? $r['objective'] ?? ''));
                $dur = trim((string) ($r['duration_lp'] ?? $r['strategies'] ?? ''));
                $ev  = trim((string) ($r['instructional_evaluation'] ?? $r['evaluation'] ?? ''));
                $dom = trim((string) ($r['step_domain'] ?? $r['domain'] ?? ''));
                $obs = isset($r['observation']) ? trim((string) $r['observation']) : '';
                $id  = isset($r['id']) ? (int) $r['id'] : 0;

                $unlocked = false;
                if ($id > 0) {
                    $chk = $this->db->prepare("SELECT observation_unlocked FROM iep_steps WHERE id = :id AND iep_id = :iep_id LIMIT 1");
                    $chk->execute(['id' => $id, 'iep_id' => $iepId]);
                    $unlocked = ((int) $chk->fetchColumn()) === 1;
                }
                if ($id > 0 && $unlocked && $obs === '') {
                    $obStmt = $this->db->prepare("SELECT observation FROM iep_steps WHERE id = :id AND iep_id = :iep_id LIMIT 1");
                    $obStmt->execute(['id' => $id, 'iep_id' => $iepId]);
                    $obs = (string) ($obStmt->fetchColumn() ?: '');
                }

                if ($dom === '' && $fallbackDomain !== '') {
                    $dom = $fallbackDomain;
                }
                $domainParam = $dom !== '' ? $dom : null;

                if ($id > 0 && in_array($id, $existing, true)) {
                    $this->updateStepFields($id, $domainParam, $obj, $dur, $ev, $obs, $unlocked);
                    $num = $this->db->prepare("UPDATE iep_steps SET step_number = :n WHERE id = :id");
                    $num->execute(['n' => $ord, 'id' => $id]);
                    $kept[] = $id;
                } else {
                    $nid = $this->insertStepRow($iepId, $ord);
                    $this->updateStepFields($nid, $domainParam, $obj, $dur, $ev, $obs, false);
                    $kept[] = $nid;
                }
                $ord++;
            }

            foreach ($existing as $eid) {
                if (!in_array($eid, $kept, true)) {
                    if ($this->countStepJunctionLinks($eid) > 0) {
                        throw new \RuntimeException(
                            'Cannot remove a step that still has linked lesson plans or materials. Remove those links first.'
                        );
                    }
                    $this->deleteStepIfAllowed($eid);
                }
            }

            $this->renumberStepsForIep($iepId);
            $this->refreshObservationUnlockedForIep($iepId);
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getProgressSubmissionsForStep(int $stepId, int $studentId): array {
        $stmt = $this->db->prepare("
            SELECT lp.title AS lesson_plan_title,
                   a.title AS activity_title,
                   a.max_score,
                   s.submitted_at,
                   s.auto_score
            FROM iep_step_lesson_plans j
            JOIN lesson_plans lp ON lp.id = j.lesson_plan_id
            JOIN lms_activities a ON a.lesson_plan_id = lp.id
            JOIN lms_submissions s ON s.activity_id = a.id AND s.student_id = :student_id
            WHERE j.iep_step_id = :step_id
            ORDER BY s.submitted_at DESC
        ");
        $stmt->execute(['step_id' => $stepId, 'student_id' => $studentId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $out = [];
        foreach ($rows as $row) {
            $parts = [];
            if ($row['lesson_plan_title'] ?? '') {
                $parts[] = 'LP: ' . $row['lesson_plan_title'];
            }
            if ($row['activity_title'] ?? '') {
                $parts[] = $row['activity_title'];
            }
            $score = $row['auto_score'];
            $max   = $row['max_score'];
            $scoreLine = '';
            if ($score !== null && $score !== '') {
                $scoreLine = 'Score: ' . $score . ($max !== null && $max !== '' ? ' / ' . $max : '');
            }
            $out[] = [
                'submitted_at' => $row['submitted_at'] ?? '',
                'status'       => 'Submitted',
                'notes'        => trim(implode(' — ', array_filter([implode(' · ', $parts), $scoreLine]))),
            ];
        }
        return $out;
    }

    /**
     * Get linked parent for a student
     */
    public function getLinkedParent($studentId) {
        $stmt = $this->db->prepare("
            SELECT u.id, u.name, u.email, u.role
            FROM users u
            JOIN enrollment_submissions es ON u.id = es.parent_id
            JOIN student_records sr ON es.id = sr.enrollment_id
            WHERE sr.id = :student_id
            AND u.role = 'parent'
            LIMIT 1
        ");
        $stmt->execute(['student_id' => $studentId]);
        return $stmt->fetch();
    }

    // ============================================================
    // DOCUMENT COPIES & NOTIFICATIONS
    // ============================================================

    /**
     * Record that a copy was sent to a user
     */
    public function recordCopy($iepId, $userId) {
        $stmt = $this->db->prepare("
            INSERT IGNORE INTO iep_copies (iep_id, sent_to, sent_at)
            VALUES (:iep_id, :user_id, NOW())
        ");
        return $stmt->execute(['iep_id' => $iepId, 'user_id' => $userId]);
    }

    /**
     * Mark copy as viewed by user
     */
    public function markCopyViewed($iepId, $userId) {
        $stmt = $this->db->prepare("
            UPDATE iep_copies 
            SET viewed_at = NOW() 
            WHERE iep_id = :iep_id AND sent_to = :user_id AND viewed_at IS NULL
        ");
        return $stmt->execute(['iep_id' => $iepId, 'user_id' => $userId]);
    }
}