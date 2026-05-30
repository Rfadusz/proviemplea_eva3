<?php

namespace App\Http\Controllers;

use App\Models\Persona;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

class PersonaController extends Controller
{
    #[OA\Get(path: "/personas", operationId: "getPersonas", summary: "Listar personas (CV ciego)", description: "Obtiene talentos activos en formato de CV ciego.", tags: ["Personas"])]
    #[OA\Response(response: 200, description: "Listado exitoso")]
    public function index(Request $request): JsonResponse
    {
        $query = Persona::where('activo', true);

        if ($request->has('validado')) {
            $query->where('validado', $request->boolean('validado'));
        }
        if ($request->has('nivel_educacional')) {
            $query->where('nivel_educacional', $request->input('nivel_educacional'));
        }

        return $this->successResponse($query->get()->map(fn($p) => $p->getCvCiego()));
    }

    #[OA\Post(path: "/personas", operationId: "createPersona", summary: "Registrar nueva persona/talento", description: "Crea un perfil de talento con código autogenerado.", tags: ["Personas"])]
    #[OA\Response(response: 201, description: "Persona creada")]
    #[OA\Response(response: 422, description: "Errores de validación")]
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email'                => 'required|email|unique:personas,email',
            'telefono'             => 'nullable|string|max:15',
            'resumen'              => 'nullable|string',
            'nivel_educacional'    => 'nullable|in:basica,media,tecnica,universitaria,postgrado',
            'titulo_carrera'       => 'nullable|string',
            'anio_egreso'          => 'nullable|integer|min:1950|max:' . date('Y'),
            'anios_experiencia'    => 'nullable|integer|min:0',
            'areas_experiencia'    => 'nullable|array',
            'competencias'         => 'nullable|array',
            'rango_renta'          => 'nullable|string',
            'tipo_jornada'         => 'nullable|in:completa,part-time,por-horas',
            'modalidad'            => 'nullable|in:presencial,remoto,hibrido',
            'cursos'               => 'nullable|array',
            'idiomas'              => 'nullable|array',
            'portafolio_url'       => 'nullable|url',
            'persona_discapacidad' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Los datos enviados no son válidos.', 422, $validator->errors()->toArray());
        }

        $data = $validator->validated();
        $data['codigo_talento'] = $this->generarCodigoTalento();
        $data['porcentaje_completitud'] = $this->calcularCompletitud($data);

        return $this->successResponse(Persona::create($data), 201);
    }

    #[OA\Get(path: "/personas/{id}", operationId: "getPersona", summary: "Obtener persona por ID", tags: ["Personas"])]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "Persona encontrada")]
    #[OA\Response(response: 404, description: "No encontrada")]
    public function show(int $persona): JsonResponse
    {
        $model = Persona::find($persona);
        if (!$model) {
            return $this->errorResponse('Persona no encontrada.', 404);
        }
        return $this->successResponse($model);
    }

    #[OA\Put(path: "/personas/{id}", operationId: "updatePersona", summary: "Actualizar persona", tags: ["Personas"])]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "Persona actualizada")]
    public function update(Request $request, int $persona): JsonResponse
    {
        $model = Persona::find($persona);
        if (!$model) {
            return $this->errorResponse('Persona no encontrada.', 404);
        }

        $validator = Validator::make($request->all(), [
            'email'                => 'sometimes|email|unique:personas,email,' . $model->id,
            'telefono'             => 'nullable|string|max:15',
            'resumen'              => 'nullable|string',
            'nivel_educacional'    => 'nullable|in:basica,media,tecnica,universitaria,postgrado',
            'titulo_carrera'       => 'nullable|string',
            'anio_egreso'          => 'nullable|integer|min:1950|max:' . date('Y'),
            'anios_experiencia'    => 'nullable|integer|min:0',
            'areas_experiencia'    => 'nullable|array',
            'competencias'         => 'nullable|array',
            'rango_renta'          => 'nullable|string',
            'tipo_jornada'         => 'nullable|in:completa,part-time,por-horas',
            'modalidad'            => 'nullable|in:presencial,remoto,hibrido',
            'cursos'               => 'nullable|array',
            'idiomas'              => 'nullable|array',
            'portafolio_url'       => 'nullable|url',
            'persona_discapacidad' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Los datos enviados no son válidos.', 422, $validator->errors()->toArray());
        }

        $data = $validator->validated();
        $data['porcentaje_completitud'] = $this->calcularCompletitud(array_merge($model->toArray(), $data));
        $model->update($data);

        return $this->successResponse($model->fresh());
    }

    #[OA\Patch(path: "/personas/{id}/validar", operationId: "validarPersona", summary: "Validar persona (solo administración)", tags: ["Personas"])]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "Persona validada")]
    public function validar(int $persona): JsonResponse
    {
        $model = Persona::find($persona);
        if (!$model) {
            return $this->errorResponse('Persona no encontrada.', 404);
        }
        $model->update(['validado' => true]);
        return $this->successResponse(['message' => 'Persona validada exitosamente.', 'data' => $model->fresh()]);
    }

    #[OA\Delete(path: "/personas/{id}", operationId: "deletePersona", summary: "Desactivar persona", tags: ["Personas"])]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "Persona desactivada")]
    public function destroy(int $persona): JsonResponse
    {
        $model = Persona::find($persona);
        if (!$model) {
            return $this->errorResponse('Persona no encontrada.', 404);
        }
        $model->update(['activo' => false]);
        return $this->successResponse(['message' => 'Persona desactivada exitosamente.']);
    }

    private function generarCodigoTalento(): string
    {
        do {
            $codigo = 'PROV-' . date('Y') . '-' . strtoupper(Str::random(4));
        } while (Persona::where('codigo_talento', $codigo)->exists());
        return $codigo;
    }

    private function calcularCompletitud(array $data): int
    {
        $campos = ['email','telefono','resumen','nivel_educacional','titulo_carrera',
                   'anio_egreso','anios_experiencia','competencias','rango_renta',
                   'tipo_jornada','modalidad'];
        $completados = count(array_filter($campos, fn($c) => !empty($data[$c])));
        return (int) round(($completados / count($campos)) * 100);
    }
}