<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ClerkController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ManagerController;
use App\Models\Branch;
use Illuminate\Support\Facades\Route;

// Home Route
Route::get('/home', function () {
    return view('home');
})->name('home');

// Redirect root to home 
Route::get('/', function () {
    return redirect()->route('home');
});

// Authentication Routes
Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('login', [AuthController::class, 'login']);
Route::get('register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('register', [AuthController::class, 'register']);
Route::post('logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/check-email', [AuthController::class, 'checkEmail'])->name('check-email');

// Admin Routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('dashboard', function () {
        $branches = Branch::all();
        return view('admin.dashboard', compact('branches'));
    })->name('admin.dashboard');
    Route::post('select-branch', [AuthController::class, 'selectBranch'])->name('admin.select-branch');
    Route::get('branch/{branch_id}/data', [AuthController::class, 'viewBranchData'])->name('admin.branch.data');
    Route::get('staff/register', [AuthController::class, 'showStaffRegisterForm'])->name('admin.staff.register');
    Route::post('staff/register', [AuthController::class, 'registerStaff'])->name('admin.staff.store');
});

// Customer Routes
Route::middleware(['auth', 'role:customer'])->group(function () {
    Route::get('reservations/room', [ReservationController::class, 'showRoomForm'])->name('reservations.room');
    Route::get('reservations/suite', [ReservationController::class, 'showSuiteForm'])->name('reservations.suite');
    Route::post('reservations', [ReservationController::class, 'store'])->name('reservations.store');
    Route::get('reservations', [ReservationController::class, 'index'])->name('customer.reservations');
    Route::get('reservations/{id}/edit', [ReservationController::class, 'edit'])->name('reservations.edit');
    Route::put('reservations/{id}', [ReservationController::class, 'update'])->name('reservations.update');
    Route::delete('reservations/{id}', [ReservationController::class, 'cancel'])->name('reservations.cancel');
    Route::get('/edit-profile', [CustomerController::class, 'editProfile'])->name('customer.edit-profile');
    Route::put('/profile', [CustomerController::class, 'updateProfile'])->name('customer.update-profile');
});

// Clerk Routes
Route::middleware(['auth', 'role:clerk'])->prefix('clerk')->group(function () {
    Route::get('/dashboard', [ClerkController::class, 'dashboard'])->name('clerk.dashboard');
    Route::get('/front-desk', [ClerkController::class, 'frontDesk'])->name('clerk.front-desk');
    Route::get('/walk-in', [ClerkController::class, 'walkInForm'])->name('clerk.walk-in');
    Route::post('/walk-in', [ClerkController::class, 'storeWalkIn'])->name('clerk.walk-in.store');
    Route::get('/room-types/{roomType}/available-rooms', [ClerkController::class, 'availableRoomsForType'])->name('clerk.available-rooms');
    Route::get('/room-reservation', [ClerkController::class, 'showRoomReservationForm'])->name('clerk.room-reservation');
    Route::get('/residential-suite-reservation', [ClerkController::class, 'showSuiteReservationForm'])->name('clerk.residential-suite-reservation');
    Route::post('/reservations', [ClerkController::class, 'storeReservation'])->name('clerk.reservation.store');
    Route::get('/reservations/{id}/edit', [ClerkController::class, 'editReservation'])->name('clerk.reservation.edit');
    Route::put('/reservations/{id}', [ClerkController::class, 'updateReservation'])->name('clerk.reservation.update');
    Route::delete('reservations/{id}/cancel', [ClerkController::class, 'cancelReservation'])->name('clerk.reservation.cancel');
    Route::get('/travel-agency', [ClerkController::class, 'travelAgencyBooking'])->name('clerk.travel-agency');
    Route::post('/travel-agency', [ClerkController::class, 'storeTravelAgencyBooking'])->name('clerk.travel-agency.store');
    Route::get('/room-availability', [ClerkController::class, 'roomAvailability'])->name('clerk.room-availability');
    Route::get('/check-in/{id}', [ClerkController::class, 'checkIn'])->name('clerk.check-in');
    Route::post('/check-in/{id}', [ClerkController::class, 'storeCheckIn'])->name('clerk.check-in.store');
    Route::get('/check-out/{id}', [ClerkController::class, 'checkOut'])->name('clerk.check-out');
    Route::post('/check-out/{id}', [ClerkController::class, 'storeCheckOut'])->name('clerk.check-out.store');
    Route::get('/edit-check-out/{id}', [ClerkController::class, 'editCheckOut'])->name('clerk.edit-check-out');
    Route::match(['post', 'put'], '/edit-check-out/{id}', [ClerkController::class, 'updateCheckOut'])->name('clerk.update-check-out');
});

// Manager Routes
Route::middleware(['auth', 'role:manager'])->prefix('manager')->group(function () {
    Route::get('dashboard', [ManagerController::class, 'dashboard'])->name('manager.dashboard');
    Route::get('occupancy-report', [ManagerController::class, 'occupancyReport'])->name('manager.occupancy-report');
    Route::get('revenue-report', [ManagerController::class, 'revenueReport'])->name('manager.revenue-report');
    Route::get('calendar-view', [ManagerController::class, 'calendarView'])->name('manager.calendar-view');
    Route::get('reports', [ManagerController::class, 'reports'])->name('manager.reports');
    Route::get('reports/{date}/{format}', [ManagerController::class, 'downloadReport'])->name('manager.download-report');
    Route::get('occupancy-report/download/{format}', [ManagerController::class, 'downloadOccupancyReport'])->name('manager.download-occupancy-report');
    Route::get('revenue-report/download/{format}', [ManagerController::class, 'downloadRevenueReport'])->name('manager.download-revenue-report');
});

// Dashboard Route
Route::get('dashboard', function () {
    $role = auth()->user()->role;
    if ($role === 'customer') {
        return redirect()->route('customer.reservations');
    } elseif ($role === 'clerk') {
        return redirect()->route('clerk.dashboard');
    } elseif ($role === 'manager') {
        return redirect()->route('manager.dashboard');
    } elseif ($role === 'admin') {
        return redirect()->route('admin.dashboard');
    }
})->middleware('auth')->name('dashboard');