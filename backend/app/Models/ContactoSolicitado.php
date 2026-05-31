<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ContactoSolicitado extends Model
{
    // Definimos explícitamente el nombre de la tabla
    protected $table = 'contactos_solicitados';

    // Activamos la protección para recibir los UUIDs y notas en el Request
    protected $fillable = [
        'empresa_id',
        'persona_id',
        'estado',
        'notas_admin',
        'fecha_contacto',
        'fecha_entrevista',
        'fecha_resultado'
    ];

    // Si tu tabla contactos_solicitados también usa UUID como llave primaria, descomenta las siguientes líneas:
    /*
    protected $keyType = 'string';
    public $incrementing = false;

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }
    */

    /**
     * Relación con el modelo Empresa
     */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    /**
     * Relación con el modelo Persona
     */
    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'persona_id');
    }
}