<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactoSolicitado extends Model
{
    use HasFactory;

    protected $table = 'contactos_solicitados';

    protected $fillable = [
        'empresa_id', 'persona_id',
        'estado', 'notas_admin',
        'fecha_contacto', 'fecha_entrevista', 'fecha_resultado',
    ];

    protected $casts = [
        'fecha_contacto'   => 'datetime',
        'fecha_entrevista' => 'datetime',
        'fecha_resultado'  => 'datetime',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class);
    }
}