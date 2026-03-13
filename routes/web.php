<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Patient\HealthQuizController;
use App\Http\Controllers\Patient\MedicineController;
use App\Http\Controllers\Patient\CartController;
use App\Http\Controllers\Patient\OrderController;

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

    // Pharmacy module routes
    Route::prefix('patient')->name('patient.')->group(function () {
        Route::get('/medicines', [MedicineController::class, 'index'])->name('medicines.index');
        Route::get('/medicines/{medicine}', [MedicineController::class, 'show'])->name('medicines.show');

        Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
        Route::post('/cart/add', [CartController::class, 'addToCart'])->name('cart.add');
        Route::post('/cart/update', [CartController::class, 'updateQuantity'])->name('cart.update');
        Route::post('/cart/remove', [CartController::class, 'removeItem'])->name('cart.remove');
        Route::post('/cart/clear', [CartController::class, 'clearCart'])->name('cart.clear');

        Route::get('/checkout', [OrderController::class, 'checkout'])->name('checkout');
        Route::post('/place-order', [OrderController::class, 'placeOrder'])->name('orders.place');

        Route::get('/orders', [OrderController::class, 'myOrders'])->name('orders.index');
        Route::get('/orders/{order}', [OrderController::class, 'orderDetails'])->name('orders.show');

        // Health Tips page
        Route::get('/health-tips', function () {
            return view('patient.health-tips');
        })->name('health-tips');
    });
});
