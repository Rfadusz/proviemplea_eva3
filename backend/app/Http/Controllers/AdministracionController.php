<?php

namespace App\Http\Controllers;

use App\Models\ContactoSolicitado;
use App\Models\Empresa;
use App\Models\Persona;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

class AdministracionController extends Controller
{
    #[OA\Get(path: "/admin/contactos", operationId: "getContactosSolicitados", summary: "Listar contactos solicitados", tags: ["Administración"])]
    #[OA\Response(response: 200, description: "Listado exitoso")]
    public function listarContactos(Request $request): JsonResponse
    {
        $query = ContactoSolicitado::with(['empresa', 'persona']);
        if ($request->has('estado')) {
            $query->where('estado', $request->input('estado'));
        }
        return $this->successResponse($query->orderBy('created_at', 'desc')->get());
    }

    #[OA\Post(path: "/admin/contactos", operationId: "crearContactoSolicitado", summary: "Registrar solicitud de contacto", tags: ["Administración"])]
    #[OA\Response(response: 201, description: "Contacto registrado")]
    public function crearContacto(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'empresa_id'  => 'required|exists:empresas,id',
            'persona_id'  => 'required|exists:personas,id',
            'notas_admin' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Los datos enviados no son válidos.', 422, $validator->errors()->toArray());
        }

        $existente = ContactoSolicitado::where('empresa_id', $request->empresa_id)
            ->where('persona_id', $request->persona_id)
            ->whereNotIn('estado', ['no-seleccionado', 'proceso-cerrado'])
            ->first();

        if ($existente) {
            return $this->errorResponse('Ya existe una solicitud activa entre esta empresa y talento.', 409);
        }

        $contacto = ContactoSolicitado::create($validator->validated());
        return $this->successResponse($contacto->load(['empresa', 'persona']), 201);
    }

    #[OA\Patch(path: "/admin/contactos/{id}/estado", operationId: "actualizarEstadoContacto", summary: "Actualizar estado de contacto", tags: ["Administración"])]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "Estado actualizado")]
    public function actualizarEstado(Request $request, int $contacto): JsonResponse
    {
        $model = ContactoSolicitado::find($contacto);
        if (!$model) {
            return $this->errorResponse('Contacto no encontrado.', 404);
        }

        $validator = Validator::make($request->all(), [
            'estado'      => 'required|in:pendiente,contactado,entrevista,seleccionado,no-seleccionado,proceso-cerrado',
            'notas_admin' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Los datos enviados no son válidos.', 422, $validator->errors()->toArray());
        }

        $data = $validator->validated();

        if ($data['estado'] === 'contactado' && !$model->fecha_contacto) {
            $data['fecha_contacto'] = now();
        } elseif ($data['estado'] === 'entrevista' && !$model->fecha_entrevista) {
            $data['fecha_entrevista'] = now();
        } elseif (in_array($data['estado'], ['seleccionado', 'no-seleccionado']) && !$model->fecha_resultado) {
            $data['fecha_resultado'] = now();
        }

        $model->update($data);
        return $this->successResponse($model->load(['empresa', 'persona']));
    }

    #[OA\Get(path: "/admin/estadisticas", operationId: "getEstadisticas", summary: "Estadísticas generales de la plataforma", tags: ["Administración"])]
    #[OA\Response(response: 200, description: "Estadísticas generadas")]
    public function estadisticas(): JsonResponse
    {
        return $this->successResponse([
            'total_personas'       => Persona::count(),
            'personas_validadas'   => Persona::where('validado', true)->count(),
            'total_empresas'       => Empresa::count(),
            'empresas_validadas'   => Empresa::where('validado', true)->count(),
            'contactos_pendientes' => ContactoSolicitado::where('estado', 'pendiente')->count(),
            'contactos_en_proceso' => ContactoSolicitado::whereIn('estado', ['contactado', 'entrevista'])->count(),
            'contactos_exitosos'   => ContactoSolicitado::where('estado', 'seleccionado')->count(),
        ]);
    }
}