<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ImportadorCompletoController;

class AlumnosController extends Controller
{
    public function importarAlumnos(Request $request)
    {
        $importador = new ImportadorCompletoController();
        return $importador->importarAlumnos($request->file('AlumnosTBC'));
    }
    public function index()
    {
        return view('alumnos.index');
    }

    public function list(Request $request)
    {
        $search = $request->search ?? '';
        $page   = max(1,intval($request->page ?? 1));
        $limit  = 10;
        $offset = ($page - 1) * $limit;

        $alumnos = DB::select("
            SELECT a.*, c.telebachillerato AS centro
            FROM alumnos a
            JOIN centros c ON c.id = a.centro_id
            WHERE a.nombre LIKE ? OR a.matricula LIKE ?
            ORDER BY a.nombre ASC
            LIMIT ?, ?
        ", ["%$search%", "%$search%", $offset, $limit]);

        $total = DB::selectOne("
            SELECT COUNT(*) AS total
            FROM alumnos
            WHERE nombre LIKE ? OR matricula LIKE ?
        ", ["%$search%", "%$search%"]);

        return response()->json([
            'data' => $alumnos,
            'total' => $total->total
        ]);
    }

    public function detalle($id)
    {
        $alumno = DB::selectOne("
            SELECT a.*, c.telebachillerato AS centro
            FROM alumnos a
            JOIN centros c ON c.id = a.centro_id
            WHERE a.id = ?
        ", [$id]);
        if(!$alumno) {
            abort(404, 'Alumno no encontrado');
        }

        return view('alumnos.detalle', compact('alumno'));
    }

    public function update(Request $request)
    {
        DB::update("
            UPDATE alumnos SET
                nombre = ?,
                paterno = ?,
                materno = ?,
                estatus = ?,
                municipio_residencia = ?,
                pais_nacimiento = ?
            WHERE id = ?
        ", [
            $request->nombre,
            $request->paterno,
            $request->materno,
            $request->estatus,
            $request->municipio_residencia,
            $request->pais_nacimiento,
            $request->id
        ]);

        return response()->json(['ok' => true]);
    }

    public function apiDetalle($id)
{
    // Buscar alumno con su centro
    $alumno = DB::selectOne("
        SELECT a.*, c.telebachillerato AS centro
        FROM alumnos a
        JOIN centros c ON c.id = a.centro_id
        WHERE a.id = ?
    ", [$id]);

    if (!$alumno) {
        return response()->json([
            'error' => 'Alumno no encontrado'
        ], 404);
    }

    // Obtener calificaciones del alumno
    $calificaciones = DB::select("
        SELECT m.nombre AS materia, c.parcial1, c.parcial2, c.parcial3, c.promedio
        FROM calificaciones c
        JOIN materias m ON m.id = c.materia_id
        WHERE c.alumno_id = ?
        ORDER BY m.nombre ASC
    ", [$id]);

    return response()->json([
        'alumno' => $alumno,
        'calificaciones' => $calificaciones
    ]);
}

}
