import{a as o}from"./index-B9ygI19o.js";window.axios=o;window.axios.defaults.headers.common["X-Requested-With"]="XMLHttpRequest";const r={quizAttemptId:null,currentQuestionOrder:0,totalQuestions:10,answers:{},init(){const t=document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");t&&(window.axios.defaults.headers.common["X-CSRF-TOKEN"]=t),this.attachEventListeners(),this.createQuizAttempt()},attachEventListeners(){document.addEventListener("click",t=>{(t.target.id==="next-button"||t.target.closest("#next-button"))&&(t.preventDefault(),this.submitAnswer()),(t.target.id==="prev-button"||t.target.closest("#prev-button"))&&(t.preventDefault(),this.goToPreviousQuestion())})},createQuizAttempt(){o.post("/patient/health-quiz/start").then(t=>{this.quizAttemptId=t.data.quiz_attempt_id;const e=document.getElementById("quiz-attempt-id");e&&(e.value=this.quizAttemptId),this.loadQuestion(1)}).catch(t=>{console.error("Quiz start error:",t),this.showError("Failed to start quiz. Please refresh and try again.")})},loadQuestion(t){this.showLoading(!0),this.showContent(!1),o.get(`/patient/health-quiz/question/${t}`).then(e=>{const n=e.data;this.currentQuestionOrder=t,this.totalQuestions=n.total||10,this.renderQuestion(n),this.showLoading(!1),this.showContent(!0)}).catch(e=>{console.error("Load question error:",e),this.showLoading(!1),this.showError("Failed to load question. Please try again.")})},renderQuestion(t){const e=t.question,n=t.options,s=t.current,i=t.total;document.getElementById("current-question").textContent=s,document.getElementById("total-questions").textContent=i;const d=Math.round(s/i*100);document.getElementById("progress-bar").style.width=d+"%",document.getElementById("progress-percentage").textContent=d+"%",document.getElementById("question-text").textContent=e,document.getElementById("question-id").value=t.question_id;const l=document.getElementById("options-container");l.innerHTML="",n.forEach(u=>{const c=`
                <label class="flex items-center p-3 border border-gray-200 rounded cursor-pointer hover:bg-blue-50 transition">
                    <input 
                        type="radio" 
                        name="health_option_id" 
                        value="${u.id}" 
                        class="form-radio text-blue-600 w-4 h-4"
                        onchange="QuizManager.enableNextButton()"
                    >
                    <span class="ml-3 text-gray-700">${u.text}</span>
                </label>
            `;l.innerHTML+=c}),this.answers[t.question_id]?(document.querySelector(`input[name="health_option_id"][value="${this.answers[t.question_id]}"]`).checked=!0,this.enableNextButton()):this.disableNextButton(),s===1?document.getElementById("prev-button").disabled=!0:document.getElementById("prev-button").disabled=!1,s===i?document.getElementById("next-button").textContent="Finish":document.getElementById("next-button").textContent="Next",document.getElementById("error-message").classList.add("hidden")},submitAnswer(){const t=document.querySelector('input[name="health_option_id"]:checked');if(!t)return this.showErrorMessage("Please select an option before proceeding."),!1;const e=document.getElementById("question-id").value,n=t.value;this.answers[e]=n,this.disableNextButton(),this.showLoading(!0),o.post("/patient/health-quiz/answer",{quiz_attempt_id:this.quizAttemptId,health_question_id:e,health_option_id:n}).then(s=>{this.currentQuestionOrder===this.totalQuestions?this.finishQuiz():this.loadQuestion(this.currentQuestionOrder+1)}).catch(s=>{console.error("Submit answer error:",s),this.showLoading(!1),this.enableNextButton(),this.showErrorMessage("Failed to submit answer. Please try again.")})},goToPreviousQuestion(){if(this.currentQuestionOrder>1){const t=document.querySelector('input[name="health_option_id"]:checked');if(t){const e=document.getElementById("question-id").value;this.answers[e]=t.value}this.loadQuestion(this.currentQuestionOrder-1)}},finishQuiz(){this.showLoading(!0),this.showContent(!1),o.post("/patient/health-quiz/finish",{quiz_attempt_id:this.quizAttemptId}).then(t=>{this.showLoading(!1),this.renderResult(t.data)}).catch(t=>{console.error("Finish quiz error:",t),this.showLoading(!1),this.showError("Failed to finish quiz. Please try again.")})},renderResult(t){const e=document.getElementById("quiz-result"),n=document.getElementById("quiz-container")?.dataset.dashboardUrl||"/dashboard/patient",s=`
            <div class="text-center">
                <div class="mb-8">
                    <svg class="w-16 h-16 mx-auto text-green-600 mb-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <h2 class="text-3xl font-bold text-gray-800 mb-4">Quiz Complete!</h2>
                </div>

                <div class="bg-blue-50 p-6 rounded mb-6">
                    <div class="mb-6">
                        <p class="text-gray-600 text-sm mb-2">Your Score</p>
                        <p class="text-4xl font-bold text-blue-600">${t.total_score}</p>
                    </div>

                    <hr class="my-4">

                    <div class="text-left">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">Recommendation</h3>
                        
                        <div class="mb-4">
                            <p class="text-gray-600 text-sm">Disease Category</p>
                            <p class="text-lg font-semibold text-gray-800">${this.escapeHtml(t.recommendation?.disease_name||"N/A")}</p>
                        </div>

                        <div class="mb-4">
                            <p class="text-gray-600 text-sm">Recommended Medicine</p>
                            <p class="text-lg font-semibold text-gray-800">${this.escapeHtml(t.recommendation?.medicine_name||"N/A")}</p>
                        </div>

                        <div>
                            <p class="text-gray-600 text-sm">Advice</p>
                            <p class="text-gray-800">${this.escapeHtml(t.recommendation?.advice||"N/A")}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-yellow-100 border-l-4 border-yellow-500 p-4 rounded mb-6">
                    <p class="text-yellow-800 text-sm">
                        <strong>⚠️ Important:</strong> This is not a medical diagnosis. Please consult a doctor for proper treatment.
                    </p>
                </div>

                <div class="flex gap-4 justify-center">
                    <a href="${n}" class="bg-gray-600 text-white px-6 py-2 rounded hover:bg-gray-700">
                        Go to Dashboard
                    </a>
                    <button onclick="location.reload()" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                        Retake Quiz
                    </button>
                </div>
            </div>
        `;e.innerHTML=s,e.classList.remove("hidden")},enableNextButton(){document.getElementById("next-button").disabled=!1},disableNextButton(){document.getElementById("next-button").disabled=!0},showErrorMessage(t){const e=document.getElementById("error-message");e.textContent=t,e.classList.remove("hidden")},showLoading(t){const e=document.getElementById("quiz-loading");if(e)if(t){e.classList.remove("hidden");const n=e.querySelector("p");n&&(this.currentQuestionOrder===0?n.textContent="Starting quiz...":n.textContent="Loading question...")}else e.classList.add("hidden")},showContent(t){document.getElementById("quiz-content").classList.toggle("hidden",!t)},showError(t){document.getElementById("quiz-error").classList.remove("hidden");const e=document.querySelector("#quiz-error p");e&&(e.textContent=t),this.showLoading(!1),this.showContent(!1)},escapeHtml(t){const e=document.createElement("div");return e.textContent=t,e.innerHTML}};function a(){document.readyState==="loading"?document.addEventListener("DOMContentLoaded",()=>r.init()):r.init()}window.QuizManager=r;window.initializeQuiz=a;document.readyState!=="loading"?a():document.addEventListener("DOMContentLoaded",a);
