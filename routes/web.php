<?php

use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Exports\TasksExport;
use Maatwebsite\Excel\Facades\Excel;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();
// Rutas que requieren autenticación
Route::middleware(['auth'])->group(function () {

    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

    // Projects
    Route::post('/projects', [ProjectController::class,'store'])->name('projects.store');
    Route::get('/projects/edit/{id}', [ProjectController::class,'edit'])->name('projects.edit');
    Route::put('/projects/update/{id}', [ProjectController::class, 'update'])->name('projects.update');
    Route::get('/projects/show/{id}', [ProjectController::class,'show'])->name('projects.show');
    Route::post('/projects/add', [ProjectController::class,'add'])->name('projects.add');
    Route::post('/projects/{project}/delete', [ProjectController::class,'delete'])->name('projects.delete');

    Route::get('export-tasks', function () {
        return Excel::download(new TasksExport, 'tasks.xlsx');
    });

});
