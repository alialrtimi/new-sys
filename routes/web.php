<?php

use App\Http\Controllers\peopleController;
use App\Http\Controllers\SearchLogController;
use App\Http\Controllers\userController;
// use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
// use Inertia\Inertia;

use App\Http\Controllers\NoteController;
use App\Http\Controllers\ReportsController;

Route::get('/test', function () {
    return 'ok';
});
Route::get('/', function () {
    return redirect()->to('dashboard');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', [peopleController::class, 'index'])->name('dashboard');

    Route::get('/fast', function () {
        return 'ok';
    });

    Route::get('reports', [ReportsController::class, 'index'])->name('reports');

    Route::get('/rejected-personal-pictures-index', [peopleController::class, 'rejected_personal_pictures_index'])->name('rejected-personal-pictures-index');

    Route::get('/waiting-for-search', [peopleController::class, 'waiting_for_search'])->name('waiting-for-search');
    Route::get('/booked-for-searchers', [peopleController::class, 'booked_for_searchers'])->name('booked-for-searchers');

    Route::get('/criminal-record-office', [peopleController::class, 'criminal_record_office'])->name('criminal-record-office');

    Route::get('/waiting-for-approval', [peopleController::class, 'waiting_for_approval'])->name('waiting-for-approval');

    // ready to recive
    Route::get('/ready-requests', [peopleController::class, 'ready_requests'])->name('ready-requests');


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
