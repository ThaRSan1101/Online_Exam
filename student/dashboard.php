<?php
require '../includes/auth.php';
require '../config/db.php';

$result = $conn->query("SELECT * FROM exams");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/sidebar.css" rel="stylesheet">
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>Online Exam</h3>
        </div>
        <ul class="sidebar-menu">
            <li class="active"><a href="dashboard.php">Dashboard</a></li>
            <li><a href="results.php">My Results</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <h2>Available Exams</h2>
        <div class="card">
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr><th>Exam Title</th><th>Subject</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php while ($exam = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $exam['title'] ?></td>
                            <td><?= $exam['subject'] ?></td>
                            <td><a href="exam.php?id=<?= $exam['id'] ?>" class="btn btn-primary btn-sm">Start</a></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>