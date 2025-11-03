<?php

use App\Http\Controllers\UsuarioController;

Route::get('/reservacion', [UsuarioController::class, 'index']); // SELECT
Route::post('/usuarios', [UsuarioController::class, 'store']); // INSERT


