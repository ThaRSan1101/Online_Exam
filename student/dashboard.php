<?php
require '../includes/session.php';
require '../config/db.php';
require_once '../classes/Exam.php';

$user_id = $_SESSION['user_id'];
$examObj = new Exam($conn);
// Get all available exams with attempt info for the student
$exams = $examObj->getAvailableExamsWithAttempt($user_id);
?>


<!DOCTYPE html>
<html>

<head>
    <title>Student Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/footer.css?v=<?= time() ?>">
</head>

<body class="d-flex flex-column min-vh-100">
    <?php include '../components/student_navbar.php'; ?>
    <div class="container">
        <h2 class="mb-4">Available Exams</h2>
        <div class="card">
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Exam Title</th>
                            <th>Subject</th>
                            <th>Action</th>
                            <th>Results</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($exams as $exam): ?>
                            <tr>
                                <td><?= htmlspecialchars($exam['title']) ?></td>
                                <td><?= htmlspecialchars($exam['subject']) ?></td>
                                <td>
                                    <?php if ($exam['attempted'] > 0): ?>
                                        <span class="badge bg-secondary">Attempted</span>
                                    <?php else: ?>
                                        <a href="exam.php?id=<?= $exam['exam_id'] ?>" class="btn btn-primary btn-sm">Start</a>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    if ($exam['attempted'] > 0) {
                                        // Get the student's score for this exam
$score = $examObj->getStudentExamScore($exam['exam_id'], $user_id);
                                        // Get the total number of questions for this exam
$total_questions = $examObj->getTotalQuestions($exam['exam_id']);
                                        if ($score !== null) {
                                            echo '<span class="badge bg-success" style="color:#000; background-color:#c3e6cb;">' . $score . ' / ' . $total_questions . '</span>';
                                        }
                                    } else {
                                        echo '<span class="badge bg-light" style="color:#000;">Not Attempted</span>';
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
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