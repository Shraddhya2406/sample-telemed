@extends('layouts.patient')

@section('title', 'Health Quiz')
@section('page_title', 'Health Quiz')
@section('eyebrow', 'Simple symptom check')

@section('content')
<div class="mx-auto max-w-3xl pb-20 lg:pb-0">
    <div id="quiz-container" class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900" data-dashboard-url="{{ route('dashboard.patient') }}" data-api-base="{{ url('') }}" data-store-url="{{ route('patient.medicines.index') }}" data-appointment-url="{{ route('patient.appointments.create') }}">
        <div class="border-b border-slate-100 bg-slate-50 px-6 py-5 dark:border-slate-800 dark:bg-slate-950">
            <p class="text-sm font-semibold text-blue-700 dark:text-blue-300">Guided health assessment</p>
            <h2 class="mt-1 text-2xl font-bold text-slate-950 dark:text-white">Answer one question at a time</h2>
            <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">Choose the option that feels closest. You can go back before finishing.</p>
        </div>

        <div class="p-6 sm:p-8">
            <div id="quiz-loading" class="text-center">
                <div class="mx-auto h-10 w-10 animate-spin rounded-full border-4 border-blue-600 border-t-transparent"></div>
                <p class="mt-3 text-sm font-medium text-slate-600 dark:text-slate-300">Starting assessment...</p>
            </div>

            <div id="quiz-content" class="hidden">
                <div class="mb-8">
                    <div class="mb-3 flex justify-between text-sm font-semibold text-slate-600 dark:text-slate-300">
                        <span>Question <span id="current-question">1</span> of <span id="total-questions">10</span></span>
                        <span id="progress-percentage">0%</span>
                    </div>
                    <div class="h-3 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                        <div id="progress-bar" class="h-full rounded-full bg-blue-600 transition-all duration-500" style="width: 0%"></div>
                    </div>
                </div>

                <h3 id="question-text" class="text-2xl font-bold leading-snug text-slate-950 dark:text-white"></h3>

                <form id="quiz-form" class="mt-7">
                    @csrf
                    <input type="hidden" id="question-id" name="health_question_id">
                    <input type="hidden" id="quiz-attempt-id" name="quiz_attempt_id">
                    <div id="options-container" class="space-y-3"></div>
                    <div id="error-message" class="hidden mt-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700 dark:border-rose-900 dark:bg-rose-950 dark:text-rose-300"></div>
                </form>

                <div class="mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:justify-between">
                    <button id="prev-button" class="rounded-lg border border-slate-200 bg-white px-6 py-3 text-sm font-bold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" disabled>Previous</button>
                    <button id="next-button" class="rounded-lg bg-blue-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50" disabled>Next</button>
                </div>
            </div>

            <div id="quiz-result" class="hidden"></div>

            <div id="quiz-error" class="hidden text-center">
                <h3 class="text-xl font-bold text-slate-950 dark:text-white">Something went wrong</h3>
                <p class="mt-2 text-sm text-rose-600">Please try again.</p>
                <button onclick="location.reload()" class="mt-5 rounded-lg bg-blue-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-blue-700">Reload Page</button>
            </div>
        </div>
    </div>

    <div class="mt-5 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm leading-6 text-amber-800 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-200">
        This assessment gives medicine suggestions in simple language. It is not a medical diagnosis, so consult a qualified doctor for treatment decisions.
    </div>
</div>

@vite('resources/js/quiz-ajax.js')
@endsection
