<?php

use App\Middlewares\AuthMiddleware;
use Slim\App;

use OpenApi\Annotations as OA;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

return function (App $app) {

  $app->get('/swagger.json', function ($request, $response, $args) {
    $openapi = \OpenApi\Generator::scan([__DIR__ . '/app']);
    $response->getBody()->write($openapi->toJson());
    return $response->withHeader('Content-Type', 'application/json');
  });

  $app->get('/api/protected', function (Request $request, Response $response) {
    $user = $request->getAttribute('user');
    $response->getBody()->write(json_encode(['user' => $user]));
    return $response;
  })->add(new AuthMiddleware());

  $app->get('/api/test', function (Request $_, Response $response) {
    $response->getBody()->write("Test API fonctionne !");
    return $response;
  });
};
