<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CentrosController;
use App\Http\Controllers\AlumnosController;
use App\Http\Controllers\CalificacionesController;
use App\Http\Controllers\ImportadorCompletoController;

// Pagina de inicio 
Route::get('/', function () {
    return view('layouts.inicio');
});

/* ============================================================
   CENTROS
   ============================================================ */
// Vista principal de centros   
Route::get('/centros', [CentrosController::class, 'index']);
// Listado de centros con paginación 
Route::get('/centros/list', [CentrosController::class, 'list']);
// Detalle de centro
Route::get('/centros/{id}', [CentrosController::class, 'detalle']);

/* ============================================================
   ALUMNOS
   ============================================================ */
// Vista principal de alumnos
Route::get('/alumnos', [AlumnosController::class, 'index']);
// Listado de alumnos con paginación
Route::get('/alumnos/list', [AlumnosController::class, 'list']);
// Detalle de alumno
Route::get('/alumnos/{id}', [AlumnosController::class, 'detalle']);
// Actualizar alumno
Route::put('/alumnos/{id}', [AlumnosController::class, 'update']);
// Servicio JSON o API para obtener el detalle de un alumno
Route::get('/api/alumnos/{id}', [AlumnosController::class, 'apiDetalle']);

/* ============================================================
   CALIFICACIONES
   ============================================================ */
// Vista principal de calificaciones
Route::get('/calificaciones', [CalificacionesController::class, 'index']);
// Listado de calificaciones con paginación
Route::get('/calificaciones/list', [CalificacionesController::class, 'list']);
// Guardar calificación
Route::post('/calificaciones/save', [CalificacionesController::class, 'save']);



/* ============================================================
   IMPORTADOR COMPLETO
   ============================================================ */
// Vista principal de importador completo
Route::get('/importar-todo', function () {
    return view('importar.index');
}) ->name ('importar.index');
// Ruta para importar ambos archivos (centros y alumnos)
Route::post('/importar-todo', [ImportadorCompletoController::class, 'importarTodo'])
    ->name('importar.todo');
