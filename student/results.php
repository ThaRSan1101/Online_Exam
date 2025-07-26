<?php
require '../includes/session.php';
require '../config/db.php';

$user_id = $_SESSION['user_id'];
$res = $conn->query("
    SELECT 
        exams.id AS exam_id,
        exams.title, 
        results.score, 
        results.created_at,
        (SELECT COUNT(*) FROM questions WHERE exam_id = exams.id) AS total_questions
    FROM results 
    JOIN exams ON results.exam_id = exams.id 
    WHERE results.user_id = $user_id
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Results</title>
    <link href="../css/results.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar">
        <a class="navbar-brand" href="#">
            <img src="../images/logo.png" alt="Logo">
        </a>
        <button class="navbar-toggler" onclick="toggleNavbar()">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="navbar-collapse">
            <ul class="navbar-nav nav-left">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="results.php">Results</a>
                </li>
            </ul>
            <ul class="navbar-nav nav-right">
                <li class="nav-item">
                    <a class="nav-link" href="../logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <h2 class="mb-4">Your Results</h2>
        <div class="card">
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Exam</th>
                            <th>Score</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $res->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['title']) ?></td>
                                <td><?= $row['score'] . '/' . $row['total_questions'] ?></td>
                                <td><?= $row['created_at'] ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <footer>
        &copy; <?php echo date('Y'); ?> Online Exam System. All rights reserved.
    </footer>

    <script>
        function toggleNavbar() {
            const navbarCollapse = document.querySelector('.navbar-collapse');
            navbarCollapse.classList.toggle('show');
        }
    </script>
</body>
</html>