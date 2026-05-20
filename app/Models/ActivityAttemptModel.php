<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 7
// Last modified: 2026-05-05
// Part of: SPED LMS — Activity Attempt Model

require_once __DIR__ . '/../../config/db.php';

class ActivityAttemptModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create activity attempt
     */
    public function create($activityId, $studentId, $answers, $score, $totalPoints, $timeSpent = 0) {
        try {
            // Get attempt number
            $attemptNumber = $this->getNextAttemptNumber($activityId, $studentId);
            $percentage = $totalPoints > 0 ? ($score / $totalPoints) * 100 : 0;
            
            $stmt = $this->db->prepare("
                INSERT INTO activity_attempts (
                    activity_id, student_id, attempt_number, answers, 
                    score, total_points, percentage, time_spent_minutes, completed
                ) VALUES (
                    :activity_id, :student_id, :attempt_number, :answers,
                    :score, :total_points, :percentage, :time_spent, TRUE
                )
            ");
            
            $stmt->execute([
                'activity_id' => $activityId,
                'student_id' => $studentId,
                'attempt_number' => $attemptNumber,
                'answers' => json_encode($answers),
                'score' => $score,
                'total_points' => $totalPoints,
                'percentage' => $percentage,
                'time_spent' => $timeSpent
            ]);
            
            // Update completed_at
            $attemptId = $this->db->lastInsertId();
            $this->db->prepare("UPDATE activity_attempts SET completed_at = CURRENT_TIMESTAMP WHERE id = :id")
                     ->execute(['id' => $attemptId]);
            
            return $attemptId;
        } catch (PDOException $e) {
            error_log('Failed to create activity attempt: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get next attempt number
     */
    private function getNextAttemptNumber($activityId, $studentId) {
        try {
            $stmt = $this->db->prepare("
                SELECT MAX(attempt_number) as max_attempt
                FROM activity_attempts
                WHERE activity_id = :activity_id AND student_id = :student_id
            ");
            
            $stmt->execute([
                'activity_id' => $activityId,
                'student_id' => $studentId
            ]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return ($result['max_attempt'] ?? 0) + 1;
        } catch (PDOException $e) {
            return 1;
        }
    }

    /**
     * Get attempts by student and activity
     */
    public function getByStudentAndActivity($studentId, $activityId) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM activity_attempts
                WHERE student_id = :student_id AND activity_id = :activity_id
                ORDER BY attempt_number DESC
            ");
            
            $stmt->execute([
                'student_id' => $studentId,
                'activity_id' => $activityId
            ]);
            
            $attempts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($attempts as &$attempt) {
                $attempt['answers'] = json_decode($attempt['answers'], true);
            }
            
            return $attempts;
        } catch (PDOException $e) {
            error_log('Failed to get attempts: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get best attempt by student and activity
     */
    public function getBestAttempt($studentId, $activityId) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM activity_attempts
                WHERE student_id = :student_id AND activity_id = :activity_id
                ORDER BY score DESC, attempt_number DESC
                LIMIT 1
            ");
            
            $stmt->execute([
                'student_id' => $studentId,
                'activity_id' => $activityId
            ]);
            
            $attempt = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($attempt) {
                $attempt['answers'] = json_decode($attempt['answers'], true);
            }
            
            return $attempt;
        } catch (PDOException $e) {
            error_log('Failed to get best attempt: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Calculate score based on activity type
     */
    public function calculateScore($activityTemplate, $answers) {
        $activityType = $activityTemplate['activity_type'];
        $activityData = $activityTemplate['activity_data'];
        $score = 0;
        
        switch ($activityType) {
            case 'multiple_choice':
                foreach ($activityData['questions'] as $i => $question) {
                    if (isset($answers[$i]) && $answers[$i] == $question['correct_answer']) {
                        $score += $question['points'];
                    }
                }
                break;
                
            case 'true_false':
                foreach ($activityData['questions'] as $i => $question) {
                    if (isset($answers[$i]) && $answers[$i] == $question['correct_answer']) {
                        $score += $question['points'];
                    }
                }
                break;
                
            case 'fill_blanks':
                foreach ($activityData['questions'] as $i => $question) {
                    if (isset($answers[$i]) && strtolower(trim($answers[$i])) == strtolower(trim($question['correct_answer']))) {
                        $score += $question['points'];
                    }
                }
                break;
                
            case 'matching':
                foreach ($activityData['pairs'] as $i => $pair) {
                    if (isset($answers[$i]) && $answers[$i] == $i) {
                        $score += 10; // 10 points per correct match
                    }
                }
                break;
                
            case 'drag_drop_sort':
                foreach ($activityData['items'] as $i => $item) {
                    if (isset($answers[$i]) && $answers[$i] == $item['correct_category']) {
                        $score += 10; // 10 points per correct sort
                    }
                }
                break;
                
            case 'sequencing':
                $correctSequence = range(0, count($activityData['items']) - 1);
                if ($answers == $correctSequence) {
                    $score = $activityTemplate['total_points']; // All or nothing
                }
                break;
        }
        
        return $score;
    }

    /**
     * Get all attempts by student
     */
    public function getByStudentId($studentId) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    aa.*,
                    at.activity_type,
                    lm.material_name,
                    lm.material_name as activity_name
                FROM activity_attempts aa
                JOIN activity_templates at ON aa.activity_id = at.id
                JOIN learning_materials lm ON at.material_id = lm.id
                WHERE aa.student_id = :student_id
                ORDER BY aa.completed_at DESC
            ");
            
            $stmt->execute(['student_id' => $studentId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Failed to get attempts by student: ' . $e->getMessage());
            return [];
        }
    }
}
