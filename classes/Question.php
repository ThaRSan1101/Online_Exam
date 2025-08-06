<?php
class Question {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Create a new question
    public function create($exam_id, $question_text, $option1, $option2, $option3, $option4, $correct_option) {
        $stmt = $this->conn->prepare("INSERT INTO questions (exam_id, question_text, option1, option2, option3, option4, correct_option) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssi", $exam_id, $question_text, $option1, $option2, $option3, $option4, $correct_option);
        return $stmt->execute();
    }

    // public function update($id, $question_text, $option1, $option2, $option3, $option4, $correct_option) {
    //     $stmt = $this->conn->prepare("UPDATE questions SET question_text=?, option1=?, option2=?, option3=?, option4=?, correct_option=? WHERE id=?");
    //     $stmt->bind_param("ssssssi", $question_text, $option1, $option2, $option3, $option4, $correct_option, $id);
    //     return $stmt->execute();
    // }

    // public function delete($id) {
    //     $stmt = $this->conn->prepare("DELETE FROM questions WHERE id=?");
    //     $stmt->bind_param("i", $id);
    //     return $stmt->execute();
    // }

    // Get all questions for a specific exam
    public function getByExam($exam_id) {
        $stmt = $this->conn->prepare("SELECT * FROM questions WHERE exam_id=?");
        $stmt->bind_param("i", $exam_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $questions = [];
        while ($row = $result->fetch_assoc()) {
            $questions[] = $row;
        }
        return $questions;
    }

    // Get question by ID
    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM questions WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
}
?>
