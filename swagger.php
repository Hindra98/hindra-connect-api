<?php

use OpenApi\Generator;

require __DIR__ . './vendor/autoload.php';

/**
 * @OA\Info(title="Mon API", version="1.0.0")
 * @OA\Server(url="http://localhost:8080/api-exchange/public/api", description="Serveur en local")
 * @OA\Get(
 *  path="/api/test",
 *  summary="Recuperer toutes les prestataires",
 *  @OA\Response(response=200, description="Liste des prestataires")
 * )
 */
$openapi = Generator::scan([__DIR__ . '/app']);
header('Content-Type: application/json');
echo $openapi->toJson();
