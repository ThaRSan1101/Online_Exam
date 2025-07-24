<?php
require '../includes/auth.php';
require '../config/db.php';

$exam_id = $_POST['exam_id'];
$user_id = $_SESSION['user_id'];
$answers = $_POST['answer'];

$score = 0;
foreach ($answers as $qid => $selected) {
    $res = $conn->query("SELECT correct_option FROM questions WHERE id=$qid");
    $correct = $res->fetch_assoc()['correct_option'];
    if ($correct == $selected) {
        $score++;
    }
}

$conn->query("INSERT INTO results(user_id, exam_id, score) VALUES($user_id, $exam_id, $score)");
header("Location: results.php");
?>