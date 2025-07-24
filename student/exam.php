<?php
require '../includes/auth.php';
require '../config/db.php';

$exam_id = $_GET['id'];
$questions = $conn->query("SELECT * FROM questions WHERE exam_id=$exam_id");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Exam</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/sidebar.css" rel="stylesheet">
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>Online Exam</h3>
        </div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="results.php">My Results</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <h2>Exam</h2>
        <form action="submit_exam.php" method="POST">
            <input type="hidden" name="exam_id" value="<?= $exam_id ?>">
            <?php foreach ($questions as $q): ?>
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><?= $q['question_text'] ?></h5>
                        <div class="form-group">
                            <?php for ($i = 1; $i <= 4; $i++): ?>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="answer[<?= $q['id'] ?>]" value="<?= $i ?>" id="q<?= $q['id'] ?>o<?= $i ?>" required>
                                    <label class="form-check-label" for="q<?= $q['id'] ?>o<?= $i ?>">
                                        <?= $q["option$i"] ?>
                                    </label>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <button type="submit" class="btn btn-success btn-lg">Submit Exam</button>
        </form>
    </div>
</body>
</html>