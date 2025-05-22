<?php

use App\Controllers\AuthController;
use Slim\App;

return function (App $app) {

  $app->post('/api/login', [AuthController::class, 'login']);
  $app->post('/api/register', [AuthController::class, 'register']);
  $app->post('/api/verify-identity', [AuthController::class, 'verifyIdentity']);
  $app->post('/api/resend-pincode', [AuthController::class, 'resendPinCode']);
  $app->post('/api/verify-registration', [AuthController::class, 'verifyRegistration']);
  $app->post('/api/forgot-password', [AuthController::class, 'forgotPassword']);
  $app->post('/api/reset-password', [AuthController::class, 'resetPassword']);
  $app->post('/api/sign-out', [AuthController::class, 'signOut']);
  $app->post('/api/refresh-token', [AuthController::class, 'refreshToken']);
};
