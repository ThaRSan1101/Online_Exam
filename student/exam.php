<?php
require '../includes/auth.php';
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/sidebar.css" rel="stylesheet">
</head>

<body class="d-flex flex-column min-vh-100">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <img src="../logo.png" alt="Logo" style="height:48px;vertical-align:middle;margin-right:12px;">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="results.php">Results</a>
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
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-success btn-lg">Submit Exam</button>
            </div>
        </form>
    </div>
    <footer class="bg-dark text-white text-center py-3 mt-auto">
    &copy; <?php echo date('Y'); ?> Online Exam System. All rights reserved.
</footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>