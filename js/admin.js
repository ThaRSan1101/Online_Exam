// Admin exam management JavaScript functions

function toggleNavbar() {
    const navbar = document.getElementById('navbarNav');
    navbar.classList.toggle('show');
    
    // Toggle aria-expanded attribute
    const toggler = document.querySelector('.navbar-toggler');
    const isExpanded = toggler.getAttribute('aria-expanded') === 'true';
    toggler.setAttribute('aria-expanded', !isExpanded);
}

function confirmDelete() {
    return confirm('Are you sure you want to delete this exam?');
}

// Modal functions
let currentExamId = null;

function openEditQuestionsModal(examId) {
    document.body.style.overflow = 'hidden'; // Prevent background scroll
    currentExamId = examId;
    document.getElementById('editQuestionsModal').style.display = 'block';
    loadExamQuestions(examId);
}

function closeEditQuestionsModal() {
    document.body.style.overflow = ''; // Restore background scroll
    document.getElementById('editQuestionsModal').style.display = 'none';
    currentExamId = null;
}

function loadExamQuestions(examId) {
    // AJAX request to get questions
    const xhr = new XMLHttpRequest();
    xhr.open('GET', `manage_exams.php?get_questions=${examId}`, true);
    xhr.onload = function() {
        if (this.status === 200) {
            document.getElementById('questionsContainer').innerHTML = this.responseText;
        }
    };
    xhr.send();
}

function finishExamStatus() {
    if (!currentExamId) {
        alert('Exam ID not found.');
        return;
    }
    if (!confirm('Are you sure you want to mark this exam as finished?')) return;
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'manage_exams.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (this.status === 200) {
            try {
                const resp = JSON.parse(this.responseText);
                if (resp.success) {
                    alert('Exam marked as finished!');
                    closeEditQuestionsModal();
                    // Optionally reload page or update UI
                    location.reload();
                } else {
                    alert('Failed to update exam status.');
                }
            } catch (e) {
                alert('Unexpected server response.');
            }
        } else {
            alert('Server error.');
        }
    };
    xhr.send('finish_exam=1&exam_id=' + encodeURIComponent(currentExamId));
}

function editQuestion(questionId) {
    // Hide the question display and show the edit form
    document.querySelector(`#question-${questionId} .card-body`).style.display = 'none';
    document.querySelector(`#edit-form-${questionId}`).style.display = 'block';
}

function cancelEdit(questionId) {
    // Hide the edit form and show the question display
    document.querySelector(`#question-${questionId} .card-body`).style.display = 'block';
    document.querySelector(`#edit-form-${questionId}`).style.display = 'none';
}

function updateQuestion(questionId) {
    const form = document.querySelector(`#edit-form-${questionId} form`);
    const formData = new FormData();
    
    // Add form fields to FormData
    formData.append('update_question', '1');
    formData.append('question_id', questionId);
    formData.append('question_text', form.querySelector('[name="question_text"]').value);
    formData.append('option1', form.querySelector('[name="option1"]').value);
    formData.append('option2', form.querySelector('[name="option2"]').value);
    formData.append('option3', form.querySelector('[name="option3"]').value);
    formData.append('option4', form.querySelector('[name="option4"]').value);
    formData.append('correct_option', form.querySelector('[name="correct_option"]').value);
    
    // Send AJAX request
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'manage_exams.php', true);
    xhr.onload = function() {
        if (this.status === 200) {
            try {
                const response = JSON.parse(this.responseText);
                if (response.success) {
                    // Update the question display with the new content
                    const cardBody = document.querySelector(`#question-${questionId} .card-body`);
                    cardBody.innerHTML = response.html;
                    // Hide the edit form and show the updated question
                    cardBody.style.display = 'block';
                    document.querySelector(`#edit-form-${questionId}`).style.display = 'none';
                }
            } catch (e) {
                console.error('Error parsing JSON response:', e);
            }
        }
    };
    xhr.send(formData);
}

function removeQuestion(questionId) {
    if (confirm('Are you sure you want to remove this question?')) {
        const xhr = new XMLHttpRequest();
        xhr.open('GET', `manage_exams.php?remove_question=${questionId}`, true);
        xhr.onload = function() {
            if (this.status === 200) {
                // Remove just this question from the DOM
                const questionElement = document.querySelector(`#question-${questionId}`);
                if (questionElement) {
                    questionElement.remove();
                    
                    // Check if there are any questions left
                    const questionsContainer = document.querySelector('#questionsContainer .questions-list');
                    if (questionsContainer && !questionsContainer.querySelector('.question-item')) {
                        questionsContainer.innerHTML = "<p class='no-questions'>No questions found for this exam.</p>";
                    }
                }
            }
        };
        xhr.send();
    }
}

// Results Modal functions
function openResultsModal(examId) {
    document.body.style.overflow = 'hidden';
    document.getElementById('resultsModal').style.display = 'block';
    loadExamResults(examId);
}

function closeResultsModal() {
    document.body.style.overflow = '';
    document.getElementById('resultsModal').style.display = 'none';
    document.getElementById('resultsContainer').innerHTML = '<div class="text-center text-muted">Loading...</div>';
}

function loadExamResults(examId) {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', `manage_exams.php?get_results=${examId}`, true);
    xhr.onload = function() {
        if (this.status === 200) {
            document.getElementById('resultsContainer').innerHTML = this.responseText;
        } else {
            document.getElementById('resultsContainer').innerHTML = '<div class="text-danger">Failed to load results.</div>';
        }
    };
    xhr.send();
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Close modal when clicking outside of it
    window.onclick = function(event) {
        const editModal = document.getElementById('editQuestionsModal');
        const resultsModal = document.getElementById('resultsModal');
        
        if (event.target === editModal) {
            closeEditQuestionsModal();
        }
        if (event.target === resultsModal) {
            closeResultsModal();
        }
    };
});
