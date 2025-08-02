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

    // Redirect to prevent form resubmission on refresh
    header("Location: manage_exams.php");
    exit;
}

// Delete exam
if (isset($_GET['delete'])) {
    $conn->query("DELETE FROM exams WHERE id=" . $_GET['delete']);
    header("Location: manage_exams.php");
    exit;
}

// Finish exam
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['finish_exam'])) {
    $exam_id = $_POST['exam_id'];
    $stmt = $conn->prepare("UPDATE exams SET status='finished' WHERE id=?");
    $stmt->bind_param("i", $exam_id);
    $stmt->execute();
    header("Location: manage_exams.php");
    exit;
}



// Get all exams
$exams = $conn->query("SELECT * FROM exams");

// Get exam results if requested
$exam_results = null;
$selected_exam_id = null;
$exam_title = null;
if (isset($_GET['view_results'])) {
    $selected_exam_id = intval($_GET['view_results']);

    // Get exam title
    $exam_stmt = $conn->prepare("SELECT title FROM exams WHERE id = ?");
    $exam_stmt->bind_param("i", $selected_exam_id);
    $exam_stmt->execute();
    $exam_result = $exam_stmt->get_result();
    if ($exam_row = $exam_result->fetch_assoc()) {
        $exam_title = $exam_row['title'];
    }

    // Get results with exam title
    $stmt = $conn->prepare("SELECT u.name, r.score, r.created_at, e.title as exam_title FROM results r JOIN users u ON r.user_id = u.id JOIN exams e ON r.exam_id = e.id WHERE r.exam_id = ? ORDER BY u.name");
    $stmt->bind_param("i", $selected_exam_id);
    $stmt->execute();
    $exam_results = $stmt->get_result();
}
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
        <div class="container-fluid" style="display: flex; align-items: center; justify-content: space-between; gap: 16px;">
            <a class="navbar-brand d-flex align-items-center" href="#" style="margin-right: 20px;">
                <img src="../images/logo.png" alt="Logo" style="height:40px; margin-right:10px;">
            </a>
            <ul class="navbar-nav d-flex flex-row align-items-center" style="gap: 10px; margin-bottom: 0;">
                <li class="nav-item">
                    <a class="nav-link active" href="manage_exams.php">Manage Exams</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="manage_users.php">Manage Users</a>
                </li>
            </ul>
            <a class="nav-link" href="#" id="logout-link" style="margin-left:auto; color:#fff;">Logout</a>
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
                        <a href="?delete=<?= $exam['id'] ?>" class="btn btn-danger btn-sm">Delete</a>
                        <a href="?view_results=<?= $exam['id'] ?>" class="btn btn-success btn-sm">View Result</a>
                    </form>
                </div>
                <div class="card-body position-relative">
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

                    <!-- Finish Exam Button positioned on the right -->
                    <div class="position-absolute" style="bottom: 15px; right: 15px;">
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="finish_exam" value="1">
                            <input type="hidden" name="exam_id" value="<?= $exam['id'] ?>">
                            <button type="submit" class="btn btn-warning btn-sm">Finish</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>

        <!-- Display Results if requested -->
        <?php if ($exam_results !== null): ?>
            <div class="card mt-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 style="margin: 0;">Exam Results: <?= htmlspecialchars($exam_title ?? 'Unknown Exam') ?></h4>
                    <a href="manage_exams.php" class="btn btn-secondary btn-sm">Back to Exam Management</a>
                </div>
                <div class="card-body">
                    <?php if ($exam_results->num_rows > 0): ?>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Exam Title</th>
                                    <th>Student Name</th>
                                    <th>Score</th>
                                    <th>Examination Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $exam_results->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['exam_title']) ?></td>
                                        <td><?= htmlspecialchars($row['name']) ?></td>
                                        <td><?= htmlspecialchars($row['score']) ?></td>
                                        <td><?= date('Y-m-d H:i', strtotime($row['created_at'])) ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="text-center text-muted">No results found for this exam.</div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <footer class="bg-dark text-white text-center py-3 mt-auto">
        &copy; <?php echo date('Y'); ?> Online Exam System. All rights reserved.
    </footer>
</body>

<script>
    document.getElementById('logout-link').addEventListener('click', function(e) {
        e.preventDefault();
        alert('You have been logged out!');
        window.location.href = '../logout.php';
    });
</script>

</html>