<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 7
// Last modified: 2026-05-13
// Part of: SPED LMS — Gamification Model (Learner Points, Badges, Stars)

require_once __DIR__ . '/../../config/db.php';

class GamificationModel {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // ============================================================
    // XP POINTS
    // ============================================================

    /**
     * Get total XP for a student (sum of all learner_points rows)
     */
    public function getTotalXP(int $studentId): int {
        try {
            $stmt = $this->db->prepare("
                SELECT COALESCE(SUM(points), 0)
                FROM learner_points
                WHERE student_id = :student_id
            ");
            $stmt->execute(['student_id' => $studentId]);
            return (int) $stmt->fetchColumn();
        } catch (\Throwable $e) {
            error_log('GamificationModel::getTotalXP() error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get full XP ledger for a student (newest first)
     */
    public function getXPLedger(int $studentId): array {
        try {
            $stmt = $this->db->prepare("
                SELECT id, points, reason, source_type, source_id, earned_at
                FROM learner_points
                WHERE student_id = :student_id
                ORDER BY earned_at DESC
            ");
            $stmt->execute(['student_id' => $studentId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log('GamificationModel::getXPLedger() error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Award XP to a student — inserts a new row in learner_points.
     * source_id is optional (activity id, lesson id, badge id — or null).
     */
    public function awardXP(int $studentId, int $points, string $reason, string $sourceType, ?int $sourceId = null): bool {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO learner_points
                    (student_id, points, reason, source_type, source_id, earned_at)
                VALUES
                    (:student_id, :points, :reason, :source_type, :source_id, NOW())
            ");
            return $stmt->execute([
                'student_id'  => $studentId,
                'points'      => $points,
                'reason'      => $reason,
                'source_type' => $sourceType,
                'source_id'   => $sourceId,
            ]);
        } catch (\Throwable $e) {
            error_log('GamificationModel::awardXP() error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if XP was already awarded for a specific source (prevents double-award).
     */
    public function xpAlreadyAwarded(int $studentId, string $sourceType, int $sourceId): bool {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM learner_points
                WHERE student_id = :student_id
                  AND source_type = :source_type
                  AND source_id   = :source_id
            ");
            $stmt->execute([
                'student_id'  => $studentId,
                'source_type' => $sourceType,
                'source_id'   => $sourceId,
            ]);
            return (int) $stmt->fetchColumn() > 0;
        } catch (\Throwable $e) {
            error_log('GamificationModel::xpAlreadyAwarded() error: ' . $e->getMessage());
            return false;
        }
    }

    // ============================================================
    // BADGES
    // ============================================================

    /**
     * Fixed badge definitions (system-defined, teacher cannot create custom badges)
     */
    public static function badgeDefinitions(): array {
        return [
            'first_activity'  => ['icon' => 'ti-trophy',   'name' => 'First activity!'],
            'lesson_complete' => ['icon' => 'ti-medal',    'name' => 'Lesson done!'],
            'perfect_score'   => ['icon' => 'ti-star',     'name' => 'Perfect score!'],
            'five_in_a_row'   => ['icon' => 'ti-flame',    'name' => '5 in a row!'],
            'all_done'        => ['icon' => 'ti-award',    'name' => 'All done!'],
            'star_collector'  => ['icon' => 'ti-sparkles', 'name' => 'Star collector!'],
        ];
    }

    /**
     * Get all earned badge keys for a student (as simple array of strings)
     */
    public function getEarnedBadgeKeys(int $studentId): array {
        try {
            $stmt = $this->db->prepare("
                SELECT badge_key FROM learner_badges
                WHERE student_id = :student_id
                ORDER BY earned_at ASC
            ");
            $stmt->execute(['student_id' => $studentId]);
            return $stmt->fetchAll(\PDO::FETCH_COLUMN);
        } catch (\Throwable $e) {
            error_log('GamificationModel::getEarnedBadgeKeys() error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all badges merged with earned status — sorted: earned first, then locked.
     * Returns array of [ 'key', 'icon', 'name', 'earned', 'earned_at' ]
     */
    public function getBadgesWithStatus(int $studentId): array {
        $earnedKeys = $this->getEarnedBadgeKeys($studentId);

        // Get earned_at dates
        $dates = [];
        try {
            $stmt = $this->db->prepare("
                SELECT badge_key, earned_at FROM learner_badges
                WHERE student_id = :student_id
            ");
            $stmt->execute(['student_id' => $studentId]);
            foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                $dates[$row['badge_key']] = $row['earned_at'];
            }
        } catch (\Throwable $e) {
            error_log('GamificationModel::getBadgesWithStatus() error: ' . $e->getMessage());
        }

        $earned = [];
        $locked = [];
        foreach (self::badgeDefinitions() as $key => $def) {
            $isEarned = in_array($key, $earnedKeys, true);
            $badge    = [
                'key'       => $key,
                'icon'      => $def['icon'],
                'name'      => $def['name'],
                'earned'    => $isEarned,
                'earned_at' => $dates[$key] ?? null,
            ];
            if ($isEarned) {
                $earned[] = $badge;
            } else {
                $locked[] = $badge;
            }
        }

        return array_merge($earned, $locked);
    }

    /**
     * Award a badge if not already earned.
     * Returns true if newly awarded, false if already existed or error.
     */
    public function awardBadge(int $studentId, string $badgeKey): bool {
        try {
            $stmt = $this->db->prepare("
                INSERT IGNORE INTO learner_badges (student_id, badge_key, earned_at)
                VALUES (:student_id, :badge_key, NOW())
            ");
            $stmt->execute(['student_id' => $studentId, 'badge_key' => $badgeKey]);
            return $stmt->rowCount() > 0;
        } catch (\Throwable $e) {
            error_log('GamificationModel::awardBadge() error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if a specific badge is already earned
     */
    public function badgeEarned(int $studentId, string $badgeKey): bool {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM learner_badges
                WHERE student_id = :student_id AND badge_key = :badge_key
            ");
            $stmt->execute(['student_id' => $studentId, 'badge_key' => $badgeKey]);
            return (int) $stmt->fetchColumn() > 0;
        } catch (\Throwable $e) {
            return false;
        }
    }

    // ============================================================
    // STARS
    // ============================================================

    /**
     * Get total stars earned by a student (sum across all activity_stars rows)
     */
    public function getTotalStars(int $studentId): int {
        try {
            $stmt = $this->db->prepare("
                SELECT COALESCE(SUM(stars), 0)
                FROM activity_stars
                WHERE student_id = :student_id
            ");
            $stmt->execute(['student_id' => $studentId]);
            return (int) $stmt->fetchColumn();
        } catch (\Throwable $e) {
            error_log('GamificationModel::getTotalStars() error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get stars for a specific submission (returns 0 if none)
     */
    public function getStarsForSubmission(int $submissionId): int {
        try {
            $stmt = $this->db->prepare("
                SELECT stars FROM activity_stars WHERE submission_id = :submission_id LIMIT 1
            ");
            $stmt->execute(['submission_id' => $submissionId]);
            return (int) $stmt->fetchColumn();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * Calculate and save stars for a submission based on score percentage.
     * Rule (from process-7.md):
     *   90–100% → 3 stars | 70–89% → 2 stars | <70% → 1 star
     *   View-only (max_score = 0 or null) → do not insert
     *
     * Uses INSERT … ON DUPLICATE KEY UPDATE (idempotent — safe to re-run on re-grade).
     */
    public function saveStarsForSubmission(int $submissionId, int $studentId, ?int $score, ?int $maxScore): ?int {
        if (!$maxScore || $maxScore <= 0) return null; // view-only — no stars

        $pct = ($score / $maxScore) * 100;
        if ($pct >= 90)     $stars = 3;
        elseif ($pct >= 70) $stars = 2;
        else                $stars = 1;

        try {
            $stmt = $this->db->prepare("
                INSERT INTO activity_stars (submission_id, student_id, stars, calculated_at)
                VALUES (:submission_id, :student_id, :stars, NOW())
                ON DUPLICATE KEY UPDATE stars = VALUES(stars), calculated_at = NOW()
            ");
            $stmt->execute([
                'submission_id' => $submissionId,
                'student_id'    => $studentId,
                'stars'         => $stars,
            ]);
            return $stars;
        } catch (\Throwable $e) {
            error_log('GamificationModel::saveStarsForSubmission() error: ' . $e->getMessage());
            return null;
        }
    }

    // ============================================================
    // DASHBOARD SUMMARY (single call for sidebar/topbar)
    // ============================================================

    /**
     * Returns [ 'total_xp' => N, 'total_stars' => N ] for sidebar/topbar pills.
     */
    public function getSummary(int $studentId): array {
        return [
            'total_xp'    => $this->getTotalXP($studentId),
            'total_stars' => $this->getTotalStars($studentId),
        ];
    }
}
