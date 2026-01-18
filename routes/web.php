<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Patient\HealthQuizController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard/admin', function () {
        return view('dashboard.admin');
    })->name('dashboard.admin');
});

Route::middleware(['auth', 'role:doctor'])->group(function () {
    Route::get('/dashboard/doctor', function () {
        return view('dashboard.doctor');
    })->name('dashboard.doctor');
});

Route::middleware(['auth', 'role:patient'])->group(function () {
    Route::get('/dashboard/patient', function () {
        return view('dashboard.patient');
    })->name('dashboard.patient');

    // AJAX Quiz Routes
    Route::get('/patient/health-quiz', function () {
        return view('patient.quiz.ajax');
    })->name('patient.health-quiz');

    Route::post('/patient/health-quiz/start', [HealthQuizController::class, 'startQuiz']);

    Route::get('/patient/health-quiz/question/{order}', [HealthQuizController::class, 'getQuestion']);

    Route::post('/patient/health-quiz/answer', [HealthQuizController::class, 'submitAnswer']);

    Route::post('/patient/health-quiz/finish', [HealthQuizController::class, 'finishQuiz']);

    Route::get('/patient/health-quiz/result/{id}', [HealthQuizController::class, 'showResult'])->name('patient.health-quiz.result');
});
