<?php

use App\Controllers\CategoryController;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {

  $app->group('/api', function (RouteCollectorProxy $group) {
    $group->get('/categories', [CategoryController::class, 'getAll']);
    $group->get('/category/{id}', [CategoryController::class, 'getOne']);
    $group->post('/category', [CategoryController::class, 'create']);
    $group->post('/update-category', [CategoryController::class, 'update']);
    $group->delete('/category/{id}', [CategoryController::class, 'delete']);
  });

  // $app->group('/api', function (RouteCollectorProxy $group) {
  //   $group->get('/categories', [CategoryController::class, 'getAll']);
  //   $group->get('/category/{id}', [CategoryController::class, 'getOne']);
  //   $group->post('/category', [CategoryController::class, 'create']);
  //   $group->post('/update-category', [CategoryController::class, 'update']);
  //   $group->delete('/category/{id}', [CategoryController::class, 'delete']);
  // })->add(new AuthMiddleware());
};
