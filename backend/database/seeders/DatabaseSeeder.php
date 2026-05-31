<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Llamamos a nuestros Seeders personalizados para llenar la base de datos
        $this->call([
            EmpresaSeeder::class,
            PersonaSeeder::class,
        ]);
    }
}