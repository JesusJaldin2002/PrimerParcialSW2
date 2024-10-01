<?php

use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Exports\TasksExport;
use App\Http\Controllers\BacklogController;
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
    Route::get('/projects/{projectId}/kanban/{sprintId}', [ProjectController::class, 'showKanban'])->name('projects.kanban');
    Route::get('/projects/edit/{id}', [ProjectController::class,'edit'])->name('projects.edit');
    Route::put('/projects/update/{id}', [ProjectController::class, 'update'])->name('projects.update');
    Route::get('/projects/show/{id}', [ProjectController::class,'show'])->name('projects.show');
    Route::post('/projects/add', [ProjectController::class,'add'])->name('projects.add');
    Route::post('/projects/{project}/delete', [ProjectController::class,'delete'])->name('projects.delete');
    // Tasks
    Route::get('/projects/tasks/{taskId}', [ProjectController::class, 'getTask']);
    Route::get('/projects/{projectId}/sprints/{sprintId}/tasks', [ProjectController::class, 'getSprintTasks']);
    Route::put('/projects/tasks/{task}/update-status', [ProjectController::class, 'updateStatus']);
    Route::delete('/projects/tasks/{task}/delete', [ProjectController::class, 'destroyTask'])->name('projects.tasks.delete');


    // Export Excel Backlog
    Route::get('export-tasks', function () {
        return Excel::download(new TasksExport, 'tasks.xlsx');
    });

    //Backlogs
    Route::get('/backlogs/create/{id}', [BacklogController::class, 'create'])->name('backlogs.create');
    Route::post('/backlogs/{id}', [BacklogController::class, 'store'])->name('backlogs.store');
    Route::get('/backlogs/edit/{id}', [BacklogController::class, 'edit'])->name('backlogs.edit');
    Route::put('/backlogs/{id}', [BacklogController::class, 'update'])->name('backlogs.update');
    Route::delete('/backlogs/{id}/delete', [BacklogController::class, 'destroy'])->name('backlogs.delete');

});
