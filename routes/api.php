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

/* ==========================================
   RUTAS PÚBLICAS (No requieren Token)
   ========================================== */

// Autenticación y Registro
Route::post('register', [TwoFactorController::class, 'register']);
// Solo permite 5 intentos por minuto para el login y 2FA
Route::post('login', [TwoFactorController::class, 'login'])->middleware('throttle:5,1');
Route::post('login/2fa', [TwoFactorController::class, 'login2fa'])->middleware('throttle:5,1');

// Enrolamiento 2FA inicial (según tu lógica pública)
Route::post('2fa/enroll/start', [TwoFactorController::class, 'enrollStart']);
Route::post('2fa/enroll/confirm', [TwoFactorController::class, 'enrollConfirm']);

// Lectura de Catálogos (Cualquiera puede ver hoteles, habitaciones, etc.)
Route::apiResource('hoteles', HotelController::class)->only(['index', 'show']);
Route::apiResource('habitaciones', HabitacionController::class)->only(['index', 'show']);
Route::apiResource('transportes', TransporteController::class)->only(['index', 'show']);
Route::apiResource('descuentos', DescuentoController::class)->only(['index', 'show']);


/* ==========================================
   RUTAS PROTEGIDAS (Requieren Token Sanctum)
   ========================================== */

Route::middleware('auth:sanctum')->group(function () {
    
    // Gestión de Usuarios y Reservaciones completas
    Route::apiResource('usuarios', UsuarioController::class);
    Route::apiResource('reservaciones', ReservacionController::class);

    // Rutas pivote de reservaciones
    Route::get('reservaciones/{id}/usuarios', [UsuarioReservacionController::class, 'indexByReservacion']);
    Route::post('reservaciones/{id}/usuarios/attach', [UsuarioReservacionController::class, 'attach']);
    Route::post('reservaciones/{id}/usuarios/detach', [UsuarioReservacionController::class, 'detach']);
    Route::patch('reservaciones/{id}/cancelar', [ReservacionController::class, 'cancelar']);

    // Creación/Edición/Eliminación de Catálogos (Solo autenticados)
    Route::apiResource('hoteles', HotelController::class)->except(['index', 'show']);
    Route::apiResource('habitaciones', HabitacionController::class)->except(['index', 'show']);
    Route::apiResource('transportes', TransporteController::class)->except(['index', 'show']);
    Route::apiResource('descuentos', DescuentoController::class)->except(['index', 'show']);

    // Configuración interna de 2FA
    Route::get('2fa/setup', [TwoFactorController::class, 'setup']);
    Route::post('2fa/confirm', [TwoFactorController::class, 'confirm']);
    Route::post('2fa/disable', [TwoFactorController::class, 'disable']);
});

