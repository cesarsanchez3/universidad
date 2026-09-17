<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportadorCompletoController extends Controller
{
    public function importarTodo(Request $request)
    {
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

    private function importarCentros($file)
    {
        try {
            $extension = strtolower($file->getClientOriginalExtension());

            if ($extension === 'csv') {
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

        DB::beginTransaction();

        try {
            foreach ($rows as $index => $row) {
                if ($index === 0) continue;

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

                if ($clave === '' || $telebachillerato === '') {
                    $errores[] = "Fila $index: Clave o telebachillerato vacíos.";
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

            return [
                'insertados' => 0,
                'errores' => ['Error al importar centros: ' . $e->getMessage()]
            ];
        }

        return [
            'insertados' => $insertados,
            'errores' => $errores
        ];
    }

    private function importarAlumnos($file)
    {
        try {
            $extension = strtolower($file->getClientOriginalExtension());

            if ($extension === 'csv') {
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

        DB::beginTransaction();

        try {
            foreach ($rows as $index => $row) {
                if ($index === 0) continue;

                if (empty(array_filter($row, fn($value) => $value !== null && trim((string) $value) !== ''))) {
                    continue;
                }

                if (count($row) < 11) {
                    $errores[] = "Fila $index: Número de columnas inválido.";
                    continue;
                }

                $matricula = trim((string) $row[0]);
                $centro_nom = trim((string) $row[1]);
                $estatus = trim((string) $row[2]);
                $nombre = trim((string) $row[3]);
                $paterno = trim((string) $row[4]);
                $materno = trim((string) $row[5]);
                $genero = trim((string) $row[6]);
                $generacion = trim((string) $row[7]);
                $municipio = trim((string) $row[8]);
                $pais = trim((string) $row[9]);
                $fecha = trim((string) $row[10]);

                if ($matricula === '') {
                    $errores[] = "Fila $index: Matrícula vacía.";
                    continue;
                }

                $existe = DB::table('alumnos')
                    ->whereRaw('LOWER(matricula) = ?', [strtolower($matricula)])
                    ->exists();

                if ($existe) {
                    $errores[] = "Fila $index: Matrícula duplicada ($matricula).";
                    continue;
                }

                $centro = DB::table('centros')
                    ->whereRaw('LOWER(telebachillerato) LIKE ?', ['%' . strtolower($centro_nom) . '%'])
                    ->first();

                if (!$centro) {
                    $errores[] = "Fila $index: Centro no encontrado ($centro_nom).";
                    continue;
                }

                $fechaNacimiento = null;

                if ($fecha !== '') {
                    $fechaNacimiento = $this->normalizarFecha($fecha);
                    if ($fechaNacimiento === null) {
                        $errores[] = "Fila $index: Fecha inválida ($fecha).";
                        continue;
                    }
                }

                DB::table('alumnos')->insert([
                    'matricula' => $matricula,
                    'centro_id' => $centro->id,
                    'estatus' => $estatus !== '' ? $estatus : null,
                    'nombre' => $nombre !== '' ? $nombre : null,
                    'paterno' => $paterno !== '' ? $paterno : null,
                    'materno' => $materno !== '' ? $materno : null,
                    'genero' => $genero !== '' ? $genero : null,
                    'generacion' => $generacion !== '' ? (int) $generacion : null,
                    'municipio_residencia' => $municipio !== '' ? $municipio : null,
                    'pais_nacimiento' => $pais !== '' ? $pais : null,
                    'fecha_nacimiento' => $fechaNacimiento,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $insertados++;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'insertados' => 0,
                'errores' => ['Error al importar alumnos: ' . $e->getMessage()]
            ];
        }

        return [
            'insertados' => $insertados,
            'errores' => $errores
        ];
    }

    private function normalizarFecha($fecha)
    {
        $fecha = trim((string) $fecha);

        if ($fecha === '0000-00-00' || $fecha === '00/00/0000' || $fecha === '00/00/00') {
            return null;
        }

        // Soporta formatos: MM/DD/YYYY, DD/MM/YYYY, YYYY-MM-DD
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            return $fecha;
        }

        if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $fecha)) {
            $partes = explode('/', $fecha);

            if (count($partes) !== 3) {
                return null;
            }

            [$mes, $dia, $anio] = $partes;

            if (!checkdate((int) $mes, (int) $dia, (int) $anio)) {
                return null;
            }

            return sprintf('%04d-%02d-%02d', (int) $anio, (int) $mes, (int) $dia);
        }

        if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{2}$/', $fecha)) {
            $partes = explode('/', $fecha);

            if (count($partes) !== 3) {
                return null;
            }

            [$dia, $mes, $anio] = $partes;

            $anio = (int) $anio;
            if ($anio < 100) {
                $anio += 2000;
            }

            if (!checkdate((int) $mes, (int) $dia, $anio)) {
                return null;
            }

            return sprintf('%04d-%02d-%02d', $anio, (int) $mes, (int) $dia);
        }

        return null;
    }
}