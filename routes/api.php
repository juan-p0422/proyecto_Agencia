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
Route::post('usuarios', [UsuarioController::class, 'store']); // POST: Crear usuario
Route::post('login', [TwoFactorController::class, 'login'])->middleware('throttle:5,1');
Route::post('login/2fa', [TwoFactorController::class, 'login2fa'])->middleware('throttle:5,1');

// Enrolamiento 2FA inicial
Route::post('2fa/enroll/start', [TwoFactorController::class, 'enrollStart']);
Route::post('2fa/enroll/confirm', [TwoFactorController::class, 'enrollConfirm']);

// Lectura de Catálogos (Solo GET)
Route::get('hoteles', [HotelController::class, 'index']);
Route::get('hoteles/{id}', [HotelController::class, 'show']);
Route::get('habitaciones', [HabitacionController::class, 'index']);
Route::get('habitaciones/{id}', [HabitacionController::class, 'show']);
Route::get('transportes', [TransporteController::class, 'index']);
Route::get('transportes/{id}', [TransporteController::class, 'show']);
Route::get('descuentos', [DescuentoController::class, 'index']);
Route::get('descuentos/{id}', [DescuentoController::class, 'show']);

/* ==========================================
   RUTAS PROTEGIDAS (Requieren Token JWT)
   ========================================== */

Route::middleware('auth:api')->group(function () { 
    
    //Solo GET para ver su propio perfil
    Route::get('usuarios/{id}', [UsuarioController::class, 'show']);

    //RESERVACIONES: Solo GET (listar/ver) y POST (crear/cancelar)
    Route::get('reservaciones', [ReservacionController::class, 'index']);
    Route::get('reservaciones/{id}', [ReservacionController::class, 'show']);
    Route::post('reservaciones', [ReservacionController::class, 'store']);
    Route::post('reservaciones/{id}/cancelar', [ReservacionController::class, 'cancelar']); // Cambiado a POST

    //Solo GET y POST (Se eliminó la opción de remover usuarios)
    Route::get('reservaciones/{id}/usuarios', [UsuarioReservacionController::class, 'indexByReservacion']);
    Route::post('reservaciones/{id}/usuarios/attach', [UsuarioReservacionController::class, 'attach']);

    // Solo POST para crear
    Route::post('hoteles', [HotelController::class, 'store']);
    Route::post('habitaciones', [HabitacionController::class, 'store']);
    Route::post('transportes', [TransporteController::class, 'store']);
    Route::post('descuentos', [DescuentoController::class, 'store']);

    // Configuración interna de 2FA
    Route::get('2fa/setup', [TwoFactorController::class, 'setup']);
    Route::post('2fa/confirm', [TwoFactorController::class, 'confirm']);
    Route::post('2fa/disable', [TwoFactorController::class, 'disable']);
});
