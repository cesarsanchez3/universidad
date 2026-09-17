<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CalificacionesController extends Controller
{
    public function index()
    {
        // Obtener materias ordenadas alfabéticamente
        $materias = DB::table("materias")->select('id', 'nombre')->orderBy("nombre", "ASC")->get();
        return view('calificaciones.index', compact('materias'));
    }

    // Lista alumnos para buscador de calificaciones.
    public function list(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $page = max(1, (int) $request->input('page', 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;
        $term = "%{$search}%";

        $alumnos = DB::table("alumnos")
            ->select('id', 'matricula', 'nombre', 'paterno', 'materno')
            ->where(function ($query) use ($term) {
                $query->where('nombre', 'LIKE', $term)
                      ->orWhere('matricula', 'LIKE', $term)
                      ->orWhere('paterno', 'LIKE', $term)
                      ->orWhere('materno', 'LIKE', $term);
            })
            ->orderBy("nombre", "ASC")
            ->orderBy("paterno", "ASC")
            ->orderBy("materno", "ASC")
            ->offset($offset)
            ->limit($limit)
            ->get();

        $total = DB::table("alumnos")
            ->where(function ($query) use ($term) {
                $query->where('nombre', 'LIKE', $term)
                      ->orWhere('matricula', 'LIKE', $term)
                      ->orWhere('paterno', 'LIKE', $term)
                      ->orWhere('materno', 'LIKE', $term);
            })
            ->count();

        return response()->json([
            'data' => $alumnos,
            'total' => $total,
            'page' => $page,
            'per_page' => $limit
        ]);
    }

    // Guarda una nueva calificación.
    public function save(Request $request)
    {
        // Validación manual de campos
        $validated = $request->validate([
            'alumno_id' => ['required', 'integer', 'exists:alumnos,id'],
            'materia_id' => ['required', 'integer', 'exists:materias,id'],
            'p1' => ['required', 'numeric', 'min:0', 'max:10'],
            'p2' => ['required', 'numeric', 'min:0', 'max:10'],
            'p3' => ['required', 'numeric', 'min:0', 'max:10'],
        ]);

        // convertir a float y calcular promedio
        $p1 = (float) $validated['p1'];
        $p2 = (float) $validated['p2'];
        $p3 = (float) $validated['p3'];

        $promedio = round(($p1 + $p2 + $p3) / 3, 2);

        $calificacion = DB::transaction(function () use ($request, $p1, $p2, $p3, $promedio) {

            // Verificar si ya existe una calificación para ese alumno y materia.
            $existe = DB::table("calificaciones")
                ->where('alumno_id', $validated['alumno_id'])
                ->where('materia_id', $validated['materia_id'])
                ->exists();

            if ($existe) {
                return null; // Ya existe, no se inserta
            }
            $now = now();

            // Insertar nueva calificación.
            return DB::table("calificaciones")->insertGetId([
                'alumno_id' => $validated['alumno_id'],
                'materia_id' => $validated['materia_id'],
                'parcial1' => $p1,
                'parcial2' => $p2,
                'parcial3' => $p3,
                'promedio' => $promedio,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        });
            if ($calificacion === null) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Ya existe una calificación para este alumno y materia'
                ], 409);
            }
            return response()->json([
                'ok' => true,
                'id' => $calificacion,
                'promedio' => $promedio
            ], 201);
    }
}
