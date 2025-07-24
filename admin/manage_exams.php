<?php
require '../includes/auth.php';
require '../config/db.php';
if ($_SESSION['role'] !== 'admin') exit("Access denied");

// Add new exam
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_exam'])) {
    $title = $_POST['title'];
    $subject = $_POST['subject'];
    $created_by = $_SESSION['user_id'];

    $stmt = $conn->prepare("INSERT INTO exams(title, subject, created_by) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $title, $subject, $created_by);
    $stmt->execute();
}

// Add new question
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_question'])) {
    $exam_id = $_POST['exam_id'];
    $question_text = $_POST['question_text'];
    $option1 = $_POST['option1'];
    $option2 = $_POST['option2'];
    $option3 = $_POST['option3'];
    $option4 = $_POST['option4'];
    $correct_option = $_POST['correct_option'];

    $stmt = $conn->prepare("INSERT INTO questions (exam_id, question_text, option1, option2, option3, option4, correct_option) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssssi", $exam_id, $question_text, $option1, $option2, $option3, $option4, $correct_option);
    $stmt->execute();
}

// Delete exam
if (isset($_GET['delete'])) {
    $conn->query("DELETE FROM exams WHERE id=" . $_GET['delete']);
    header("Location: manage_exams.php");
    exit;
}

// Get all exams
$exams = $conn->query("SELECT * FROM exams");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Exams</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/sidebar.css" rel="stylesheet">
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>Admin Panel</h3>
        </div>
        <ul class="sidebar-menu">
            <li class="active"><a href="manage_exams.php">Manage Exams</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <h2>Exam Management</h2>

        <!-- Add Exam -->
        <form method="POST" class="card p-3 mb-4">
            <input type="hidden" name="add_exam" value="1">
            <div class="row">
                <div class="col-md-5">
                    <input type="text" name="title" class="form-control" placeholder="Exam Title" required>
                </div>
                <div class="col-md-5">
                    <input type="text" name="subject" class="form-control" placeholder="Subject" required>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Add Exam</button>
                </div>
            </div>
        </form>

        <!-- Display Exams and Question Forms -->
        <?php while ($exam = $exams->fetch_assoc()): ?>
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between">
                <strong><?= $exam['title'] ?> - <?= $exam['subject'] ?></strong>
                <a href="?delete=<?= $exam['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
            </div>
            <div class="card-body">
                <!-- Add Question Form -->
                <form method="POST">
                    <input type="hidden" name="add_question" value="1">
                    <input type="hidden" name="exam_id" value="<?= $exam['id'] ?>">
                    <div class="mb-2">
                        <label>Question</label>
                        <textarea name="question_text" class="form-control" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <input type="text" name="option1" class="form-control" placeholder="Option 1" required>
                        </div>
                        <div class="col-md-6 mb-2">
                            <input type="text" name="option2" class="form-control" placeholder="Option 2" required>
                        </div>
                        <div class="col-md-6 mb-2">
                            <input type="text" name="option3" class="form-control" placeholder="Option 3" required>
                        </div>
                        <div class="col-md-6 mb-2">
                            <input type="text" name="option4" class="form-control" placeholder="Option 4" required>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label>Correct Option (1-4)</label>
                        <input type="number" name="correct_option" min="1" max="4" class="form-control" required>
                    </div>
                    <button class="btn btn-success btn-sm">Add Question</button>
                </form>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</body>
</html>
