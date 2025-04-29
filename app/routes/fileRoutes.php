<?php

use App\Controllers\BenefitController;
use App\Controllers\FileController;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {

  $app->group('/api', function (RouteCollectorProxy $group) {
    $group->get('/files', [BenefitController::class, 'getAll']);
    $group->get('/files/{id}', [BenefitController::class, 'getOne']);
    $group->post('/files', [BenefitController::class, 'create']);
    $group->put('/files', [BenefitController::class, 'update']);
    $group->delete('/files/{id}', [BenefitController::class, 'delete']);
  });

  // Routes protégées
$app->group('/api', function ($group) {
  // Upload de fichier
  $group->post('/upload', [FileController::class, 'upload']);
  
  // Récupération de fichier
  $group->get('/files/{id}', [FileController::class, 'getFile']);
});
// })->add(new JwtMiddleware());

  // $app->group('/api', function (RouteCollectorProxy $group) {
  //   $group->get('/files', [BenefitController::class, 'getAll']);
  //   $group->get('/files/{id}', [BenefitController::class, 'getOne']);
  //   $group->post('/files', [BenefitController::class, 'create']);
  //   $group->put('/files', [BenefitController::class, 'update']);
  //   $group->delete('/files/{id}', [BenefitController::class, 'delete']);
  // })->add(new AuthMiddleware());
};
