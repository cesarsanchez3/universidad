<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;

class ImportadorCompletoController extends Controller
{
    public function importarTodo(Request $request)
    {
        //Validacion de archivos
        if (!$request->hasFile('CentrosTBC') || !$request->hasFile('AlumnosTBC')) {
            return response()->json([
                'error' => 'Debes subir ambos archivos: Centros y Alumnos.'
            ], 400);
        }

        $fileCentros = $request->file('CentrosTBC');
        $fileAlumnos = $request->file('AlumnosTBC');

        $resCentros = $this->importarCentros($fileCentros);
        $resAlumnos = $this->importarAlumnos($fileAlumnos);

        return response()->json([
            'centros' => $resCentros,
            'alumnos' => $resAlumnos
        ]);
    }

    // ---------------------------------------------------------
    // IMPORTADOR DE CENTROS
    // ---------------------------------------------------------
    private function importarCentros($file)
    {
        try {
            $extension = strtolower($file->getClientOriginalExtension());
            
            if($extension === 'csv') {
                $reader = IOFactory::createReader('Csv');
                $reader->setDelimiter(',');
                $reader->setEnclosure('"');
                $reader->setInputEncoding('UTF-8');
            } else {
                $reader = IOFactory::createReader('Xlsx');
            }
            
            $spreadsheet = $reader->load($file->getPathname());
            $rows = $spreadsheet->getActiveSheet()->toArray();

        } catch (\Exception $e) {
            return [
                'insertados' => 0,
                'errores' => ['Archivo inválido: ' . $e->getMessage()]
            ];
        }

        if (count($rows) <= 1) {
            return [
                'insertados' => 0,
                'errores' => ['Archivo vacío']
            ];
        }

        $insertados = 0;
        $errores = [];

        foreach ($rows as $index => $row) {

            if ($index === 0) continue;

            // Validación de columnas
            if (count($row) < 6) {
                $errores[] = "Fila $index: Número de columnas inválido.";
                continue;
            }

            $clave      = trim($row[0]);
            $telebachillerato = trim($row[1]);
            $clave_ct   = trim($row[2]);
            $municipio  = trim($row[3]);
            $encargado  = trim($row[4]);
            $correo     = trim($row[5]);

            if ($clave === "" || $telebachillerato === "") {
                $errores[] = "Fila $index: Clave vacía o telebachillerato vacío.";
                continue;
            }

            $existe = DB::table("centros")->where('clave', $clave)->first();
            if ($existe) {
                $errores[] = "Fila $index: Centro duplicado ($clave).";
                continue;
            }

            DB::table("centros")->insert([
                'clave' => $clave,
                'telebachillerato' => $telebachillerato,
                'clave_ct' => $clave_ct,
                'municipio' => $municipio,
                'encargado' => $encargado,
                'correo' => $correo,
                'created_at' => now()
            ]);

            $insertados++;
        }

        return [
            'insertados' => $insertados,
            'errores' => $errores
        ];
    }

    // ---------------------------------------------------------
    // IMPORTADOR DE ALUMNOS
    // ---------------------------------------------------------
    private function importarAlumnos($file)
    {
        try {
            $extension = strtolower($file->getClientOriginalExtension());
            
            if($extension === 'csv') {
                $reader = IOFactory::createReader('Csv');
                $reader->setDelimiter(',');
                $reader->setEnclosure('"');
                $reader->setInputEncoding('UTF-8');
            } else {
                $reader = IOFactory::createReader('Xlsx');
            }
            
            $spreadsheet = $reader->load($file->getPathname());
            $rows = $spreadsheet->getActiveSheet()->toArray();

        } catch (\Exception $e) {
            return [
                'insertados' => 0,
                'errores' => ['Archivo inválido: ' . $e->getMessage()]
            ];
        }

        if (count($rows) <= 1) {
            return [
                'insertados' => 0,
                'errores' => ['Archivo vacío']
            ];
        }

        $insertados = 0;
        $errores = [];

        foreach ($rows as $index => $row) {

            if ($index === 0) continue;

            // Validación de columnas
            if (count($row) < 11) {
                $errores[] = "Fila $index: Número de columnas inválido.";
                continue;
            }

            $matricula   = trim($row[0]);
            $centro_nom  = trim($row[1]);
            $estatus     = trim($row[2]);
            $nombre      = trim($row[3]);
            $paterno     = trim($row[4]);
            $materno     = trim($row[5]);
            $genero      = trim($row[6]);
            $generacion  = intval($row[7]);
            $municipio   = trim($row[8]);
            $pais        = trim($row[9]);
            $fecha       = trim($row[10]);

            if ($matricula === "") {
                $errores[] = "Fila $index: Matrícula vacía.";
                continue;
            }

            $existe = DB::table('alumnos')->where('matricula', $matricula)->first();
            if ($existe) {
                $errores[] = "Fila $index: Matrícula duplicada ($matricula).";
                continue;
            }

            // Coincidencia parcial del nombre del centro
            $centro = DB::table('centros')
            ->where('telebachillerato', 'LIKE', "%$centro_nom%")
            ->first();

            if (!$centro) {
                $errores[] = "Fila $index: Centro no encontrado ($centro_nom).";
                continue;
            }

            $centro_id = $centro->id;

            // Normalización de fecha MM/DD/YYYY → YYYY-MM-DD
            if (!$fecha || trim($fecha) === "") {
                $fecha = null;
            } else {
                $partes = explode('/', $fecha);

                if (count($partes) === 3) {
                    $mes = str_pad($partes[0], 2, '0', STR_PAD_LEFT);
                    $dia = str_pad($partes[1], 2, '0', STR_PAD_LEFT);
                    $anio = $partes[2];
                    $fecha = "$anio-$mes-$dia";
                } else {
                    $fecha = null;
                }
            }

            DB::table('alumnos')->insert([
                'matricula' => $matricula,
                'centro_id' => $centro_id,
                'estatus' => $estatus,
                'nombre' => $nombre,
                'paterno' => $paterno,
                'materno' => $materno,
                'genero' => $genero,
                'generacion' => $generacion,
                'municipio_residencia' => $municipio,
                'pais_nacimiento' => $pais,
                'fecha_nacimiento' => $fecha,
                'created_at' => now()
            ]);

            $insertados++;
        }

        return [
            'insertados' => $insertados,
            'errores' => $errores
        ];
    }
}
