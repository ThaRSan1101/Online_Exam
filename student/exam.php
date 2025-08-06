<?php
require '../includes/session.php';
require '../config/db.php';
require_once '../classes/Question.php';
require_once '../classes/Exam.php';

$questionObj = new Question($conn);
$examObj = new Exam($conn);

// Handle exam submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['exam_id'])) {
    $exam_id = $_POST['exam_id'];
    $user_id = $_SESSION['user_id'];
    $answers = $_POST['answer'];

    $score = 0;
    foreach ($answers as $qid => $selected) {
        // Get question details by question ID
$question = $questionObj->getById($qid);
        if ($question && $question['correct_option'] == $selected) {
            $score++;
        }
    }

    // Check if result already exists using 
    require_once '../classes/Exam.php';
    $examObj = new Exam($conn);
    if ($examObj->hasStudentAttempted($exam_id, $user_id)) {
        // Result exists, skip insert
        header("Location: dashboard.php");
        exit();
    } else {
        // Insert the student's result for this exam
$examObj->insertResult($user_id, $exam_id, $score);
        header("Location: dashboard.php");
        exit();
    }
}

// Normal exam display
$exam_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Check if user has already attempted this exam 
$already_attempted = $examObj->hasStudentAttempted($exam_id, $user_id);

// Get all questions for this exam
$questions = $questionObj->getByExam($exam_id);
?>
<!DOCTYPE html>
<html>

<head>
    <title>Exam</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../css/exam.css">
    <link rel="stylesheet" href="../css/footer.css?v=<?= time() ?>">
</head>

<body class="d-flex flex-column min-vh-100">
    <?php include '../components/student_navbar.php'; ?>
    <div class="container">
        <h2 class="mb-4">Exam</h2>
        <form method="POST">
            <input type="hidden" name="exam_id" value="<?= $exam_id ?>">
            <?php foreach ($questions as $q): ?>
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title mb-3"><?= htmlspecialchars($q['question_text']) ?></h5>
                        <div class="form-group">
                            <?php for ($i = 1; $i <= 4; $i++): ?>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="answer[<?= $q['id'] ?>]" value="<?= $i ?>" id="q<?= $q['id'] ?>o<?= $i ?>" required>
                                    <label class="form-check-label" for="q<?= $q['id'] ?>o<?= $i ?>">
                                        <?= htmlspecialchars($q["option$i"]) ?>
                                    </label>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <div class="d-grid gap-2 text-center">
                <button type="submit" class="btn btn-success btn-short">Submit</button>
            </div>
        </form>
    </div>
    <footer class="bg-dark text-white text-center py-3 mt-auto">
        &copy; <?php echo date('Y'); ?> Online Exam System. All rights reserved.
    </footer>

    <script src="../js/exam.js?v=<?= time() ?>"></script>
    <?php if ($already_attempted): ?>
        <script>
            // Clear any saved answers if exam was already attempted
            clearSavedAnswers(<?= $exam_id ?>);
        </script>
    <?php endif; ?>
</body>

</html>