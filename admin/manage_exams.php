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

// Get questions for an exam (AJAX)

// Get results for an exam (AJAX)
if (isset($_GET['get_results'])) {
    $exam_id = intval($_GET['get_results']);
    $stmt = $conn->prepare("SELECT u.name, r.score, r.created_at FROM results r JOIN users u ON r.user_id = u.id WHERE r.exam_id = ? ORDER BY u.name");
    $stmt->bind_param("i", $exam_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        echo "<table class='results-table'><thead><tr><th>Student Name</th><th>Score</th><th>Examination Date</th></tr></thead><tbody>";
        while ($row = $result->fetch_assoc()) {
            $date = date('Y-m-d H:i', strtotime($row['created_at']));
            echo "<tr><td>" . htmlspecialchars($row['name']) . "</td><td>" . htmlspecialchars($row['score']) . "</td><td>" . htmlspecialchars($date) . "</td></tr>";
        }
        echo "</tbody></table>";
    } else {
        echo "<div class='text-center text-muted'>No results found for this exam.</div>";
    }
    exit;
}

if (isset($_GET['get_questions'])) {
    $exam_id = $_GET['get_questions'];
    $questions = $conn->query("SELECT * FROM questions WHERE exam_id=$exam_id ORDER BY id ASC");

    echo "<div data-exam-id='$exam_id' class='questions-list'>";
    if ($questions->num_rows > 0) {
        while ($q = $questions->fetch_assoc()) {
            echo "<div class='question-item card mb-3' id='question-" . $q['id'] . "'>";
            echo "<div class='card-body'>";
            echo "<div class='d-flex justify-content-between align-items-start'>";
            echo "<h5 class='card-title'>" . htmlspecialchars($q['question_text']) . "</h5>";
            echo "<div class='question-actions'>";
            echo "<button class='btn btn-primary btn-sm me-2' onclick='editQuestion(" . $q['id'] . ")'>Edit</button>";
            echo "<button class='btn btn-danger btn-sm' onclick='removeQuestion(" . $q['id'] . ")'>Remove</button>";
            echo "</div>";
            echo "</div>";
            echo "<div class='options-list'>";
            for ($i = 1; $i <= 4; $i++) {
                $optionClass = ($q['correct_option'] == $i) ? 'correct-option' : '';
                echo "<div class='option-item $optionClass'>";
                echo "<span class='option-number'>Option $i:</span> " . htmlspecialchars($q["option$i"]) . "";
                echo "</div>";
            }
            echo "</div>";
            echo "</div>";

            // Hidden edit form (initially not displayed)
            echo "<div class='edit-form' id='edit-form-" . $q['id'] . "' style='display:none;'>";
            echo "<form class='p-3'>";
            echo "<input type='hidden' name='question_id' value='" . $q['id'] . "'>";
            echo "<div class='mb-3'>";
            echo "<label class='form-label'>Question</label>";
            echo "<textarea class='form-control' name='question_text' required>" . htmlspecialchars($q['question_text']) . "</textarea>";
            echo "</div>";

            for ($i = 1; $i <= 4; $i++) {
                echo "<div class='mb-3'>";
                echo "<label class='form-label'>Option $i</label>";
                echo "<input type='text' class='form-control' name='option$i' value='" . htmlspecialchars($q["option$i"]) . "' required>";
                echo "</div>";
            }

            echo "<div class='mb-3'>";
            echo "<label class='form-label'>Correct Option (1-4)</label>";
            echo "<select class='form-control' name='correct_option' required>";
            for ($i = 1; $i <= 4; $i++) {
                $selected = ($q['correct_option'] == $i) ? 'selected' : '';
                echo "<option value='$i' $selected>$i</option>";
            }
            echo "</select>";
            echo "</div>";

            echo "<div class='d-flex gap-2'>";
            echo "<button type='button' class='btn btn-success btn-sm' onclick='updateQuestion(" . $q['id'] . ")'>
                    Save Changes
                  </button>";
            echo "<button type='button' class='btn btn-secondary btn-sm' onclick='cancelEdit(" . $q['id'] . ")'>
                    Cancel
                  </button>";
            echo "</div>";
            echo "</form>";
            echo "</div>";
            echo "</div>";
        }
    } else {
        echo "<p class='no-questions'>No questions found for this exam.</p>";
    }
    echo "</div>";
    exit;
}

// Remove a question (AJAX)
if (isset($_GET['remove_question'])) {
    $question_id = $_GET['remove_question'];
    $conn->query("DELETE FROM questions WHERE id=$question_id");
    echo "success";
    exit;
}

// Update a question (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_question'])) {
    $question_id = $_POST['question_id'];
    $question_text = $_POST['question_text'];
    $option1 = $_POST['option1'];
    $option2 = $_POST['option2'];
    $option3 = $_POST['option3'];
    $option4 = $_POST['option4'];
    $correct_option = $_POST['correct_option'];

    $stmt = $conn->prepare("UPDATE questions SET question_text=?, option1=?, option2=?, option3=?, option4=?, correct_option=? WHERE id=?");
    $stmt->bind_param("sssssii", $question_text, $option1, $option2, $option3, $option4, $correct_option, $question_id);
    $stmt->execute();

    // Return the updated question data for display
    $result = $conn->query("SELECT * FROM questions WHERE id=$question_id");
    $question = $result->fetch_assoc();

    // Format the response as HTML for the updated question display
    ob_start();
    echo "<div class='d-flex justify-content-between align-items-start'>";
    echo "<h5 class='card-title'>" . htmlspecialchars($question['question_text']) . "</h5>";
    echo "<div class='question-actions'>";
    echo "<button class='btn btn-primary btn-sm me-2' onclick='editQuestion(" . $question_id . ")'>
Edit</button>";
    echo "<button class='btn btn-danger btn-sm' onclick='removeQuestion(" . $question_id . ")'>
Remove</button>";
    echo "</div>";
    echo "</div>";
    echo "<div class='options-list'>";
    for ($i = 1; $i <= 4; $i++) {
        $optionClass = ($question['correct_option'] == $i) ? 'correct-option' : '';
        echo "<div class='option-item $optionClass'>";
        echo "<span class='option-number'>Option $i:</span> " . htmlspecialchars($question["option$i"]) . "";
        echo "</div>";
    }
    echo "</div>";
    $html = ob_get_clean();

    echo json_encode(['success' => true, 'html' => $html]);
    exit;
}

// Mark exam as finished (AJAX)
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['finish_exam']) &&
    isset($_POST['exam_id'])
) {
    $exam_id = intval($_POST['exam_id']);
    $stmt = $conn->prepare("UPDATE exams SET status='finished' WHERE id=?");
    $stmt->bind_param("i", $exam_id);
    $success = $stmt->execute();
    echo json_encode(['success' => $success]);
    exit;
}

// Get all exams
$exams = $conn->query("SELECT * FROM exams");
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
            <a class="nav-link" href="../logout.php" style="margin-left:auto; color:#fff;">Logout</a>
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
                        <a href="?delete=<?= $exam['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
                        <button type="button" class="btn btn-info btn-sm" onclick="openEditQuestionsModal(<?= $exam['id'] ?>)">View
                            Questions</button>
                        <button type="button" class="btn btn-success btn-sm" onclick="openResultsModal(<?= $exam['id'] ?>)">View Result</button>
                    </form>
                </div>
                <div class="card-body">
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
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <!-- Results Modal -->
    <div id="resultsModal" class="modal" role="dialog" aria-modal="true" aria-labelledby="resultsModalTitle">
        <div class="modal-content" tabindex="-1">
            <div class="modal-header">
                <span style="font-size:1.5rem; margin-right:0.75rem; vertical-align:middle;">&#128202;</span>
                <h3 id="resultsModalTitle" style="flex:1; margin:0; font-size:1.25rem; font-weight:600; letter-spacing:0.01em;">Exam Results</h3>
                <span class="close" onclick="closeResultsModal()" aria-label="Close">&times;</span>
            </div>
            <div class="modal-body">
                <div id="resultsContainer" style="min-height:120px;">
                    <div class="text-center text-muted">Loading...</div>
                </div>
                <div class="text-center mt-4">
                    <button type="button" class="btn btn-primary" style="margin-top: 1.5rem; min-width: 120px;" onclick="closeResultsModal()">Close Form</button>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white text-center py-3 mt-auto">
        &copy; <?php echo date('Y'); ?> Online Exam System. All rights reserved.
    </footer>

    <!-- Edit Questions Modal -->
    <div id="editQuestionsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>View Exam Questions</h3>
                <span class="close" onclick="closeEditQuestionsModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div id="questionsContainer"></div>
                <div class="text-end">
                    <button class="btn btn-success mt-3" onclick="finishExamStatus()">Finished</button>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/admin.js"></script>
</body>

</html>