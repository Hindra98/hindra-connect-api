<?php

namespace App\Middlewares;

use App\Config\LoggerApi;
use App\Core\Utils\JWTCore;
use App\Core\Utils\ResponseFormatter;
use App\Repositories\UserRepository;
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

    if (!$refreshToken) return ResponseFormatter::error($response, 401, ["Session expiré, veuillez vous reconnecter"]);
    $jwtCore = new JWTCore();
    try {
      $decoded = $jwtCore->decodeToken($token);
      $request = $request->withAttribute('user', $decoded);
      // $this->redis->setex("jwt:$token", 900, json_encode($decoded)); // 15 min
      $logger->getInfo($request, ["", $decoded->email]);
      if ($decoded != null) return $handler->handle($request); // Token encore valide :: Ligne à enlever
      $refreshDecoded = $jwtCore->decodeToken($refreshToken);
      $user = (new UserRepository())->getById($refreshDecoded->id);
      $newAccessToken = $jwtCore->generateTokenWithClaims($user, 900); // 15 min
      $newRefreshToken = $jwtCore->generateTokenWithClaims($user, 604800); // 7 jours

      // $this->redis->setex("jwt:$token", 900, json_encode($decoded)); // 15 min
      return $handler->handle($request)
      ->withHeader('Authorization', $newAccessToken)
      ->withHeader('New-Access-Token', $newAccessToken)
      ->withHeader('Set-Cookie', "refresh_token={$newRefreshToken}; HttpOnly; Secure; SameSite=Strict");
    } catch (\Exception $e) {
      $logger->getError($request, ["Session expiré, veuillez vous reconnecter"]);
      return ResponseFormatter::error($response, 401, ["Session expiré, veuillez vous reconnecter", $e->getMessage()]);
    }
  }
}
