<?php

use App\Controllers\UserController;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {

  $app->group('/api', function (RouteCollectorProxy $group) {
    $group->get('/user', [UserController::class, 'getAll']);
    $group->post('/update-email', [UserController::class, 'updateEmail']);
    $group->post('/update-otp-email', [UserController::class, 'updateOtpEmail']);
    $group->delete('/user', [UserController::class, 'delete']);
  });

  // $app->group('/api', function (RouteCollectorProxy $group) {
  //   $group->get('/user', [UserController::class, 'getAll']);
  //   $group->post('/update-email', [UserController::class, 'updateEmail']);
  //   $group->post('/update-otp-email', [UserController::class, 'updateOtpEmail']);
  //   $group->delete('/user', [UserController::class, 'delete']);
  // })->add(new AuthMiddleware());
  
};
