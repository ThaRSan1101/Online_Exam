<?php
require '../includes/auth.php';
require '../config/db.php';
if ($_SESSION['role'] !== 'admin') exit("Access denied");

$title = $_POST['title'];
$subject = $_POST['subject'];
$created_by = $_SESSION['user_id'];

$stmt = $conn->prepare("INSERT INTO exams(title, subject, created_by) VALUES (?, ?, ?)");
$stmt->bind_param("ssi", $title, $subject, $created_by);
$stmt->execute();
header("Location: manage_exams.php");
?>