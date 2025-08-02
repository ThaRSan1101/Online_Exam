<?php
require '../includes/session.php';
require '../config/db.php';


$user_id = $_SESSION['user_id'];
// Fetch all exams with student's attempt info
$sql = "
    SELECT
        e.id AS exam_id,
        e.title,
        e.subject,
        (SELECT COUNT(*) FROM results r WHERE r.exam_id = e.id AND r.user_id = ?) AS attempted
    FROM exams e
    WHERE e.status = 'finished'
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>


<!DOCTYPE html>
<html>

<head>
    <title>Student Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>

<body class="d-flex flex-column min-vh-100">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top mb-4">
        <div class="container-fluid" style="display: flex; align-items: center; justify-content: space-between; gap: 16px;">
            <a class="navbar-brand d-flex align-items-center" href="#" style="margin-right: 20px;">
                <img src="../images/logo.png" alt="Logo" style="height:40px; margin-right:10px;">
            </a>
            <ul class="navbar-nav d-flex flex-row align-items-center" style="gap: 10px; margin-bottom: 0;">
                <li class="nav-item">
                    <a class="nav-link active" href="dashboard.php">Dashboard</a>
                </li>

            </ul>
            <a class="nav-link" href="../logout.php" style="margin-left:auto; color:#fff;">Logout</a>
        </div>
    </nav>
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
                        <?php while ($exam = $result->fetch_assoc()): ?>
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
                                        // Fetch score for this user and exam
                                        $score_sql = "SELECT score FROM results WHERE exam_id = ? AND user_id = ? LIMIT 1";
                                        $score_stmt = $conn->prepare($score_sql);
                                        $score_stmt->bind_param("ii", $exam['exam_id'], $user_id);
                                        $score_stmt->execute();
                                        $score_result = $score_stmt->get_result();
                                        if ($score_row = $score_result->fetch_assoc()) {
                                            // Fetch total questions for this exam
                                            $total_sql = "SELECT COUNT(*) as total FROM questions WHERE exam_id = ?";
                                            $total_stmt = $conn->prepare($total_sql);
                                            $total_stmt->bind_param("i", $exam['exam_id']);
                                            $total_stmt->execute();
                                            $total_result = $total_stmt->get_result();
                                            $total_row = $total_result->fetch_assoc();
                                            $total_questions = $total_row ? $total_row['total'] : 0;
                                            echo '<span class="badge bg-success" style="color:#000; background-color:#c3e6cb;">' . $score_row['score'] . ' / ' . $total_questions . '</span>';
                                        }
                                    } else {
                                        echo '<span class="badge bg-light" style="color:#000;">Not Attempted</span>';
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <footer class="bg-dark text-white text-center py-3 mt-auto">
        &copy; <?php echo date('Y'); ?> Online Exam System. All rights reserved.
    </footer>
    <script src="../js/dashboard.js"></script>
</body>

</html>