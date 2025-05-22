<?php

namespace App\Controllers;

use App\Config\LoggerApi;
use App\Core\Utils\JWTCore;
use App\Core\Utils\ResponseFormatter;
use App\Repositories\ParamsRepository;
use App\Repositories\UserRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ParamsController
{

  private $userModel;
  private $paramsModel;
  private $logger;

  public function __construct()
  {
    $this->userModel = new UserRepository();
    $this->paramsModel = new ParamsRepository();
    $this->logger = new LoggerApi();
  }

  public function updateParams(Request $request, Response $response, $_)
  {
    $error = [];
    $status = 200;
    $payload = ["message" => ""];
    $jwtCore = new JWTCore();

    $header = $request->getHeader('Authorization');
    if (empty($header)) {
      $this->logger->getError($request);
      return ResponseFormatter::error($response, 401, ["Token manquant"]);
    }

    $decoded = $jwtCore->decodeToken($header[0]);
    if ($decoded == null) return ResponseFormatter::format($response, 401, ["Identifiants invalides! Reessayez de vous connecter!"], $payload);

    $id = $decoded->userId;
    $user = $this->userModel->getById($id);
    if ($user == null) return ResponseFormatter::format($response, 401, ["Token d'authentification compromis! Reessayez de vous connecter!"], $payload);

    $parsedBody = $request->getParsedBody();
    $userlanguage = $parsedBody['userlanguage'] ?? "";
    $theme = $parsedBody['theme'] ?? "";

    if ($userlanguage == '') array_push($error, "Langue sélectionnée inconnue");
    if ($theme == '') array_push($error, "Theme non sélectionné");

    if (count($error) == 0) {
      $params = $this->paramsModel->getByUser($user->id);

      $this->paramsModel->updateParams($params->id, $userlanguage, $theme);
      $data = ['userlanguage' => $userlanguage, 'theme' => $theme];
      $accessToken = $jwtCore->generateTokenWithClaims($user, 900); // 15 min

      $payload = ['token' => $accessToken, "message" => "Modification de vos parametres reussie"];
      $payload = array_merge($payload, $data);
      $status = 200;
    } else $status = 401;
    return ResponseFormatter::format($response, $status, $error, $payload);
  }
  public function updateNotification(Request $request, Response $response, $_)
  {
    $error = [];
    $status = 200;
    $payload = ["message" => ""];
    $jwtCore = new JWTCore();

    $header = $request->getHeader('Authorization');
    if (empty($header)) {
      $this->logger->getError($request);
      return ResponseFormatter::error($response, 401, ["Token manquant"]);
    }

    $decoded = $jwtCore->decodeToken($header[0]);
    if ($decoded == null) return ResponseFormatter::format($response, 401, ["Identifiants invalides! Reessayez de vous connecter!"], $payload);

    $id = $decoded->userId;
    $user = $this->userModel->getById($id);
    if ($user == null) return ResponseFormatter::format($response, 401, ["Token d'authentification compromis! Reessayez de vous connecter!"], $payload);

    $parsedBody = $request->getParsedBody();
    $notify_email = $parsedBody['notify_email'] == "true" ? 1 : 0;
    $notify_in_app = $parsedBody['notify_in_app'] == "true" ? 1 : 0;
    $notify_phone = $parsedBody['notify_phone'] == "true" ? 1 : 0;

    if (count($error) == 0) {
      $params = $this->paramsModel->getByUser($user->id);

      $this->paramsModel->updateNotification($params->id, $notify_email, $notify_in_app, $notify_phone);
      $data = ['notify_email' => $notify_email == 1, 'notify_in_app' => $notify_in_app == 1, 'notify_phone' => $notify_phone == 1];
      $accessToken = $jwtCore->generateTokenWithClaims($user, 900); // 15 min

      $payload = ['token' => $accessToken, "message" => "Modification de vos parametres reussie"];
      $payload = array_merge($payload, $data);
      $status = 200;
    } else $status = 401;
    return ResponseFormatter::format($response, $status, $error, $payload);
  }
}
