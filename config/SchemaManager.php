<?php
// DO NOT ALTER WITHOUT APPROVAL — Security Module 1
// Last modified: 2026-05-01
// Part of: SPED LMS — Schema Migration Manager

require_once __DIR__ . '/db.php';

class SchemaManager {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function applyMigrations() {
        try {
            // Read schema.sql
            $schemaPath = __DIR__ . '/schema.sql';
            if (!file_exists($schemaPath)) {
                throw new Exception("schema.sql not found");
            }

            $sql = file_get_contents($schemaPath);

            // Strip UTF-8 BOM if present (prevents MySQL syntax error)
            if (substr($sql, 0, 3) === "\xEF\xBB\xBF") {
                $sql = substr($sql, 3);
            }

            // Extract and apply only migrations newer than current version
            $currentVersion = $this->getCurrentVersion();

            // Find all migration blocks: -- MIGRATION: vN ... -- END MIGRATION: vN
            preg_match_all(
                '/--\s*MIGRATION:\s*v(\d+).*?--\s*END MIGRATION:\s*v\1/s',
                $sql,
                $matches,
                PREG_SET_ORDER
            );

            if (empty($matches)) {
                // Fallback: run entire schema (idempotent with IF NOT EXISTS)
                $this->db->exec($sql);
                return true;
            }

            foreach ($matches as $match) {
                $version = (int)$match[1];
                if ($version > $currentVersion) {
                    $statements = array_filter(array_map('trim', explode(';', $match[0])));
                    foreach ($statements as $stmt_sql) {
                        if (!empty($stmt_sql) && !preg_match('/^\s*--/', $stmt_sql)) {
                            try {
                                $this->db->exec($stmt_sql);
                            } catch (PDOException $e) {
                                error_log("Migration v{$version} statement warning: " . $e->getMessage());
                            }
                        }
                    }
                    $this->markVersionApplied($version);
                    error_log("SchemaManager: applied migration v{$version}");
                }
            }

            return true;
        } catch (PDOException $e) {
            error_log("Schema migration failed: " . $e->getMessage());
            return false;
        }
    }

    public function getCurrentVersion() {
        try {
            $stmt = $this->db->query("SELECT MAX(version) as version FROM db_version");
            $result = $stmt->fetch();
            return $result['version'] ?? 0;
        } catch (PDOException $e) {
            return 0;
        }
    }

    public function markVersionApplied($version) {
        try {
            $stmt = $this->db->prepare("INSERT IGNORE INTO db_version (version) VALUES (:version)");
            $stmt->execute(['version' => $version]);
            return true;
        } catch (PDOException $e) {
            error_log("Failed to mark version: " . $e->getMessage());
            return false;
        }
    }
}
