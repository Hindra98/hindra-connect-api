<?php

use App\Controllers\UserController;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {

  $app->group('/api', function (RouteCollectorProxy $group) {
    $group->get('/profile', [UserController::class, 'getAll']);
    $group->get('/profile/{id}', [UserController::class, 'getOne']);
    // $group->post('/profile', [UserController::class, 'create']);
    $group->put('/profile', [UserController::class, 'update']);
    $group->delete('/profile/{id}', [UserController::class, 'delete']);
  });

  // $app->group('/api', function (RouteCollectorProxy $group) {
  //   $group->get('/profile', [UserController::class, 'getAll']);
  //   $group->get('/profile/{id}', [UserController::class, 'getOne']);
  //   $group->post('/profile', [UserController::class, 'create']);
  //   $group->put('/profile', [UserController::class, 'update']);
  //   $group->delete('/profile/{id}', [UserController::class, 'delete']);
  // })->add(new AuthMiddleware());
};
