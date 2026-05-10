import{a as i}from"./index-B9ygI19o.js";window.axios=i;window.axios.defaults.headers.common["X-Requested-With"]="XMLHttpRequest";const r={quizAttemptId:null,currentQuestionOrder:0,totalQuestions:10,answers:{},apiBase:"",buildUrl(t){const e=this.apiBase||"";return e?e.replace(/\/$/,"")+"/"+t.replace(/^\//,""):t},init(){const t=document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");t&&(window.axios.defaults.headers.common["X-CSRF-TOKEN"]=t);const e=document.getElementById("quiz-container");e&&(this.apiBase=e.dataset.apiBase||"",this.storeUrl=e.dataset.storeUrl||""),this.attachEventListeners(),this.createQuizAttempt()},attachEventListeners(){document.addEventListener("click",t=>{(t.target.id==="next-button"||t.target.closest("#next-button"))&&(t.preventDefault(),this.submitAnswer()),(t.target.id==="prev-button"||t.target.closest("#prev-button"))&&(t.preventDefault(),this.goToPreviousQuestion())})},createQuizAttempt(){i.post(this.buildUrl("/patient/health-quiz/start")).then(t=>{this.quizAttemptId=t.data.quiz_attempt_id;const e=document.getElementById("quiz-attempt-id");e&&(e.value=this.quizAttemptId),this.loadQuestion(1)}).catch(t=>{console.error("Diagnosis start error:",t),this.showError("Failed to start diagnosis. Please refresh and try again.")})},loadQuestion(t){this.showLoading(!0),this.showContent(!1),i.get(this.buildUrl(`/patient/health-quiz/question/${t}`)).then(e=>{const s=e.data;this.currentQuestionOrder=t,this.totalQuestions=s.total||10,this.renderQuestion(s),this.showLoading(!1),this.showContent(!0)}).catch(e=>{console.error("Load question error:",e),this.showLoading(!1),this.showError("Failed to load question. Please try again.")})},renderQuestion(t){const e=t.question,s=t.options,n=t.current,o=t.total;document.getElementById("current-question").textContent=n,document.getElementById("total-questions").textContent=o;const a=Math.round(n/o*100);document.getElementById("progress-bar").style.width=a+"%",document.getElementById("progress-percentage").textContent=a+"%",document.getElementById("question-text").textContent=e,document.getElementById("question-id").value=t.question_id;const l=document.getElementById("options-container");l.innerHTML="",s.forEach(u=>{const c=`
                <label class="group flex cursor-pointer items-center rounded-lg border border-slate-200 bg-white p-4 transition hover:border-blue-300 hover:bg-blue-50 dark:border-slate-700 dark:bg-slate-900 dark:hover:bg-slate-800">
                    <input 
                        type="radio" 
                        name="health_option_id" 
                        value="${u.id}" 
                        class="h-4 w-4 text-blue-600"
                        onchange="QuizManager.enableNextButton()"
                    >
                    <span class="ml-3 text-base font-semibold text-slate-700 group-hover:text-blue-800 dark:text-slate-200 dark:group-hover:text-blue-200">${u.text}</span>
                </label>
            `;l.innerHTML+=c}),this.answers[t.question_id]?(document.querySelector(`input[name="health_option_id"][value="${this.answers[t.question_id]}"]`).checked=!0,this.enableNextButton()):this.disableNextButton(),n===1?document.getElementById("prev-button").disabled=!0:document.getElementById("prev-button").disabled=!1,n===o?document.getElementById("next-button").textContent="Finish":document.getElementById("next-button").textContent="Next",document.getElementById("error-message").classList.add("hidden")},submitAnswer(){const t=document.querySelector('input[name="health_option_id"]:checked');if(!t)return this.showErrorMessage("Please select an option before proceeding."),!1;const e=document.getElementById("question-id").value,s=t.value;this.answers[e]=s,this.disableNextButton(),this.showLoading(!0),i.post(this.buildUrl("/patient/health-quiz/answer"),{quiz_attempt_id:this.quizAttemptId,health_question_id:e,health_option_id:s}).then(n=>{this.currentQuestionOrder===this.totalQuestions?this.finishQuiz():this.loadQuestion(this.currentQuestionOrder+1)}).catch(n=>{console.error("Submit answer error:",n),this.showLoading(!1),this.enableNextButton(),this.showErrorMessage("Failed to submit answer. Please try again.")})},goToPreviousQuestion(){if(this.currentQuestionOrder>1){const t=document.querySelector('input[name="health_option_id"]:checked');if(t){const e=document.getElementById("question-id").value;this.answers[e]=t.value}this.loadQuestion(this.currentQuestionOrder-1)}},finishQuiz(){this.showLoading(!0),this.showContent(!1),i.post(this.buildUrl("/patient/health-quiz/finish"),{quiz_attempt_id:this.quizAttemptId}).then(t=>{this.showLoading(!1),this.renderResult(t.data)}).catch(t=>{console.error("Finish diagnosis error:",t),this.showLoading(!1),this.showError("Failed to finish diagnosis. Please try again.")})},renderResult(t){const e=document.getElementById("quiz-result"),s=document.getElementById("quiz-container")?.dataset.dashboardUrl||"/dashboard/patient",n=document.getElementById("quiz-container")?.dataset.appointmentUrl||"/patient/appointments/create",o=document.getElementById("quiz-container")?.dataset.storeUrl||this.buildUrl("/patient/medicines"),a=`
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
                            <p class="mt-1 text-lg font-bold text-slate-950 dark:text-white">${this.escapeHtml(t.recommendation?.disease_name||"General wellness support")}</p>
                        </div>

                        <div class="mb-4 rounded-lg bg-white p-4 dark:bg-slate-900">
                            <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Suggested medicine</p>
                            <p class="mt-1 text-lg font-bold text-slate-950 dark:text-white">${this.escapeHtml(t.recommendation?.medicine_name||"Please consult a doctor")}</p>
                        </div>

                        <div class="rounded-lg bg-white p-4 dark:bg-slate-900">
                            <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">What to do next</p>
                            <p class="mt-1 leading-6 text-slate-700 dark:text-slate-200">${this.escapeHtml(t.recommendation?.advice||"Rest, stay hydrated, and consult a doctor if symptoms continue.")}</p>
                        </div>
                    </div>
                </div>

                <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 p-4 text-left dark:border-amber-900 dark:bg-amber-950/40">
                    <p class="text-sm leading-6 text-amber-800 dark:text-amber-200">
                        Important: This is not a medical diagnosis. Please consult a doctor for proper treatment.
                    </p>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:justify-center">
                    <a href="${o}" class="rounded-lg bg-emerald-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-600/20 transition hover:bg-emerald-700">
                        Buy Medicines
                    </a>
                    <a href="${n}" class="rounded-lg bg-blue-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700">
                        Book Doctor
                    </a>
                    <button onclick="location.reload()" class="rounded-lg border border-slate-200 bg-white px-6 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                        Retake Quiz
                    </button>
                    <a href="${s}" class="rounded-lg px-6 py-3 text-sm font-bold text-slate-600 transition hover:text-blue-700 dark:text-slate-300">Dashboard</a>
                </div>
            </div>
        `;e.innerHTML=a,e.classList.remove("hidden")},enableNextButton(){document.getElementById("next-button").disabled=!1},disableNextButton(){document.getElementById("next-button").disabled=!0},showErrorMessage(t){const e=document.getElementById("error-message");e.textContent=t,e.classList.remove("hidden")},showLoading(t){const e=document.getElementById("quiz-loading");if(e)if(t){e.classList.remove("hidden");const s=e.querySelector("p");s&&(this.currentQuestionOrder===0?s.textContent="Starting diagnosis...":s.textContent="Loading question...")}else e.classList.add("hidden")},showContent(t){document.getElementById("quiz-content").classList.toggle("hidden",!t)},showError(t){document.getElementById("quiz-error").classList.remove("hidden");const e=document.querySelector("#quiz-error p");e&&(e.textContent=t),this.showLoading(!1),this.showContent(!1)},escapeHtml(t){const e=document.createElement("div");return e.textContent=t,e.innerHTML}};function d(){document.readyState==="loading"?document.addEventListener("DOMContentLoaded",()=>r.init()):r.init()}window.QuizManager=r;window.initializeQuiz=d;document.readyState!=="loading"?d():document.addEventListener("DOMContentLoaded",d);
