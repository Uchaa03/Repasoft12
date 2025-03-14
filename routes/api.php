<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\RepairController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Rutas públicas
Route::post('technician/register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// Rutas protegidas para técnicos
Route::middleware('auth:technician')->group(function () {
    // Datos del técnico
    Route::get('technician/me', function (Request $request) {
        return response()->json($request->user());
    });

    // Gestión de clientes
    Route::post('client/register', [AuthController::class, 'registerClient']);
    Route::get('client/search', [ClientController::class, 'searchClientByEmail']);

    // Gestión de reparaciones
    Route::get('technician/repairs', [RepairController::class, 'technicianRepairs']);
    Route::post('repairs', [RepairController::class, 'store']);
    Route::put('repairs/{repair}/status', [RepairController::class, 'updateStatus']);
    Route::delete('repairs/{repair}', [RepairController::class, 'delete']);

    // Obtener últimas 5 reparaciones
    Route::get('technician/repairs/recent', [RepairController::class, 'recentRepairs']);

    // Logout
    Route::post('technician/logout', [AuthController::class, 'logout']);
});

// Rutas protegidas para clientes
Route::middleware('auth:client')->group(function () {
    // Datos del cliente
    Route::get('client/me', function (Request $request) {
        return response()->json($request->user());
    });

    // Reparaciones del cliente
    Route::get('client/repairs', [ClientController::class, 'clientRepairs']);

    // Cambio de contraseña
    Route::post('client/change-password', [PasswordResetController::class, 'changePassword']);

    // Logout
    Route::post('client/logout', [AuthController::class, 'logout']);
});
