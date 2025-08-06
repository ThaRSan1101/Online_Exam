<?php
require '../includes/session.php';
require '../config/db.php';
require_once '../classes/Exam.php';
if ($_SESSION['role'] !== 'admin') exit("Access denied");

$examObj = new Exam($conn);

// Add new exam 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_exam'])) {
    $title = $_POST['title'];
    $subject = $_POST['subject'];
    $created_by = $_SESSION['user_id'];
    // Create a new exam
$examObj->create($title, $subject, $created_by);
}

// Edit exam 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_exam'])) {
    $exam_id = $_POST['exam_id'];
    $new_title = $_POST['new_title'];
    $new_subject = $_POST['new_subject'];
    // Update exam details using the update method
    $examObj->update($exam_id, $new_title, $new_subject);
    header("Location: manage_exams.php");
    exit;
}

// Delete exam 
if (isset($_GET['delete'])) {
    // Delete an exam using the delete method
    $examObj->delete((int)$_GET['delete']);
    header("Location: manage_exams.php");
    exit;
}

// Hide exam 
if (isset($_GET['hide_exam'])) {
    // Hide an exam using the setVisibility method
    $examObj->setVisibility((int)$_GET['hide_exam'], 'hide');
    header("Location: manage_exams.php");
    exit;
}

// Show exam 
if (isset($_GET['show_exam'])) {
    // Show an exam using the setVisibility method
    $examObj->setVisibility((int)$_GET['show_exam'], 'visible');
    header("Location: manage_exams.php");
    exit;
}

// Finish exam 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['finish_exam'])) {
    $exam_id = $_POST['exam_id'];
    // Mark exam as finished using the finish method
    $examObj->finish($exam_id);
    header("Location: manage_exams.php");
    exit;
}

// Get all exams 
$exams = $examObj->getAll();

require_once '../classes/Question.php';
$questionObj = new Question($conn);
// Add new question 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_question'])) {
    $exam_id = $_POST['exam_id'];
    $question_text = $_POST['question_text'];
    $option1 = $_POST['option1'];
    $option2 = $_POST['option2'];
    $option3 = $_POST['option3'];
    $option4 = $_POST['option4'];
    $correct_option = $_POST['correct_option'];
    // Create a new question using the create method
    $questionObj->create($exam_id, $question_text, $option1, $option2, $option3, $option4, $correct_option);
    // Redirect to prevent form resubmission on refresh
    header("Location: manage_exams.php");
    exit;
}

// Get exam results if requested (for full page view) 
$exam_results = null;
$selected_exam_id = null;
$exam_title = null;
if (isset($_GET['view_results'])) {
    $selected_exam_id = intval($_GET['view_results']);
    // Get exam title by ID using the getTitleById method
    $exam_title = $examObj->getTitleById($selected_exam_id); // new method in Exam.php
    // Get exam results with total questions using the getResultsWithTotalQuestions method
    $exam_results = $examObj->getResultsWithTotalQuestions($selected_exam_id); // new method in Exam.php
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Manage Exams</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../css/admin.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../css/footer.css?v=<?= time() ?>">
</head>

<body class="d-flex flex-column min-vh-100">
    <?php include '../components/admin_navbar.php'; ?>

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
        <?php foreach ($exams as $exam): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <!-- Edit Exam Title/Subject -->
                    <form method="POST" class="d-flex flex-wrap align-items-center gap-2">
                        <input type="hidden" name="edit_exam" value="1">
                        <input type="hidden" name="exam_id" value="<?= $exam['id'] ?>">
                        <input type="text" name="new_title" value="<?= htmlspecialchars($exam['title']) ?>" class="form-control w-25" required>
                        <input type="text" name="new_subject" value="<?= htmlspecialchars($exam['subject']) ?>" class="form-control w-25" required>
                        <button class="btn btn-warning btn-sm">Update</button>
                        <a href="?delete=<?= $exam['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this exam?')">Delete</a>
                        <a href="?view_results=<?= $exam['id'] ?>" class="btn btn-success btn-sm">View Results</a>
                        <?php if ($exam['visibility'] === 'visible'): ?>
                            <a href="?hide_exam=<?= $exam['id'] ?>" class="btn btn-secondary btn-sm">Hide</a>
                        <?php else: ?>
                            <a href="?show_exam=<?= $exam['id'] ?>" class="btn btn-primary btn-sm">Show</a>
                        <?php endif; ?>
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
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex gap-2 align-items-center">
                                <button class="btn btn-success btn-sm">Add Question</button>
                                
                                <!-- Finish Exam Button right next to Add Question -->
                                <?php if (isset($exam['status']) && $exam['status'] === 'finished'): ?>
                                    <span class="status-finished">Finished</span>
                                <?php else: ?>
                                    <button type="button" class="btn btn-warning btn-sm finish-btn" onclick="submitFinishForm(<?= $exam['id'] ?>)">
                                        Finish
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>

                    <!-- Hidden Finish Form (separate from Add Question form) -->
                    <form id="finishForm<?= $exam['id'] ?>" method="POST" style="display: none;">
                        <input type="hidden" name="finish_exam" value="1">
                        <input type="hidden" name="exam_id" value="<?= $exam['id'] ?>">
                    </form>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Display Results if requested -->
        <?php if ($exam_results !== null): ?>
            <div class="card mt-4">
                <div class="card-header results-header">
                    <h4 class="results-title">Exam Results: <?= htmlspecialchars($exam_title ?? 'Unknown Exam') ?></h4>
                    <a href="manage_exams.php" class="back-btn">
                        Back to Exam Management
                    </a>
                </div>
                <div class="card-body">
                    <?php if ($exam_results->num_rows > 0): ?>
                        <div class="results-table-container">
                            <table class="exam-results-table">
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
                                            <td><span class="score-badge"><?= htmlspecialchars($row['score']) ?>/<?= htmlspecialchars($row['total_questions']) ?></span></td>
                                            <td><?= date('Y-m-d H:i', strtotime($row['created_at'])) ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="no-results">
                            <h5>No results found for this exam.</h5>
                            <p>Students haven't taken this exam yet.</p>
                        </div>
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

    function submitFinishForm(examId) {
        if (confirm('Are you sure you want to finish this exam? This action cannot be undone.')) {
            document.getElementById('finishForm' + examId).submit();
        }
    }
</script>

</html>
