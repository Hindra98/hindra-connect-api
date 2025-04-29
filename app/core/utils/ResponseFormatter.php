<?php

namespace App\Core\Utils;


use Psr\Http\Message\ResponseInterface as Response;
use Slim\Psr7\Response as SlimResponse;

class ResponseFormatter
{
  public function __construct() {}

  public function response($success, $response = null, array $error)
  {
    $errors = [];
    foreach($error as $err) {
      array_push($errors, ["errorMessage"=>$err]);
    }
    return ["hasSucceeded" => $success, "payload" => $response, "errorMessages" => $errors];
  }
  public static function format(Response $response, int $statusCode, array $error=[], $data = null)
  {
    
    $errors = [];
    foreach($error as $err) {
      array_push($errors, ["errorMessage"=>$err]);
    }
    $payload = [
      'hasSucceeded' => ($statusCode >= 200 && $statusCode < 300),
      'status' => $statusCode,
      'payload' => $data,
      'errorMessages' => array_reverse($errors)
    ];
    $response->getBody()->write(json_encode($payload));
    return $response
    ->withHeader('Content-Type', 'application/json');
    // ->withStatus($statusCode);
  }
  public static function error(SlimResponse $response, int $statusCode=401, array $error=[], $data = null)
  {
    
    $errors = [];
    foreach($error as $err) {
      array_push($errors, ["errorMessage"=>$err]);
    }
    $payload = [
      'hasSucceeded' => false,
      'status' => $statusCode,
      'payload' => $data,
      'errorMessages' => $errors
    ];
    $response->getBody()->write(json_encode($payload));
    return $response
    ->withHeader('Content-Type', 'application/json');
    // ->withStatus($statusCode);
  }
}
