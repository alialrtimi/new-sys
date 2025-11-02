<?php

use App\Http\Controllers\peopleController;
use App\Http\Controllers\SearchLogController;
use App\Http\Controllers\userController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\NoteController;

Route::get('/', function () {
    return redirect()->to('dashboard');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', [peopleController::class, 'index'])->name('dashboard');
    Route::post('/save-person-data', [peopleController::class, 'store']);
    Route::delete('/delete-person/{id}', [peopleController::class, 'delete_person']);


    Route::get('/restore-person/{id}', [peopleController::class, 'restore_person']);

    Route::get('/deleted-files', [peopleController::class, 'deleted_files'])->name('deleted-files');
    Route::get('/users-index', [userController::class, 'index'])->name('users-index');
    Route::post('/save-user-data', [userController::class, 'store']);
    Route::post('/edit-person-data', [peopleController::class, 'edit_person']);


    Route::post('/change-user-type', [userController::class, 'change_user_type']);
    Route::post('/change-user-password', [userController::class, 'change_user_password']);


    Route::delete('/delete-user/{id}', [userController::class, 'delete_user']);
    Route::delete('/active-user/{id}', [userController::class, 'active_user']);
    Route::post('/upload-excel', [peopleController::class, 'uploadExcel']);




    Route::resource('notes', NoteController::class);

    Route::get('/search-log', [SearchLogController::class, 'index'])->name('search-log');
});
