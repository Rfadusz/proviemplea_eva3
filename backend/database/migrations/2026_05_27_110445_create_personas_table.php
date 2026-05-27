<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personas', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Datos de contacto (privados)
            $table->string('email')->unique();
            $table->string('telefono', 15)->nullable();
            $table->string('comprobante_residencia')->nullable();

            // Datos visibles en CV ciego
            $table->string('codigo_talento')->unique(); // Ej: PROV-2026-001
            $table->text('resumen')->nullable();

            // Educación (sin institución para CV ciego)
            $table->string('nivel_educacional')->nullable();
            $table->string('titulo_carrera')->nullable();
            $table->integer('anio_egreso')->nullable();

            // Experiencia
            $table->integer('anios_experiencia')->default(0);
            $table->json('areas_experiencia')->nullable();

            // Competencias y condiciones
            $table->json('competencias')->nullable();
            $table->string('rango_renta')->nullable();
            $table->string('tipo_jornada')->nullable(); // completa, part-time, por-horas
            $table->string('modalidad')->nullable();    // presencial, remoto, híbrido

            // Perfeccionamiento e idiomas
            $table->json('cursos')->nullable();
            $table->json('idiomas')->nullable();

            // Extras
            $table->text('portafolio_url')->nullable();
            $table->boolean('persona_discapacidad')->default(false);

            // Estado
            $table->boolean('validado')->default(false);
            $table->boolean('activo')->default(true);
            $table->integer('porcentaje_completitud')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personas');
    }
};
