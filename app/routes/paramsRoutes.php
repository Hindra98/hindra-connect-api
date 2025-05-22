<?php

use App\Controllers\ParamsController;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {

  $app->group('/api', function (RouteCollectorProxy $group) {
    $group->post('/update-params', [ParamsController::class, 'updateParams']);
    $group->post('/update-notification', [ParamsController::class, 'updateNotification']);
  });

  // $app->group('/api', function (RouteCollectorProxy $group) {
  //   $group->post('/update-params', [ParamsController::class, 'updateParams']);
  //   $group->post('/update-notification', [ParamsController::class, 'updateNotification']);
  // })->add(new AuthMiddleware());

};
