<?php
class Exam {
    private $conn;
    public $id;
    public $title;
    public $subject;
    public $created_by;
    public $status;
    public $visibility;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Create a new exam
    public function create($title, $subject, $created_by) {
        $stmt = $this->conn->prepare("INSERT INTO exams(title, subject, created_by) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $title, $subject, $created_by);
        return $stmt->execute();
    }

    // Update exam details
    public function update($id, $title, $subject) {
        $stmt = $this->conn->prepare("UPDATE exams SET title=?, subject=? WHERE id=?");
        $stmt->bind_param("ssi", $title, $subject, $id);
        return $stmt->execute();
    }

    // Set exam visibility
    public function setVisibility($id, $visibility) {
        $stmt = $this->conn->prepare("UPDATE exams SET visibility=? WHERE id=?");
        $stmt->bind_param("si", $visibility, $id);
        return $stmt->execute();
    }

    // Delete an exam
    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM exams WHERE id=?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    // Mark exam as finished
    public function finish($id) {
        $stmt = $this->conn->prepare("UPDATE exams SET status='finished' WHERE id=?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    // Get all exams
    public function getAll() {
        $result = $this->conn->query("SELECT * FROM exams");
        $exams = [];
        while ($row = $result->fetch_assoc()) {
            $exams[] = $row;
        }
        return $exams;
    }

    // Get exam by ID
    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM exams WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // Get exam title by ID
    public function getTitleById($exam_id) {
        $stmt = $this->conn->prepare("SELECT title FROM exams WHERE id = ?");
        $stmt->bind_param("i", $exam_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $row['title'];
        }
        return null;
    }

    // Get exam results with total questions
    public function getResultsWithTotalQuestions($exam_id) {
        $stmt = $this->conn->prepare("
            SELECT u.name, r.score, r.created_at, e.title as exam_title,
            (SELECT COUNT(*) FROM questions q WHERE q.exam_id = e.id) as total_questions
            FROM results r 
            JOIN users u ON r.user_id = u.id 
            JOIN exams e ON r.exam_id = e.id 
            WHERE r.exam_id = ? 
            ORDER BY u.name
        ");
        $stmt->bind_param("i", $exam_id);
        $stmt->execute();
        return $stmt->get_result();
    }
    
    // Get all available exams with attempt info for a student
    public function getAvailableExamsWithAttempt($user_id) {
        $sql = "
            SELECT
                e.id AS exam_id,
                e.title,
                e.subject,
                (SELECT COUNT(*) FROM results r WHERE r.exam_id = e.id AND r.user_id = ?) AS attempted
            FROM exams e
            WHERE e.status='finished'
            AND e.visibility = 'visible'
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $exams = [];
        while ($row = $result->fetch_assoc()) {
            $exams[] = $row;
        }
        return $exams;
    }

    // Get student's score for an exam
    public function getStudentExamScore($exam_id, $user_id) {
        $stmt = $this->conn->prepare("SELECT score FROM results WHERE exam_id = ? AND user_id = ? LIMIT 1");
        $stmt->bind_param("ii", $exam_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $row['score'];
        }
        return null;
    }

    // Get total number of questions for an exam
    public function getTotalQuestions($exam_id) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM questions WHERE exam_id = ?");
        $stmt->bind_param("i", $exam_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $row['total'];
        }
        return 0;
    }

    // Check if student has already attempted the exam
    public function hasStudentAttempted($exam_id, $user_id) {
        $stmt = $this->conn->prepare("SELECT id FROM results WHERE user_id = ? AND exam_id = ? LIMIT 1");
        $stmt->bind_param("ii", $user_id, $exam_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result && $result->num_rows > 0;
    }

    // Insert a new exam result for a student
    public function insertResult($user_id, $exam_id, $score) {
        $stmt = $this->conn->prepare("INSERT INTO results(user_id, exam_id, score) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $user_id, $exam_id, $score);
        return $stmt->execute();
    }
}
?>
