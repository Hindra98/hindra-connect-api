<?php

namespace App\Middlewares;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class CorsMiddleware
{
  public function __invoke(Request $request, RequestHandler $handler)
  {
    
    // Gérer la pré-requête OPTIONS
    if ($request->getMethod() === 'OPTIONS') {
      $response = new \Slim\Psr7\Response();
      return $this->addCorsHeaders($request, $response);
    }

    // Traiter la requête normale
    $response = $handler->handle($request);
    return $this->addCorsHeaders($request, $response);
  }

  private function addCorsHeaders(Request $request, Response $response)
  {
    // Liste des origines autorisées (ajoutez vos URLs de développement/production)
    $allowedOrigins = [
      '*',
      'http://localhost:5173',        // React en développement en local
      'http://192.168.137.154:5173',        // React en développement mais virtuellement accessible
      'http://192.168.137.4:5173',        // React en développement mais virtuellement accessible
      'https://hindra-exchange-service.vercel.app/',     // Votre domaine en production sur Vercel
      'https://hindra-exchange-service.pages.dev/',     // Votre domaine en production sur CloudFlare
      'https://hindra-connect-api.onrender.com/',     // Votre domaine en production sur Render
    ];

    // $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    $origin = $request->getHeaderLine('Origin');
    $origin = in_array($origin, $allowedOrigins) ? $origin : $allowedOrigins[0];
    // $origin = '*'; // Autoriser toutes les origines (pour le développement uniquement)
    if (in_array($origin, $allowedOrigins))
      return $response
        ->withHeader('Access-Control-Allow-Origin', $origin)
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, User-Agent, Cookie, Accept-Language, X-Api-Key, X-Correlation-Id, Content-Type, Content-Length, Accept, Origin, Authorization, Set-Cookie, Cookie, Refresh-Token, New-Access-Token')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS')
        ->withHeader('Access-Control-Allow-Credentials', 'true')
        ->withHeader('Access-Control-Max-Age', '86400'); // Cache pendant 24h
    return $response;
  }
}
