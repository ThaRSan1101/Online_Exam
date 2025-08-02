// Exam-specific JavaScript functions

// Initialize exam functionality when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeExam();
});

function initializeExam() {
    // Auto-save functionality (optional - stores answers in localStorage)
    setupAutoSave();
    
    // Clear saved answers on form submission
    const examForm = document.querySelector('form[method="POST"]');
    if (examForm) {
        const examId = examForm.querySelector('input[name="exam_id"]').value;
        const storageKey = `exam_${examId}_answers`;
        
        examForm.addEventListener('submit', function() {
            // Clear saved answers from localStorage when submitting
            localStorage.removeItem(storageKey);
        });
    }
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

// Clear saved answers for a specific exam
function clearSavedAnswers(examId) {
    try {
        const storageKey = `exam_${examId}_answers`;
        localStorage.removeItem(storageKey);
    } catch (e) {
        console.error('Error clearing saved answers:', e);
    }
}
