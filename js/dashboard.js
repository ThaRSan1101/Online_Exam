// Dashboard-specific JavaScript functions

// Initialize dashboard functionality when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeDashboard();
});

function initializeDashboard() {
    // Add confirmation before starting exams
    setupExamStartConfirmation();
}

// Confirmation before starting exam
function setupExamStartConfirmation() {
    const startButtons = document.querySelectorAll('a.btn-primary[href*="exam.php"]');
    startButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const examTitle = this.closest('tr').cells[0].textContent.trim();
            if (!confirm(`Are you sure you want to start the exam: "${examTitle}"?\n\nOnce started, you cannot pause or restart the exam.`)) {
                e.preventDefault();
            }
        });
    });
}

// Function to show exam details in modal (if needed)
function showExamDetails(examId, examTitle) {
    // This could be extended to show more details about the exam
    alert(`Exam Details:\nID: ${examId}\nTitle: ${examTitle}`);
}

// Utility function for dashboard interactions
function refreshDashboard() {
    window.location.reload();
}
