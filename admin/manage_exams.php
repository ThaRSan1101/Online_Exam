<?php
require '../includes/session.php';
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

// Edit exam
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_exam'])) {
    $exam_id = $_POST['exam_id'];
    $new_title = $_POST['new_title'];
    $new_subject = $_POST['new_subject'];

    $stmt = $conn->prepare("UPDATE exams SET title=?, subject=? WHERE id=?");
    $stmt->bind_param("ssi", $new_title, $new_subject, $exam_id);
    $stmt->execute();
    header("Location: manage_exams.php");
    exit;
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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body class="d-flex flex-column min-vh-100">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <img src="../images/logo.png" alt="Logo" style="height:48px;vertical-align:middle;margin-right:12px;">
            </a>
            <button class="navbar-toggler" type="button" onclick="toggleNavbar()" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active" href="manage_exams.php">Manage Exams</a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <div class="container">
        <h2 class="mb-4">Exam Management</h2>
        
        <!-- Add Exam Form -->
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
                <div class="card-header">
                    <!-- Edit Exam Title/Subject -->
                    <form method="POST" class="d-flex flex-wrap align-items-center gap-2">
                        <input type="hidden" name="edit_exam" value="1">
                        <input type="hidden" name="exam_id" value="<?= $exam['id'] ?>">
                        <input type="text" name="new_title" value="<?= htmlspecialchars($exam['title']) ?>" class="form-control w-25" required>
                        <input type="text" name="new_subject" value="<?= htmlspecialchars($exam['subject']) ?>" class="form-control w-25" required>
                        <button class="btn btn-warning btn-sm">Update</button>
                        <a href="?delete=<?= $exam['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
                    </form>
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
    
    <footer class="bg-dark text-white text-center py-3 mt-auto">
        &copy; <?php echo date('Y'); ?> Online Exam System. All rights reserved.
    </footer>
    
    <script>
        function toggleNavbar() {
            const navbar = document.getElementById('navbarNav');
            navbar.classList.toggle('show');
            
            // Toggle aria-expanded attribute
            const toggler = document.querySelector('.navbar-toggler');
            const isExpanded = toggler.getAttribute('aria-expanded') === 'true';
            toggler.setAttribute('aria-expanded', !isExpanded);
        }

        function confirmDelete() {
            return confirm('Are you sure you want to delete this exam?');
        }
    </script>
</body>
</html>