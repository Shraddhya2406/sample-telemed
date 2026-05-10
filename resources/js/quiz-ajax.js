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
            // optional store URL so JS can link to the pharmacy without Blade
            this.storeUrl = container.dataset.storeUrl || '';
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
                console.error('Diagnosis start error:', error);
                this.showError('Failed to start diagnosis. Please refresh and try again.');
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
                <label class="group flex cursor-pointer items-center rounded-lg border border-slate-200 bg-white p-4 transition hover:border-blue-300 hover:bg-blue-50 dark:border-slate-700 dark:bg-slate-900 dark:hover:bg-slate-800">
                    <input 
                        type="radio" 
                        name="health_option_id" 
                        value="${option.id}" 
                        class="h-4 w-4 text-blue-600"
                        onchange="QuizManager.enableNextButton()"
                    >
                    <span class="ml-3 text-base font-semibold text-slate-700 group-hover:text-blue-800 dark:text-slate-200 dark:group-hover:text-blue-200">${option.text}</span>
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
                console.error('Finish diagnosis error:', error);
                this.showLoading(false);
                this.showError('Failed to finish diagnosis. Please try again.');
            });
    },

    renderResult(data) {
        const resultContainer = document.getElementById('quiz-result');
        const dashboardUrl = document.getElementById('quiz-container')?.dataset.dashboardUrl || '/dashboard/patient';
        const appointmentUrl = document.getElementById('quiz-container')?.dataset.appointmentUrl || '/patient/appointments/create';
        // resolve store URL from data attribute or build it from apiBase
        const storeUrl = document.getElementById('quiz-container')?.dataset.storeUrl || this.buildUrl('/patient/medicines');
        
        const resultHTML = `
            <div class="text-center">
                <div class="mb-8">
                    <svg class="mx-auto mb-4 h-16 w-16 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <h2 class="mb-3 text-3xl font-bold text-slate-950 dark:text-white">Assessment Complete</h2>
                    <p class="mx-auto max-w-xl text-sm leading-6 text-slate-600 dark:text-slate-300">Here is a simple recommendation based on your answers. A doctor can confirm what is right for you.</p>
                </div>

                <div class="mb-6 rounded-lg border border-blue-100 bg-blue-50 p-6 text-left dark:border-blue-900 dark:bg-blue-950/40">

                    <div>
                        <h3 class="mb-4 text-xl font-bold text-slate-950 dark:text-white">Care Recommendation</h3>
                        
                        <div class="mb-4 rounded-lg bg-white p-4 dark:bg-slate-900">
                            <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Possible concern</p>
                            <p class="mt-1 text-lg font-bold text-slate-950 dark:text-white">${this.escapeHtml(data.recommendation?.disease_name || 'General wellness support')}</p>
                        </div>

                        <div class="mb-4 rounded-lg bg-white p-4 dark:bg-slate-900">
                            <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Suggested medicine</p>
                            <p class="mt-1 text-lg font-bold text-slate-950 dark:text-white">${this.escapeHtml(data.recommendation?.medicine_name || 'Please consult a doctor')}</p>
                        </div>

                        <div class="rounded-lg bg-white p-4 dark:bg-slate-900">
                            <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">What to do next</p>
                            <p class="mt-1 leading-6 text-slate-700 dark:text-slate-200">${this.escapeHtml(data.recommendation?.advice || 'Rest, stay hydrated, and consult a doctor if symptoms continue.')}</p>
                        </div>
                    </div>
                </div>

                <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 p-4 text-left dark:border-amber-900 dark:bg-amber-950/40">
                    <p class="text-sm leading-6 text-amber-800 dark:text-amber-200">
                        Important: This is not a medical diagnosis. Please consult a doctor for proper treatment.
                    </p>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:justify-center">
                    <a href="${storeUrl}" class="rounded-lg bg-emerald-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-600/20 transition hover:bg-emerald-700">
                        Buy Medicines
                    </a>
                    <a href="${appointmentUrl}" class="rounded-lg bg-blue-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700">
                        Book Doctor
                    </a>
                    <button onclick="location.reload()" class="rounded-lg border border-slate-200 bg-white px-6 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                        Retake Quiz
                    </button>
                    <a href="${dashboardUrl}" class="rounded-lg px-6 py-3 text-sm font-bold text-slate-600 transition hover:text-blue-700 dark:text-slate-300">Dashboard</a>
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
                        loadingText.textContent = 'Starting diagnosis...';
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
