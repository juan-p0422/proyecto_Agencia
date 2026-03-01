<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    UsuarioController,
    HotelController,
    HabitacionController,
    TransporteController,
    DescuentoController,
    ReservacionController,
    UsuarioReservacionController,
    TwoFactorController
};

Route::apiResource('usuarios', UsuarioController::class);
Route::apiResource('hoteles', HotelController::class);
Route::apiResource('habitaciones', HabitacionController::class);
Route::apiResource('transportes', TransporteController::class);
Route::apiResource('descuentos', DescuentoController::class);
Route::apiResource('reservaciones', ReservacionController::class);

// Rutas para la pivote
Route::get('reservaciones/{id}/usuarios', [UsuarioReservacionController::class, 'indexByReservacion']);
Route::post('reservaciones/{id}/usuarios/attach', [UsuarioReservacionController::class, 'attach']);
Route::post('reservaciones/{id}/usuarios/detach', [UsuarioReservacionController::class, 'detach']);

Route::patch('reservaciones/{id}/cancelar', [ReservacionController::class, 'cancelar']);

// Login 2 pasos
Route::post('register', [TwoFactorController::class, 'register']);
Route::post('login', [TwoFactorController::class, 'login']);
Route::post('login/2fa', [TwoFactorController::class, 'login2fa']);

// Setup/confirm 2FA (requiere token Sanctum)
    Route::middleware('auth:sanctum')->group(function () {
    Route::get('2fa/setup', [TwoFactorController::class, 'setup']);
    Route::post('2fa/confirm', [TwoFactorController::class, 'confirm']);
    Route::post('2fa/disable', [TwoFactorController::class, 'disable']);
});

Route::post('2fa/enroll/start', [TwoFactorController::class, 'enrollStart']);
Route::post('2fa/enroll/confirm', [TwoFactorController::class, 'enrollConfirm']);

