// Import axios for AJAX requests
import axios from 'axios';

// Make axios available globally
window.axios = axios;

// Set default headers
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Quiz AJAX Logic
const QuizManager = {
    quizAttemptId: null,
    currentQuestionOrder: 0,
    totalQuestions: 10,
    answers: {}, // Store answers: { questionId: optionId }
    apiBase: '',
    buildUrl(path) {
        // Ensure apiBase is set and join safely to avoid double slashes
        const base = this.apiBase || '';
        if (!base) return path;
        return base.replace(/\/$/, '') + '/' + path.replace(/^\//, '');
    },
    
    init() {
        // Set up Axios CSRF token
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (token) {
            window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
        }
        
        // Read API base URL from blade data attribute (handles subdirectory/public)
        const container = document.getElementById('quiz-container');
        if (container) {
            this.apiBase = container.dataset.apiBase || '';
        }
        
        // Attach event listeners
        this.attachEventListeners();
        
        // Start quiz
        this.createQuizAttempt();
    },

    attachEventListeners() {
        // Use event delegation for buttons that may not exist yet
        document.addEventListener('click', (e) => {
            if (e.target.id === 'next-button' || e.target.closest('#next-button')) {
                e.preventDefault();
                this.submitAnswer();
            }
            if (e.target.id === 'prev-button' || e.target.closest('#prev-button')) {
                e.preventDefault();
                this.goToPreviousQuestion();
            }
        });
    },

    createQuizAttempt() {
        axios.post(this.buildUrl('/patient/health-quiz/start'))
            .then(response => {
                this.quizAttemptId = response.data.quiz_attempt_id;
                const attemptIdInput = document.getElementById('quiz-attempt-id');
                if (attemptIdInput) {
                    attemptIdInput.value = this.quizAttemptId;
                }
                this.loadQuestion(1);
            })
            .catch(error => {
                console.error('Quiz start error:', error);
                this.showError('Failed to start quiz. Please refresh and try again.');
            });
    },

    loadQuestion(order) {
        this.showLoading(true);
        this.showContent(false);
        
        axios.get(this.buildUrl(`/patient/health-quiz/question/${order}`))
            .then(response => {
                const data = response.data;
                this.currentQuestionOrder = order;
                this.totalQuestions = data.total || 10;
                this.renderQuestion(data);
                this.showLoading(false);
                this.showContent(true);
            })
            .catch(error => {
                console.error('Load question error:', error);
                this.showLoading(false);
                this.showError('Failed to load question. Please try again.');
            });
    },

    renderQuestion(data) {
        const question = data.question;
        const options = data.options;
        const current = data.current;
        const total = data.total;

        // Update progress
        document.getElementById('current-question').textContent = current;
        document.getElementById('total-questions').textContent = total;
        const progressPercentage = Math.round((current / total) * 100);
        document.getElementById('progress-bar').style.width = progressPercentage + '%';
        document.getElementById('progress-percentage').textContent = progressPercentage + '%';

        // Update question text
        document.getElementById('question-text').textContent = question;
        document.getElementById('question-id').value = data.question_id;

        // Render options
        const optionsContainer = document.getElementById('options-container');
        optionsContainer.innerHTML = '';

        options.forEach(option => {
            const optionHTML = `
                <label class="flex items-center p-3 border border-gray-200 rounded cursor-pointer hover:bg-blue-50 transition">
                    <input 
                        type="radio" 
                        name="health_option_id" 
                        value="${option.id}" 
                        class="form-radio text-blue-600 w-4 h-4"
                        onchange="QuizManager.enableNextButton()"
                    >
                    <span class="ml-3 text-gray-700">${option.text}</span>
                </label>
            `;
            optionsContainer.innerHTML += optionHTML;
        });

        // Restore previous answer if exists
        if (this.answers[data.question_id]) {
            document.querySelector(`input[name="health_option_id"][value="${this.answers[data.question_id]}"]`).checked = true;
            this.enableNextButton();
        } else {
            this.disableNextButton();
        }

        // Update button states
        if (current === 1) {
            document.getElementById('prev-button').disabled = true;
        } else {
            document.getElementById('prev-button').disabled = false;
        }

        if (current === total) {
            document.getElementById('next-button').textContent = 'Finish';
        } else {
            document.getElementById('next-button').textContent = 'Next';
        }

        // Clear error message
        document.getElementById('error-message').classList.add('hidden');
    },

    submitAnswer() {
        const selectedOption = document.querySelector('input[name="health_option_id"]:checked');
        
        if (!selectedOption) {
            this.showErrorMessage('Please select an option before proceeding.');
            return false;
        }

        const questionId = document.getElementById('question-id').value;
        const optionId = selectedOption.value;

        // Store answer locally
        this.answers[questionId] = optionId;

        // Disable buttons during submission
        this.disableNextButton();
        this.showLoading(true);

        // Submit answer to backend
        axios.post(this.buildUrl('/patient/health-quiz/answer'), {
            quiz_attempt_id: this.quizAttemptId,
            health_question_id: questionId,
            health_option_id: optionId
        })
        .then(response => {
            const isLastQuestion = this.currentQuestionOrder === this.totalQuestions;

            if (isLastQuestion) {
                this.finishQuiz();
            } else {
                this.loadQuestion(this.currentQuestionOrder + 1);
            }
        })
        .catch(error => {
            console.error('Submit answer error:', error);
            this.showLoading(false);
            this.enableNextButton();
            this.showErrorMessage('Failed to submit answer. Please try again.');
        });
    },

    goToPreviousQuestion() {
        if (this.currentQuestionOrder > 1) {
            // Save current answer if one is selected (but don't submit to backend)
            const selectedOption = document.querySelector('input[name="health_option_id"]:checked');
            if (selectedOption) {
                const questionId = document.getElementById('question-id').value;
                this.answers[questionId] = selectedOption.value;
            }
            // Load previous question
            this.loadQuestion(this.currentQuestionOrder - 1);
        }
    },

    finishQuiz() {
        this.showLoading(true);
        this.showContent(false);

        axios.post(this.buildUrl('/patient/health-quiz/finish'), {
            quiz_attempt_id: this.quizAttemptId
        })
            .then(response => {
                this.showLoading(false);
                this.renderResult(response.data);
            })
            .catch(error => {
                console.error('Finish quiz error:', error);
                this.showLoading(false);
                this.showError('Failed to finish quiz. Please try again.');
            });
    },

    renderResult(data) {
        const resultContainer = document.getElementById('quiz-result');
        const dashboardUrl = document.getElementById('quiz-container')?.dataset.dashboardUrl || '/dashboard/patient';
        
        const resultHTML = `
            <div class="text-center">
                <div class="mb-8">
                    <svg class="w-16 h-16 mx-auto text-green-600 mb-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <h2 class="text-3xl font-bold text-gray-800 mb-4">Quiz Complete!</h2>
                </div>

                <div class="bg-blue-50 p-6 rounded mb-6">

                    <div class="text-left">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">Recommendation</h3>
                        
                        <div class="mb-4">
                            <p class="text-gray-600 text-sm">Disease Category</p>
                            <p class="text-lg font-semibold text-gray-800">${this.escapeHtml(data.recommendation?.disease_name || 'N/A')}</p>
                        </div>

                        <div class="mb-4">
                            <p class="text-gray-600 text-sm">Recommended Medicine</p>
                            <p class="text-lg font-semibold text-gray-800">${this.escapeHtml(data.recommendation?.medicine_name || 'N/A')}</p>
                        </div>

                        <div>
                            <p class="text-gray-600 text-sm">Advice</p>
                            <p class="text-gray-800">${this.escapeHtml(data.recommendation?.advice || 'N/A')}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-yellow-100 border-l-4 border-yellow-500 p-4 rounded mb-6">
                    <p class="text-yellow-800 text-sm">
                        <strong>⚠️ Important:</strong> This is not a medical diagnosis. Please consult a doctor for proper treatment.
                    </p>
                </div>

                <div class="flex gap-4 justify-center">
                    <a href="${dashboardUrl}" class="bg-gray-600 text-white px-6 py-2 rounded hover:bg-gray-700">
                        Go to Dashboard
                    </a>
                    <button onclick="location.reload()" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                        Retake Quiz
                    </button>
                </div>
            </div>
        `;

        resultContainer.innerHTML = resultHTML;
        resultContainer.classList.remove('hidden');
    },

    enableNextButton() {
        document.getElementById('next-button').disabled = false;
    },

    disableNextButton() {
        document.getElementById('next-button').disabled = true;
    },

    showErrorMessage(message) {
        const errorEl = document.getElementById('error-message');
        errorEl.textContent = message;
        errorEl.classList.remove('hidden');
    },

    showLoading(show) {
        const loadingEl = document.getElementById('quiz-loading');
        if (loadingEl) {
            if (show) {
                loadingEl.classList.remove('hidden');
                // Update loading message based on context
                const loadingText = loadingEl.querySelector('p');
                if (loadingText) {
                    if (this.currentQuestionOrder === 0) {
                        loadingText.textContent = 'Starting quiz...';
                    } else {
                        loadingText.textContent = 'Loading question...';
                    }
                }
            } else {
                loadingEl.classList.add('hidden');
            }
        }
    },

    showContent(show) {
        document.getElementById('quiz-content').classList.toggle('hidden', !show);
    },

    showError(message) {
        document.getElementById('quiz-error').classList.remove('hidden');
        const errorP = document.querySelector('#quiz-error p');
        if (errorP) {
            errorP.textContent = message;
        }
        this.showLoading(false);
        this.showContent(false);
    },

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
};

// Initialize quiz when page loads
function initializeQuiz() {
    // Wait for DOM to be fully ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => QuizManager.init());
    } else {
        QuizManager.init();
    }
}

// Make QuizManager and initializeQuiz available globally
window.QuizManager = QuizManager;
window.initializeQuiz = initializeQuiz;

// Auto-initialize if DOM is already ready
if (document.readyState !== 'loading') {
    initializeQuiz();
} else {
    document.addEventListener('DOMContentLoaded', initializeQuiz);
}