import{a as i}from"./index-B9ygI19o.js";window.axios=i;window.axios.defaults.headers.common["X-Requested-With"]="XMLHttpRequest";const r={quizAttemptId:null,currentQuestionOrder:0,totalQuestions:10,answers:{},apiBase:"",buildUrl(e){const t=this.apiBase||"";return t?t.replace(/\/$/,"")+"/"+e.replace(/^\//,""):e},init(){const e=document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");e&&(window.axios.defaults.headers.common["X-CSRF-TOKEN"]=e);const t=document.getElementById("quiz-container");t&&(this.apiBase=t.dataset.apiBase||""),this.attachEventListeners(),this.createQuizAttempt()},attachEventListeners(){document.addEventListener("click",e=>{(e.target.id==="next-button"||e.target.closest("#next-button"))&&(e.preventDefault(),this.submitAnswer()),(e.target.id==="prev-button"||e.target.closest("#prev-button"))&&(e.preventDefault(),this.goToPreviousQuestion())})},createQuizAttempt(){i.post(this.buildUrl("/patient/health-quiz/start")).then(e=>{this.quizAttemptId=e.data.quiz_attempt_id;const t=document.getElementById("quiz-attempt-id");t&&(t.value=this.quizAttemptId),this.loadQuestion(1)}).catch(e=>{console.error("Diagnosis start error:",e),this.showError("Failed to start diagnosis. Please refresh and try again.")})},loadQuestion(e){this.showLoading(!0),this.showContent(!1),i.get(this.buildUrl(`/patient/health-quiz/question/${e}`)).then(t=>{const n=t.data;this.currentQuestionOrder=e,this.totalQuestions=n.total||10,this.renderQuestion(n),this.showLoading(!1),this.showContent(!0)}).catch(t=>{console.error("Load question error:",t),this.showLoading(!1),this.showError("Failed to load question. Please try again.")})},renderQuestion(e){const t=e.question,n=e.options,s=e.current,o=e.total;document.getElementById("current-question").textContent=s,document.getElementById("total-questions").textContent=o;const d=Math.round(s/o*100);document.getElementById("progress-bar").style.width=d+"%",document.getElementById("progress-percentage").textContent=d+"%",document.getElementById("question-text").textContent=t,document.getElementById("question-id").value=e.question_id;const l=document.getElementById("options-container");l.innerHTML="",n.forEach(u=>{const c=`
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
            `;l.innerHTML+=c}),this.answers[e.question_id]?(document.querySelector(`input[name="health_option_id"][value="${this.answers[e.question_id]}"]`).checked=!0,this.enableNextButton()):this.disableNextButton(),s===1?document.getElementById("prev-button").disabled=!0:document.getElementById("prev-button").disabled=!1,s===o?document.getElementById("next-button").textContent="Finish":document.getElementById("next-button").textContent="Next",document.getElementById("error-message").classList.add("hidden")},submitAnswer(){const e=document.querySelector('input[name="health_option_id"]:checked');if(!e)return this.showErrorMessage("Please select an option before proceeding."),!1;const t=document.getElementById("question-id").value,n=e.value;this.answers[t]=n,this.disableNextButton(),this.showLoading(!0),i.post(this.buildUrl("/patient/health-quiz/answer"),{quiz_attempt_id:this.quizAttemptId,health_question_id:t,health_option_id:n}).then(s=>{this.currentQuestionOrder===this.totalQuestions?this.finishQuiz():this.loadQuestion(this.currentQuestionOrder+1)}).catch(s=>{console.error("Submit answer error:",s),this.showLoading(!1),this.enableNextButton(),this.showErrorMessage("Failed to submit answer. Please try again.")})},goToPreviousQuestion(){if(this.currentQuestionOrder>1){const e=document.querySelector('input[name="health_option_id"]:checked');if(e){const t=document.getElementById("question-id").value;this.answers[t]=e.value}this.loadQuestion(this.currentQuestionOrder-1)}},finishQuiz(){this.showLoading(!0),this.showContent(!1),i.post(this.buildUrl("/patient/health-quiz/finish"),{quiz_attempt_id:this.quizAttemptId}).then(e=>{this.showLoading(!1),this.renderResult(e.data)}).catch(e=>{console.error("Finish diagnosis error:",e),this.showLoading(!1),this.showError("Failed to finish diagnosis. Please try again.")})},renderResult(e){const t=document.getElementById("quiz-result"),n=document.getElementById("quiz-container")?.dataset.dashboardUrl||"/dashboard/patient",s=`
            <div class="text-center">
                <div class="mb-8">
                    <svg class="w-16 h-16 mx-auto text-green-600 mb-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <h2 class="text-3xl font-bold text-gray-800 mb-4">Diagnosis Complete!</h2>
                </div>

                <div class="bg-blue-50 p-6 rounded mb-6">

                    <div class="text-left">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">Recommendation</h3>
                        
                        <div class="mb-4">
                            <p class="text-gray-600 text-sm">Disease Category</p>
                            <p class="text-lg font-semibold text-gray-800">${this.escapeHtml(e.recommendation?.disease_name||"N/A")}</p>
                        </div>

                        <div class="mb-4">
                            <p class="text-gray-600 text-sm">Recommended Medicine</p>
                            <p class="text-lg font-semibold text-gray-800">${this.escapeHtml(e.recommendation?.medicine_name||"N/A")}</p>
                        </div>

                        <div>
                            <p class="text-gray-600 text-sm">Advice</p>
                            <p class="text-gray-800">${this.escapeHtml(e.recommendation?.advice||"N/A")}</p>
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
                        Retake Diagnosis
                    </button>
                    <a href="{{ route('patient.medicines.index') }}" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">
                        Visit Store
                    </a>
                </div>
            </div>
        `;t.innerHTML=s,t.classList.remove("hidden")},enableNextButton(){document.getElementById("next-button").disabled=!1},disableNextButton(){document.getElementById("next-button").disabled=!0},showErrorMessage(e){const t=document.getElementById("error-message");t.textContent=e,t.classList.remove("hidden")},showLoading(e){const t=document.getElementById("quiz-loading");if(t)if(e){t.classList.remove("hidden");const n=t.querySelector("p");n&&(this.currentQuestionOrder===0?n.textContent="Starting quiz...":n.textContent="Loading question...")}else t.classList.add("hidden")},showContent(e){document.getElementById("quiz-content").classList.toggle("hidden",!e)},showError(e){document.getElementById("quiz-error").classList.remove("hidden");const t=document.querySelector("#quiz-error p");t&&(t.textContent=e),this.showLoading(!1),this.showContent(!1)},escapeHtml(e){const t=document.createElement("div");return t.textContent=e,t.innerHTML}};function a(){document.readyState==="loading"?document.addEventListener("DOMContentLoaded",()=>r.init()):r.init()}window.QuizManager=r;window.initializeQuiz=a;document.readyState!=="loading"?a():document.addEventListener("DOMContentLoaded",a);
