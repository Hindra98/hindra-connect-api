<?php

use App\Controllers\BenefitController;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {

  $app->group('/api', function (RouteCollectorProxy $group) {
    $group->get('/benefits', [BenefitController::class, 'getAll']);
    $group->get('/benefits/{id}', [BenefitController::class, 'getOne']);
    $group->post('/benefits', [BenefitController::class, 'create']);
    $group->put('/benefits', [BenefitController::class, 'update']);
    $group->delete('/benefits/{id}', [BenefitController::class, 'delete']);
  });

  // $app->group('/api', function (RouteCollectorProxy $group) {
  //   $group->get('/benefits', [BenefitController::class, 'getAll']);
  //   $group->get('/benefits/{id}', [BenefitController::class, 'getOne']);
  //   $group->post('/benefits', [BenefitController::class, 'create']);
  //   $group->put('/benefits', [BenefitController::class, 'update']);
  //   $group->delete('/benefits/{id}', [BenefitController::class, 'delete']);
  // })->add(new AuthMiddleware());
};
