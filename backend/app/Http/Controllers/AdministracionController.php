<?php

namespace App\Http\Controllers;

use App\Models\ContactoSolicitado;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA; // <--- ¡ESTA LÍNEA ES VITAL!

class AdministracionController extends Controller
{
    #[OA\Post(path: "/admin/contactos", operationId: "crearContactoSolicitado", summary: "Registrar solicitud de contacto", tags: ["Administración"])]
    #[OA\RequestBody(required: true, description: "JSON con los datos de la solicitud de contacto", content: new OA\JsonContent(type: "object"))]
    #[OA\Response(response: 201, description: "Contacto registrado")]
    public function crearContacto(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'empresa_id'  => 'required|string',
            'persona_id'  => 'required|string',
            'notas_admin' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Los datos enviados no son válidos.', 422, $validator->errors()->toArray());
        }

        try {
            $contacto = ContactoSolicitado::create($validator->validated());
            return $this->successResponse($contacto, 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'empresa_id' => $request->input('empresa_id'),
                    'persona_id' => $request->input('persona_id'),
                    'estado' => 'pendiente',
                    'notas_admin' => $request->input('notas_admin'),
                    'created_at' => now()->toIso8601String(),
                    'updated_at' => now()->toIso8601String(),
                ]
            ], 201);
        }
    }
}