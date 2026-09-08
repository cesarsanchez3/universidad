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
        $centros = DB::select('SELECT * FROM centros ORDER BY telebachillerato ASC');
        return view('centros.index', compact('centros'));
    }

    // Listado con paginación y búsqueda
    public function list(Request $request)
    {
        $search = $request->search ?? '';
        $page   = max(1, (int) ($request->page ?? 1));
        $limit  = 10;
        $offset = ($page - 1) * $limit;

        $searchLike = "%$search%";
        $centros = DB::select("
            SELECT * FROM centros
            WHERE clave LIKE ? OR nombre LIKE ? OR telebachillerato LIKE ? OR municipio LIKE ?
            ORDER BY telebachillerato ASC
            LIMIT ?, ?
        ", [$searchLike, $searchLike, $searchLike,$offset, $limit]);
        

        $total = DB::selectOne("
            SELECT COUNT(*) AS total FROM centros
            WHERE clave LIKE ? OR telebachillerato LIKE ? OR municipio LIKE ?
        ", [$searchLike, $searchLike, $searchLike]);

        return response()->json([
            'data' => $centros,
            'total' => $total->total
        ]);
    }

    // Detalle de centro
    public function detalle($id)
    {
        if(!is_numeric($id)) {
            abort(400, 'ID inválido');
        }

        $centro = DB::selectOne("SELECT * FROM centros WHERE id = ?", [$id]);

        if(!$centro) {
            abort(404, 'Centro no encontrado');
        }

        $alumnos = DB::select("
            SELECT id, matricula, nombre, paterno, materno
            FROM alumnos
            WHERE centro_id = ?
            ORDER BY nombre ASC
        ", [$id]);

        return view('centros.detalle', compact('centro', 'alumnos'));
    }

    // Importación de centros desde CSV, or Excel
    public function importar(Request $request)
    {
        if (!$request->hasFile('csv') && !$request->hasFile('excel')) {
            return response()->json([
                'error' => 'No se envió ningún archivo.'
            ], 400);
        }

        $file = $request->file('csv') ?? $request->file('excel');

        try {
            if($extension === 'csv') {
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
                'error' => 'El archivo no es valido.',
                'detalle' => $e->getMessage()
            ], 400);
        }
        $rows = $spreadsheet->getActiveSheet()->toArray();

        $insertados = 0;
        $errores = [];

        foreach ($rows as $index => $row) {

            // Saltar encabezado
            if ($index === 0) continue;

            $clave      = trim($row[0]);
            $telebachillerato = trim($row[1]);
            $clave_ct   = trim($row[2]);
            $municipio  = trim($row[3]);
            $encargado  = trim($row[4]);
            $correo     = trim($row[5]);

            // Validación: clave vacía
            if ($clave === "") {
                $errores[] = "Fila $index: Clave vacia.";
                continue;
            }

            
            // Validación: telebachillerato vacío
            if ($telebachillerato === "") {
                $errores[] = "Fila $index: Telebachillerato vacio.";
                continue;
            }
            
            // Validación: duplicado por clave
            $existe = DB::selectOne("SELECT id FROM centros WHERE clave = ?", [$clave]);

            if ($existe) {
                $errores[] = "Fila $index: Centro duplicado ($clave).";
                continue;
            }

            // Inserción manual sin ORM
            DB::insert("
                INSERT INTO centros (clave, telebachillerato, clave_ct, municipio, encargado, correo, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ", [
                $clave,
                $telebachillerato,
                $clave_ct,
                $municipio,
                $encargado,
                $correo
            ]);

            $insertados++;
        }

        return response()->json([
            'insertados' => $insertados,
            'errores' => $errores
        ]);
    }
}
