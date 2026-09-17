<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $materias = [
            'Historia',
            'Geografia',
            'Espanol',
            'Matematicas',
            'Biologia',
            'Fisica',
            'Quimica',
            'Ingles',
            'Educacion Fisica',
            'Arte',
            'Musica',
        ];

        foreach ($materias as $nombre) {
            DB::insert(
                'INSERT INTO materias (nombre, created_at, updated_at)
                 SELECT ?, NOW(), NOW()
                 WHERE NOT EXISTS (
                     SELECT 1
                     FROM materias
                     WHERE nombre = ?
                 )',
                [$nombre, $nombre]
            );
        }
    }
}