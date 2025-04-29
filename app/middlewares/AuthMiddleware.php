<?php

namespace App\Middlewares;

use App\Config\LoggerApi;
use App\Core\Utils\JWTCore;
use App\Core\Utils\ResponseFormatter;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Predis\Client as Redis;

class AuthMiddleware
{
  private $redis;
  public function __construct()
  {
    $this->redis = new Redis();
  }
  public function __invoke(Request $request, RequestHandler $handler)
  {
    $response = new \Slim\Psr7\Response();
    $logger = new LoggerApi();
    $header = $request->getHeader('Authorization');
    $refreshToken = $request->getHeader('Refresh-Token')[0] ?? null;
    if (empty($header)) {
      $logger->getError($request);
      return ResponseFormatter::error($response, 401, ["Token manquant"]);
    }
    $token = $header[0];

    // if ($this->redis->exists("jwt:$token")) return $handler->handle($request);

    $jwtCore = new JWTCore();
    try {
      $decoded = $jwtCore->decodeToken($token);
      $request = $request->withAttribute('user', $decoded);
      // $this->redis->setex("jwt:$token", 900, json_encode($decoded)); // 15 min
      $logger->getInfo($request, ["", $decoded->email]);
      return $handler->handle($request);
    } catch (\Firebase\JWT\ExpiredException $e) {
      if (!$refreshToken) return ResponseFormatter::error($response, 401, ["Token expiré, veuillez vous reconnecter"]);
      try {
        $refreshDecoded = $jwtCore->decodeToken($refreshToken);
        $data = ['email' => $refreshDecoded->email];
        $newAccessToken = $jwtCore->generateToken($data, 900); // 15 min
        $decoded = $jwtCore->decodeToken($newAccessToken);
        // $this->redis->setex("jwt:$token", 900, json_encode($decoded)); // 15 min
        $request = $request->withAttribute('user', $refreshDecoded);
        return $handler->handle($request)->withHeader('New-Access-Token', $newAccessToken);
      } catch (\Exception $ex) {
        $logger->getError($request, ["Session expiré, veuillez vous reconnecter"]);
        return ResponseFormatter::error($response, 401, ["Session expiré, veuillez vous reconnecter", $e->getMessage(), $ex->getMessage()]);
      }
    } catch (\Exception $e) {
      $logger->getError($request, ["Token invalide"]);
      return ResponseFormatter::error($response, 401, ["Token invalide", $e->getMessage()]);
    }
  }
}
