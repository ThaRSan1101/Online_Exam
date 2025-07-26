<?php
require '../includes/session.php';
require '../config/db.php';

// Handle exam submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['exam_id'])) {
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
    exit();
}

// Normal exam display
$exam_id = $_GET['id'];
$questions = $conn->query("SELECT * FROM questions WHERE exam_id=$exam_id");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Exam</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../css/exam.css">
</head>
<body class="d-flex flex-column min-vh-100">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top mb-4">
        <div class="container-fluid" style="display: flex; align-items: center; justify-content: space-between; gap: 16px;">
            <a class="navbar-brand d-flex align-items-center" href="#" style="margin-right: 20px;">
                <img src="../images/logo.png" alt="Logo" style="height:40px; margin-right:10px;">
            </a>
            <ul class="navbar-nav d-flex flex-row align-items-center" style="gap: 10px; margin-bottom: 0;">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">Dashboard</a>
                </li>
            </ul>
            <a class="nav-link" href="../logout.php" style="margin-left:auto; color:#fff;">Logout</a>
        </div>
    </nav>
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
    <script>
        function toggleNavbar() {
            const navbar = document.getElementById('navbarNav');
            navbar.classList.toggle('show');
            
            // Toggle aria-expanded attribute
            const toggler = document.querySelector('.navbar-toggler');
            const isExpanded = toggler.getAttribute('aria-expanded') === 'true';
            toggler.setAttribute('aria-expanded', !isExpanded);
        }
    </script>
</body>
</html>