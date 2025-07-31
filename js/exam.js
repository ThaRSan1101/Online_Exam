// Exam-specific JavaScript functions

// Initialize exam functionality when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeExam();
});

function initializeExam() {
    // Add confirmation before leaving the page
    window.addEventListener('beforeunload', function(e) {
        const examForm = document.querySelector('form[method="POST"]');
        if (examForm) {
            e.preventDefault();
            e.returnValue = 'Are you sure you want to leave? Your exam progress will be lost.';
            return e.returnValue;
        }
    });

    // Add submission confirmation
    const submitButton = document.querySelector('button[type="submit"]');
    if (submitButton) {
        submitButton.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to submit your exam? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    }

    // Auto-save functionality (optional - stores answers in localStorage)
    setupAutoSave();
}

function setupAutoSave() {
    const examForm = document.querySelector('form[method="POST"]');
    if (!examForm) return;

    const examId = examForm.querySelector('input[name="exam_id"]').value;
    const storageKey = `exam_${examId}_answers`;

    // Load saved answers
    loadSavedAnswers(storageKey);

    // Save answers when changed
    const radioInputs = examForm.querySelectorAll('input[type="radio"]');
    radioInputs.forEach(input => {
        input.addEventListener('change', function() {
            saveAnswers(storageKey);
        });
    });
}

function loadSavedAnswers(storageKey) {
    try {
        const savedAnswers = localStorage.getItem(storageKey);
        if (savedAnswers) {
            const answers = JSON.parse(savedAnswers);
            Object.keys(answers).forEach(questionId => {
                const radio = document.querySelector(`input[name="answer[${questionId}]"][value="${answers[questionId]}"]`);
                if (radio) {
                    radio.checked = true;
                }
            });
        }
    } catch (e) {
        console.error('Error loading saved answers:', e);
    }
}

function saveAnswers(storageKey) {
    try {
        const examForm = document.querySelector('form[method="POST"]');
        const formData = new FormData(examForm);
        const answers = {};
        
        for (let [key, value] of formData.entries()) {
            if (key.startsWith('answer[')) {
                const questionId = key.match(/answer\[(\d+)\]/)[1];
                answers[questionId] = value;
            }
        }
        
        localStorage.setItem(storageKey, JSON.stringify(answers));
    } catch (e) {
        console.error('Error saving answers:', e);
    }
}

// Clear saved answers after successful submission
function clearSavedAnswers() {
    const examId = document.querySelector('input[name="exam_id"]').value;
    const storageKey = `exam_${examId}_answers`;
    localStorage.removeItem(storageKey);
}

// Optional timer functionality
function startExamTimer(duration) {
    let timer = duration;
    const display = document.getElementById('timer');
    
    const interval = setInterval(function() {
        const minutes = parseInt(timer / 60, 10);
        const seconds = parseInt(timer % 60, 10);
        
        const displayMinutes = minutes < 10 ? "0" + minutes : minutes;
        const displaySeconds = seconds < 10 ? "0" + seconds : seconds;
        
        if (display) {
            display.textContent = displayMinutes + ":" + displaySeconds;
        }
        
        if (--timer < 0) {
            clearInterval(interval);
            // Auto-submit when time is up
            const examForm = document.querySelector('form[method="POST"]');
            if (examForm) {
                alert('Time is up! Your exam will be submitted automatically.');
                examForm.submit();
            }
        }
    }, 1000);
}

// Function to highlight unanswered questions
function highlightUnansweredQuestions() {
    const questions = document.querySelectorAll('.card');
    let unanswered = [];
    
    questions.forEach((card, index) => {
        const radios = card.querySelectorAll('input[type="radio"]');
        const isAnswered = Array.from(radios).some(radio => radio.checked);
        
        if (!isAnswered) {
            card.style.borderLeft = '4px solid #dc3545';
            unanswered.push(index + 1);
        } else {
            card.style.borderLeft = '4px solid #28a745';
        }
    });
    
    return unanswered;
}

// Function to show exam progress
function showExamProgress() {
    const totalQuestions = document.querySelectorAll('.card').length;
    const answeredQuestions = document.querySelectorAll('input[type="radio"]:checked').length;
    const progress = (answeredQuestions / totalQuestions) * 100;
    
    const progressBar = document.getElementById('examProgress');
    if (progressBar) {
        progressBar.style.width = progress + '%';
        progressBar.textContent = `${answeredQuestions}/${totalQuestions} answered`;
    }
    
    return { total: totalQuestions, answered: answeredQuestions, progress: progress };
}
