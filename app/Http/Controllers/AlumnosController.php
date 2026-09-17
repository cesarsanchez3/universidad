<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AlumnosController extends Controller
{
    /**
     * Vista principal de alumnos.
     */
    public function index()
    {
        return view('alumnos.index');
    }

    /**
     * Listado paginado y búsqueda de alumnos.
     */
    public function list(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('search', ''));
        $page = max(1, (int) $request->query('page', 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $query = DB::table('alumnos as a')
            ->join('centros as c', 'c.id', '=', 'a.centro_id')
            ->select([
                'a.id',
                'a.matricula',
                'a.nombre',
                'a.paterno',
                'a.materno',
                'a.estatus',
                'a.centro_id',
                'c.telebachillerato as centro',
            ]);

        if ($search !== '') {
            $like = '%' . $search . '%';

            $query->where(function ($q) use ($like) {
                $q->where('a.matricula', 'like', $like)
                    ->orWhere('a.nombre', 'like', $like)
                    ->orWhere('a.paterno', 'like', $like)
                    ->orWhere('a.materno', 'like', $like)
                    ->orWhere('c.telebachillerato', 'like', $like);
            });
        }

        $total = (clone $query)->count('a.id');

        $alumnos = $query
            ->orderBy('a.nombre')
            ->orderBy('a.paterno')
            ->offset($offset)
            ->limit($limit)
            ->get();

        return response()->json([
            'data' => $alumnos,
            'total' => $total,
            'page' => $page,
            'per_page' => $limit,
            'last_page' => max(1, (int) ceil($total / $limit)),
        ]);
    }

    /**
     * Detalle de un alumno.
     */
    public function detalle($id)
    {
        $id = filter_var($id, FILTER_VALIDATE_INT);

        if (!$id || $id < 1) {
            abort(400, 'ID de alumno inválido.');
        }

        $alumno = DB::table('alumnos as a')
            ->join('centros as c', 'c.id', '=', 'a.centro_id')
            ->select([
                'a.*',
                'c.telebachillerato as centro',
            ])
            ->where('a.id', $id)
            ->first();

        if (!$alumno) {
            abort(404, 'Alumno no encontrado.');
        }

        return view('alumnos.detalle', compact('alumno'));
    }

    /**
     * Actualiza los datos de un alumno.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $id = filter_var($id, FILTER_VALIDATE_INT);

        if (!$id || $id < 1) {
            return response()->json([
                'ok' => false,
                'error' => 'ID de alumno inválido.',
            ], 400);
        }

        try {
            $datos = $request->validate([
                'nombre' => ['required', 'string', 'max:255'],
                'paterno' => ['nullable', 'string', 'max:255'],
                'materno' => ['nullable', 'string', 'max:255'],
                'estatus' => ['nullable', 'string', 'max:50'],
                'genero' => ['nullable', 'string', 'max:20'],
                'generacion' => ['nullable', 'integer', 'min:1900', 'max:2200'],
                'municipio_residencia' => ['nullable', 'string', 'max:255'],
                'pais_nacimiento' => ['nullable', 'string', 'max:255'],
                'fecha_nacimiento' => ['nullable', 'date'],
            ]);
        } catch (ValidationException $exception) {
            return response()->json([
                'ok' => false,
                'errores' => $exception->errors(),
            ], 422);
        }

        $existe = DB::table('alumnos')
            ->where('id', $id)
            ->exists();

        if (!$existe) {
            return response()->json([
                'ok' => false,
                'error' => 'Alumno no encontrado.',
            ], 404);
        }

        $datos['updated_at'] = now();

        DB::table('alumnos')
            ->where('id', $id)
            ->update($datos);

        return response()->json([
            'ok' => true,
            'mensaje' => 'Alumno actualizado correctamente.',
        ]);
    }

    /**
     * Servicio JSON o API para obtener el detalle de un alumno.
     */
    public function apiDetalle($id): JsonResponse
    {
        $id = filter_var($id, FILTER_VALIDATE_INT);

        if (!$id || $id < 1) {
            return response()->json([
                'error' => 'ID de alumno inválido.',
            ], 400);
        }

        $alumno = DB::table('alumnos as a')
            ->join('centros as c', 'c.id', '=', 'a.centro_id')
            ->select([
                'a.id',
                'a.matricula',
                'a.nombre',
                'a.paterno',
                'a.materno',
                'a.estatus',
                'a.genero',
                'a.generacion',
                'a.municipio_residencia',
                'a.pais_nacimiento',
                'a.fecha_nacimiento',
                'c.id as centro_id',
                'c.telebachillerato as centro',
            ])
            ->where('a.id', $id)
            ->first();

        if (!$alumno) {
            return response()->json([
                'error' => 'Alumno no encontrado.',
            ], 404);
        }

        $calificaciones = DB::table('calificaciones as c')
            ->join('materias as m', 'm.id', '=', 'c.materia_id')
            ->select([
                'm.id as materia_id',
                'm.nombre as materia',
                'c.parcial1',
                'c.parcial2',
                'c.parcial3',
                'c.promedio',
            ])
            ->where('c.alumno_id', $id)
            ->orderBy('m.nombre')
            ->get();

        return response()->json([
            'alumno' => $alumno,
            'calificaciones' => $calificaciones,
        ]);
    }
}