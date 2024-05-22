<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\DepartmentsController;
use App\Http\Controllers\ShiftsController;
use App\Http\Controllers\EventsController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\AttendanceController;



//Auth


Route::post('/login', [AuthController::class, 'login']);
Route::post('/usuarios/{id}/asistencia', [AttendanceController::class, 'attachAttendance']);
Route::get('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');


//Registro de asistencia
Route::get('/report/usuario', [AttendanceController::class, 'generateReportUser'])->middleware('verify.rol:Admin,Admin-1,Admin-2');
//Reporte de asistencia
Route::get('/asistencia', [AttendanceController::class, 'getDailyAttendace'])->middleware('verify.rol:Admin,Admin-1,Admin-2');



//CRUD admins
Route::prefix('admins')->middleware('verify.rol:Admin')->group(function () {
    Route::get('/', [AuthController::class, 'index']);
    Route::get('/{id}', [AuthController::class, 'show']);
    Route::post('/', [AuthController::class, 'register']);
    Route::put('/{id}', [AuthController::class, 'update']);
    Route::delete('/{id}',[AuthController::class, 'destroy'] );
});

//CRUD roles
Route::prefix('roles')->middleware('verify.rol:Admin')->group(function () {
    Route::get('/', [RolesController::class, 'index']); 
    Route::post('/', [RolesController::class, 'store']); 
    Route::get('/{id}', [RolesController::class, 'show']); 
    Route::put('/{id}', [RolesController::class, 'update']); 
    Route::delete('/{id}', [RolesController::class, 'destroy']); 
});

//CRUD departmentos
Route::prefix('departmentos')->middleware('verify.rol:Admin,Admin-1')->group(function () {
    Route::get('/', [DepartmentsController::class, 'index']); 
    Route::post('/', [DepartmentsController::class, 'store']); 
    Route::get('/{id}', [DepartmentsController::class, 'show']); 
    Route::put('/{id}', [DepartmentsController::class, 'update']); 
    Route::delete('/{id}', [DepartmentsController::class, 'destroy']); 
});

//CRUD horarios

Route::prefix('turnos')->middleware('verify.rol:Admin,Admin-1')->group(function () {
    Route::get('/', [ShiftsController::class, 'index']); 
    Route::post('/', [ShiftsController::class, 'store']); 
    Route::get('/{id}', [ShiftsController::class, 'show']); 
    Route::put('/{id}', [ShiftsController::class, 'update']); 
    Route::delete('/{id}', [ShiftsController::class, 'destroy']); 
});

//CRUD eventos
Route::get('eventos', [EventsController::class, 'index'])->middleware('verify.rol:Admin,Admin-1,Admin-2'); 

Route::prefix('eventos')->middleware('verify.rol:Admin,Admin-1')->group(function () {
    Route::post('/', [EventsController::class, 'store']); 
    Route::get('/{id}', [EventsController::class, 'show']); 
    Route::patch('/{id}', [EventsController::class, 'update']); 
    Route::patch('/{id}/status', [EventsController::class, 'updateStatus']); 
    Route::patch('/{id}/daily-attendance', [EventsController::class, 'updateDailyAttendance']);
    Route::delete('/{id}', [EventsController::class, 'destroy']);
});

//CRUD users
Route::get('usuarios', [UsersController::class, 'index'])->middleware('verify.rol:Admin,Admin-1,Admin-2'); 
Route::get('usuarios/{id}', [UsersController::class, 'show'])->middleware('verify.rol:Admin,Admin-1,Admin-2'); 

Route::prefix('usuarios')->middleware('verify.rol:Admin,Admin-1')->group(function () {
    Route::post('/', [UsersController::class, 'store']); 
    Route::patch('/{id}', [UsersController::class, 'update']); 
    Route::delete('/{id}', [UsersController::class, 'destroy']); 
});







