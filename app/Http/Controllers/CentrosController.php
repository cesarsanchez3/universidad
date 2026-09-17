<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class CentrosController extends Controller
{
    // Vista principal con listado completo
    public function index()
    {
        $centros = DB::table('centros')
            ->orderBy('telebachillerato', 'asc')
            ->get();

        return view('centros.index', compact('centros'));
    }

    // Listado con paginación y búsqueda
    public function list(Request $request)
    {
        $search = trim((string) ($request->search ?? ''));
        $page = max(1, (int) ($request->page ?? 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $query = DB::table('centros');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('clave', 'like', "%{$search}%")
                  ->orWhere('telebachillerato', 'like', "%{$search}%")
                  ->orWhere('municipio', 'like', "%{$search}%")
                  ->orWhere('encargado', 'like', "%{$search}%");
            });
        }

        $centros = $query
            ->orderBy('telebachillerato', 'asc')
            ->offset($offset)
            ->limit($limit)
            ->get();

        $total = $query->count();

        return response()->json([
            'data' => $centros,
            'total' => $total
        ]);
    }

    // Detalle de centro
    public function detalle($id)
    {
        if (!is_numeric($id)) {
            abort(400, 'ID inválido');
        }

        $centro = DB::table('centros')->where('id', $id)->first();

        if (!$centro) {
            abort(404, 'Centro no encontrado');
        }

        $alumnos = DB::table('alumnos')
            ->select('id', 'matricula', 'nombre', 'paterno', 'materno')
            ->where('centro_id', $id)
            ->orderBy('nombre', 'asc')
            ->get();

        return view('centros.detalle', compact('centro', 'alumnos'));
    }

    // Importación de centros desde archivo CSV o Excel
    public function importar(Request $request)
    {
        if (!$request->hasFile('csv') && !$request->hasFile('excel')) {
            return response()->json([
                'error' => 'No se envió ningún archivo.'
            ], 400);
        }

        $file = $request->file('csv') ?? $request->file('excel');
        $extension = strtolower($file->getClientOriginalExtension());

        try {
            if ($extension === 'csv') {
                $reader = IOFactory::createReader('Csv');
                $reader->setDelimiter(',');
                $reader->setEnclosure('"');
                $reader->setInputEncoding('UTF-8');
            } else {
                $reader = IOFactory::createReader('Xlsx');
            }

            $spreadsheet = $reader->load($file->getPathname());
        } catch (\Exception $e) {
            DB::table('import_log')->insert([
                'tipo' => 'centros',
                'mensaje' => 'Archivo inválido: ' . $e->getMessage(),
                'created_at' => now()
            ]);

            return response()->json([
                'error' => 'El archivo no es válido.',
                'detalle' => $e->getMessage()
            ], 400);
        }

        $rows = $spreadsheet->getActiveSheet()->toArray();
        $insertados = 0;
        $errores = [];

        DB::beginTransaction();

        try {
            foreach ($rows as $index => $row) {
                if ($index === 0) continue;

                // Salta filas vacías
                if (empty(array_filter($row, fn($value) => $value !== null && trim((string) $value) !== ''))) {
                    continue;
                }

                if (count($row) < 6) {
                    $errores[] = "Fila $index: Número de columnas inválido.";
                    continue;
                }

                $clave = trim((string) $row[0]);
                $telebachillerato = trim((string) $row[1]);
                $clave_ct = trim((string) $row[2]);
                $municipio = trim((string) $row[3]);
                $encargado = trim((string) $row[4]);
                $correo = trim((string) $row[5]);

                if ($clave === '') {
                    $errores[] = "Fila $index: Clave vacía.";
                    continue;
                }

                if ($telebachillerato === '') {
                    $errores[] = "Fila $index: Telebachillerato vacío.";
                    continue;
                }

                $existe = DB::table('centros')
                    ->whereRaw('LOWER(clave) = ?', [strtolower($clave)])
                    ->exists();

                if ($existe) {
                    $errores[] = "Fila $index: Centro duplicado ($clave).";
                    continue;
                }

                DB::table('centros')->insert([
                    'clave' => $clave,
                    'telebachillerato' => $telebachillerato,
                    'clave_ct' => $clave_ct !== '' ? $clave_ct : null,
                    'municipio' => $municipio !== '' ? $municipio : null,
                    'encargado' => $encargado !== '' ? $encargado : null,
                    'correo' => $correo !== '' ? $correo : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $insertados++;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => 'Ocurrió un error al importar centros.',
                'detalle' => $e->getMessage()
            ], 500);
        }

        return response()->json([
            'insertados' => $insertados,
            'errores' => $errores
        ]);
    }
}