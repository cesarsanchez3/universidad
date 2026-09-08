<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CalificacionesController extends Controller
{
    public function index()
    {
        // Obtener materias ordenadas alfabéticamente
        $materias = DB::select("SELECT id, nombre FROM materias ORDER BY nombre ASC");
        return view('calificaciones.index', compact('materias'));
    }

    // Lista alumnos para buscador de calificaciones.
    public function list(Request $request)
    {
        $search = $request->search ?? '';

        $alumnos = DB::select("
            SELECT id, matricula, nombre, paterno, materno
            FROM alumnos
            WHERE nombre LIKE ? OR matricula LIKE ?
            ORDER BY nombre ASC, paterno ASC
        ", ["%$search%", "%$search%"]);

        return response()->json($alumnos);
    }

    // Guarda una nueva calificación.
    public function save(Request $request)
    {
        // Validación manual de campos
        $request->validate([
            'alumno_id' => 'required|integer|exists:alumnos,id',
            'materia_id' => 'required|integer|exists:materias,id',
            'p1' => 'required|numeric|min:0|max:10',
            'p2' => 'required|numeric|min:0|max:10',
            'p3' => 'required|numeric|min:0|max:10',
        ]);

        // convertir a float y calcular promedio
        $p1 = floatval($request->p1);
        $p2 = floatval($request->p2);
        $p3 = floatval($request->p3);

        $promedio = round(($p1 + $p2 + $p3) / 3, 2);

        // Verificar si ya existe una calificación para ese alumno y materia.
        $existe = DB::selectOne("
            SELECT id FROM calificaciones
            WHERE alumno_id = ? AND materia_id = ?
            LIMIT 1
        ", [$request->alumno_id, $request->materia_id]);

        if ($existe) {
            return response()->json([
                'ok' => false,
                'message' => 'Ya existe una calificación para este alumno y materia'
            ], 409);
        }

        // Insertar nueva calificación.
        DB::insert("
            INSERT INTO calificaciones (
                alumno_id, materia_id, parcial1, parcial2, parcial3, promedio, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, NOW())
        ", [
            $request->alumno_id,
            $request->materia_id,
            $p1, $p2, $p3,
            $promedio
        ]);

        return response()->json([
            'ok' => true,
            'promedio' => $promedio
        ]);
    }
}
