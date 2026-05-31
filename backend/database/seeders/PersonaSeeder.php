<?php

namespace Database\Seeders;

use App\Models\Persona;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PersonaSeeder extends Seeder
{
    public function run(): void
    {
        Persona::create([
            'id' => Str::uuid(),
            'email' => 'izuku@ua.cl',
            'codigo_talento' => 'PROV-2026-SEED',
            'nivel_educacional' => 'universitaria',
            'porcentaje_completitud' => 100
        ]);
    }
}