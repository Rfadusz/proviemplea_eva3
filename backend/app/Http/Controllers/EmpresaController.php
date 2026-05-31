<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

class EmpresaController extends Controller
{
    #[OA\Get(path: "/empresas", operationId: "getEmpresas", summary: "Listar empresas validadas", tags: ["Empresas"])]
    #[OA\Response(response: 200, description: "Listado exitoso")]
    public function index(Request $request): JsonResponse
    {
        $query = Empresa::where('activo', true);
        if ($request->has('tipo_empresa')) {
            $query->where('tipo_empresa', $request->input('tipo_empresa'));
        }
        return $this->successResponse($query->get());
    }

    #[OA\Post(path: "/empresas", operationId: "createEmpresa", summary: "Registrar nueva empresa", tags: ["Empresas"])]
    #[OA\RequestBody(required: true, description: "JSON con los datos de la empresa", content: new OA\JsonContent(type: "object"))]
    #[OA\Response(response: 201, description: "Empresa creada")]
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nombre_empresa'    => 'required|string|max:255',
            'rut_empresa'       => 'required|string|max:20|unique:empresas,rut_empresa',
            'email'             => 'required|email|unique:empresas,email',
            'logo_url'          => 'nullable|url',
            'rubro'             => 'nullable|string|max:100',
            'tipo_empresa'      => 'required|in:contratacion-directa,est,outsourcing',
            'presentacion'      => 'nullable|string',
            'beneficios'        => 'nullable|array',
            'beneficios.*'      => 'string',
            'contacto_nombre'   => 'required|string|max:100',
            'contacto_email'    => 'required|email',
            'contacto_telefono' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Los datos enviados no son válidos.', 422, $validator->errors()->toArray());
        }

        return $this->successResponse(Empresa::create($validator->validated()), 201);
    }

    #[OA\Get(path: "/empresas/{id}", operationId: "getEmpresa", summary: "Obtener empresa por ID", tags: ["Empresas"])]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "Empresa encontrada")]
    public function show(int $empresa): JsonResponse
    {
        $model = Empresa::find($empresa);
        if (!$model) {
            return $this->errorResponse('Empresa no encontrada.', 404);
        }
        return $this->successResponse($model);
    }

    #[OA\Put(path: "/empresas/{id}", operationId: "updateEmpresa", summary: "Actualizar empresa", tags: ["Empresas"])]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\RequestBody(required: true, description: "Datos a actualizar", content: new OA\JsonContent(type: "object"))]
    #[OA\Response(response: 200, description: "Empresa actualizada")]
    public function update(Request $request, int $empresa): JsonResponse
    {
        $model = Empresa::find($empresa);
        if (!$model) {
            return $this->errorResponse('Empresa no encontrada.', 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre_empresa'    => 'sometimes|string|max:255',
            'rut_empresa'       => 'sometimes|string|max:20|unique:empresas,rut_empresa,' . $model->id,
            'email'             => 'sometimes|email|unique:empresas,email,' . $model->id,
            'logo_url'          => 'nullable|url',
            'rubro'             => 'nullable|string|max:100',
            'tipo_empresa'      => 'sometimes|in:contratacion-directa,est,outsourcing',
            'presentacion'      => 'nullable|string',
            'beneficios'        => 'nullable|array',
            'beneficios.*'      => 'string',
            'contacto_nombre'   => 'sometimes|string|max:100',
            'contacto_email'    => 'sometimes|email',
            'contacto_telefono' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Los datos enviados no son válidos.', 422, $validator->errors()->toArray());
        }

        $model->update($validator->validated());
        return $this->successResponse($model->fresh());
    }

    #[OA\Patch(path: "/empresas/{id}/validar", operationId: "validarEmpresa", summary: "Validar empresa (solo administración)", tags: ["Empresas"])]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "Empresa validada")]
    public function validar(int $empresa): JsonResponse
    {
        $model = Empresa::find($empresa);
        if (!$model) {
            return $this->errorResponse('Empresa no encontrada.', 404);
        }
        $model->update(['validado' => true]);
        return $this->successResponse(['message' => 'Empresa validada exitosamente.', 'data' => $model->fresh()]);
    }

    #[OA\Delete(path: "/empresas/{id}", operationId: "deleteEmpresa", summary: "Desactivar empresa", tags: ["Empresas"])]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "Empresa desactivada")]
    public function destroy(int $empresa): JsonResponse
    {
        $model = Empresa::find($empresa);
        if (!$model) {
            return $this->errorResponse('Empresa no encontrada.', 404);
        }
        $model->update(['activo' => false]);
        return $this->successResponse(['message' => 'Empresa desactivada exitosamente.']);
    }
}