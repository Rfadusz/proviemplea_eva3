<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Info(title: "ProviEmplea API", version: "1.0.0", description: "API REST para la plataforma de empleo ProviEmplea de Providencia.")]
#[OA\Server(url: "http://localhost:8080/api", description: "Servidor de desarrollo local")]
class HealthController extends Controller
{
    #[OA\Get(
        path: "/health",
        operationId: "healthCheck",
        summary: "Verificar estado del servicio",
        description: "Endpoint de observabilidad. Verifica que la API está disponible.",
        tags: ["Health"]
    )]
    #[OA\Response(
        response: 200,
        description: "Servicio operativo",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "online"),
                new OA\Property(property: "service", type: "string", example: "ProviEmplea API"),
                new OA\Property(property: "version", type: "string", example: "1.0.0")
            ]
        )
    )]
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'status'    => 'online',
            'service'   => 'ProviEmplea API',
            'version'   => '1.0.0',
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}