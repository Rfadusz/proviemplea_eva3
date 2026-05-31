<?php

namespace Database\Seeders;

use App\Models\Empresa;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EmpresaSeeder extends Seeder
{
    public function run(): void
    {
        Empresa::create([
            'id' => Str::uuid(), // Generamos el UUID aquí
            'nombre_empresa' => 'Agencia Heroes Plus Ultra',
            'rut_empresa' => '12345678-9',
            'email' => 'contacto@heroes.cl',
            'tipo_empresa' => 'contratacion-directa',
            'contacto_nombre' => 'All Might',
            'contacto_email' => 'allmight@ua.cl'
        ]);
    }
}