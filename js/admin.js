// Admin exam management JavaScript functions

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
        const resultsModal = document.getElementById('resultsModal');
        
        if (event.target === resultsModal) {
            closeResultsModal();
        }
    };
});
