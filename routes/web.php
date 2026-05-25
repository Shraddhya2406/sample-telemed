<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\MedicineCategoryController as AdminMedicineCategoryController;
use App\Http\Controllers\Admin\MedicineController as AdminMedicineController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Patient\AIHealthController;
use App\Http\Controllers\Patient\HealthQuizController;
use App\Http\Controllers\Patient\MedicineController;
use App\Http\Controllers\Patient\CartController;
use App\Http\Controllers\Patient\OrderController;
use App\Http\Controllers\Patient\PrescriptionController as PatientPrescriptionController;
use App\Http\Controllers\Patient\AppointmentController as PatientAppointmentController;
use App\Http\Controllers\Doctor\DoctorDashboardController;
use App\Http\Controllers\Doctor\DoctorAppointmentController;
use App\Http\Controllers\Doctor\DoctorPrescriptionController;
use App\Http\Controllers\Doctor\DoctorPatientController;
use App\Http\Controllers\RazorpayController;
use App\Http\Controllers\VideoCallController;
use App\Http\Controllers\NotificationController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/media/public/{path}', [MediaController::class, 'showPublicFile'])
    ->where('path', '.*')
    ->name('media.public');

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');

Route::middleware(['auth', 'is_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');

    Route::patch('categories/{category}/toggle-status', [AdminMedicineCategoryController::class, 'toggleStatus'])->name('categories.toggle-status');
    Route::resource('categories', AdminMedicineCategoryController::class)->except(['show']);
    Route::patch('medicines/{medicine}/toggle-status', [AdminMedicineController::class, 'toggleStatus'])->name('medicines.toggle-status');
    Route::resource('medicines', AdminMedicineController::class);
    Route::resource('orders', AdminOrderController::class)->only(['index', 'show', 'update']);
    Route::resource('users', AdminUserController::class)->only(['index', 'show', 'edit', 'update']);
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard/admin',  function () {
        return redirect()->route('admin.dashboard');
    })->name('dashboard.admin');
});

Route::middleware(['auth', 'role:doctor'])->group(function () {
    Route::get('/dashboard/doctor',  function () {
        return redirect()->route('doctor.dashboard');
    })->name('dashboard.doctor');
});

Route::middleware(['auth', 'is_doctor'])->prefix('doctor')->name('doctor.')->group(function () {
    Route::get('/dashboard', DoctorDashboardController::class)->name('dashboard');
    Route::get('/profile', [DoctorDashboardController::class, 'profile'])->name('profile');
    Route::post('/profile', [DoctorDashboardController::class, 'updateProfile'])->name('profile.update');
    Route::post('/availability', [DoctorDashboardController::class, 'updateAvailability'])->name('availability.update');

    Route::get('/patients', [DoctorPatientController::class, 'index'])->name('patients.index');
    Route::get('/patients/{patient}', [DoctorPatientController::class, 'show'])->name('patients.show');
    Route::get('/call/{patient}', [VideoCallController::class, 'start'])->name('call.start');

    Route::get('/appointments', [DoctorAppointmentController::class, 'index'])->name('appointments.index');
    Route::get('/appointments/{appointment}', [DoctorAppointmentController::class, 'show'])->name('appointments.show');
    Route::patch('/appointments/{appointment}/status', [DoctorAppointmentController::class, 'updateStatus'])->name('appointments.status');
    Route::patch('/appointments/{appointment}/notes', [DoctorAppointmentController::class, 'updateNotes'])->name('appointments.notes');
    Route::get('/appointments/{appointment}/messages', [DoctorAppointmentController::class, 'messages'])->name('appointments.messages.index');
    Route::post('/appointments/{appointment}/messages', [DoctorAppointmentController::class, 'storeMessage'])->name('appointments.messages.store');

    Route::get('/prescriptions', [DoctorPrescriptionController::class, 'index'])->name('prescriptions.index');
    Route::get('/prescriptions/create', [DoctorPrescriptionController::class, 'create'])->name('prescriptions.create');
    Route::post('/prescriptions', [DoctorPrescriptionController::class, 'store'])->name('prescriptions.store');
    Route::get('/prescriptions/{prescription}', [DoctorPrescriptionController::class, 'show'])->name('prescriptions.show');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/notifications/{notification}', [NotificationController::class, 'open'])->name('notifications.open');

    Route::get('/call/{videoCall}', [VideoCallController::class, 'show'])->name('call.show');
    Route::post('/call/accept', [VideoCallController::class, 'accept'])->name('call.accept');
    Route::post('/call/reject', [VideoCallController::class, 'reject'])->name('call.reject');
    Route::post('/call/end', [VideoCallController::class, 'end'])->name('call.end');
    Route::post('/call/signal', [VideoCallController::class, 'signal'])->name('call.signal');
});

Route::middleware(['auth', 'role:patient'])->group(function () {
    Route::get('/dashboard/patient', function () {
        return view('dashboard.patient');
    })->name('dashboard.patient');

    Route::get('/patient/health-quiz', [AIHealthController::class, 'index'])->name('patient.health-quiz');
    Route::get('/patient/ai-health-assistant', [AIHealthController::class, 'index'])->name('patient.ai-health.index');
    Route::post('/patient/ai-health-assistant/start', [AIHealthController::class, 'start'])->name('patient.ai-health.start');
    Route::post('/patient/ai-health-assistant/new', [AIHealthController::class, 'restart'])->name('patient.ai-health.restart');
    Route::post('/patient/ai-health-assistant/{conversation}/send', [AIHealthController::class, 'send'])->name('patient.ai-health.send');
    Route::post('/patient/ai-health-assistant/{conversation}/complete', [AIHealthController::class, 'complete'])->name('patient.ai-health.complete');

    // Legacy AJAX quiz endpoints are kept available for existing data and fallback flows.
    Route::post('/patient/health-quiz/start', [HealthQuizController::class, 'startQuiz']);

    Route::get('/patient/health-quiz/question/{order}', [HealthQuizController::class, 'getQuestion']);

    Route::post('/patient/health-quiz/answer', [HealthQuizController::class, 'submitAnswer']);

    Route::post('/patient/health-quiz/finish', [HealthQuizController::class, 'finishQuiz']);

    Route::get('/patient/health-quiz/result/{id}', [HealthQuizController::class, 'showResult'])->name('patient.health-quiz.result');

    Route::prefix('api')->group(function () {
        Route::post('/create-order', [RazorpayController::class, 'createOrder'])->name('razorpay.create-order');
        Route::post('/verify-payment', [RazorpayController::class, 'verifyPayment'])->name('razorpay.verify-payment');
    });

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

        Route::get('/profile', function () {
            return view('patient.profile');
        })->name('profile');

        Route::get('/appointments', [PatientAppointmentController::class, 'index'])->name('appointments.index');
        Route::get('/appointments/create', [PatientAppointmentController::class, 'create'])->name('appointments.create');
        Route::post('/appointments', [PatientAppointmentController::class, 'store'])->name('appointments.store');
        Route::post('/appointments/payment/order', [PatientAppointmentController::class, 'createPaymentOrder'])->name('appointments.payment.order');
        Route::post('/appointments/payment/verify', [PatientAppointmentController::class, 'verifyPayment'])->name('appointments.payment.verify');
        Route::get('/appointments/{appointment}', [PatientAppointmentController::class, 'show'])->name('appointments.show');
        Route::patch('/appointments/{appointment}/cancel', [PatientAppointmentController::class, 'cancel'])->name('appointments.cancel');
        Route::get('/appointments/{appointment}/messages', [PatientAppointmentController::class, 'messages'])->name('appointments.messages.index');
        Route::post('/appointments/{appointment}/messages', [PatientAppointmentController::class, 'storeMessage'])->name('appointments.messages.store');

        Route::get('/prescriptions', [PatientPrescriptionController::class, 'index'])->name('prescriptions.index');
        Route::get('/prescriptions/{prescription}', [PatientPrescriptionController::class, 'show'])->name('prescriptions.show');

        // Health Tips page
        Route::get('/health-tips', function () {
            return view('patient.health-tips');
        })->name('health-tips');
    });
});
