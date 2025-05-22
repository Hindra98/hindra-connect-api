<?php

use App\Controllers\ProfileController;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {

  $app->group('/api', function (RouteCollectorProxy $group) {
    $group->get('/profile', [ProfileController::class, 'getAll']);
    $group->post('/profile', [ProfileController::class, 'create']);
    $group->get('/my-profile', [ProfileController::class, 'getOne']);
    $group->post('/update-profile', [ProfileController::class, 'updateProfile']);
    $group->post('/update-phone', [ProfileController::class, 'updatePhone']);
    // $group->post('/update-picture', [ProfileController::class, 'updatePicture']);
    $group->post('/update-website', [ProfileController::class, 'updateWebsite']);
    $group->delete('/profile', [ProfileController::class, 'delete']);
  });

  // $app->group('/api', function (RouteCollectorProxy $group) {
  //   $group->get('/profile', [ProfileController::class, 'getAll']);
  //   $group->get('/profile/{id}', [ProfileController::class, 'getOne']);
  //   $group->post('/profile', [ProfileController::class, 'create']);
  //   $group->post('/profile', [ProfileController::class, 'update']);
  //   $group->delete('/profile/{id}', [ProfileController::class, 'delete']);
  // })->add(new AuthMiddleware());
};
