<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    UsuarioController,
    HotelController,
    HabitacionController,
    TransporteController,
    DescuentoController,
    ReservacionController,
    UsuarioReservacionController
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
Route::post('/login', [UsuarioController::class, 'login']);

Route::patch('reservaciones/{id}/cancelar', [ReservacionController::class, 'cancelar']);





