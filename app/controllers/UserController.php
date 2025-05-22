<?php

namespace App\Controllers;

use App\Config\LoggerApi;
use App\Core\Services\EmailService;
use App\Core\Services\ValidateService;
use App\Core\Utils\JWTCore;
use App\Core\Utils\ResponseFormatter;
use App\Repositories\UserRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class UserController
{

  private $userModel;
  private $logger;

  public function __construct()
  {
    $this->userModel = new UserRepository();
    $this->logger = new LoggerApi();
  }

  public function getAll(Request $_, Response $response, $args)
  {
    $error = [];
    $status = 200;
    $payload =  $this->userModel->getAll();

    if ($payload) {
      $status = 200;
    } else {
      $payload = ["message" => ""];
      array_push($error, "Aucun utilisateur trouvé");
      $status = 404;
    }
    $user = $this->userModel->getById(2);
    $send_mail = EmailService::send($user, "Votre code de vérification",  987654, EMAIL_RENDER_RESET_PASSWORD);
    array_push($payload, $send_mail);

    return ResponseFormatter::format($response, $status, $error, $payload);
  }
  public function updateEmail(Request $request, Response $response, $_)
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
    $email = $parsedBody['email'] ?? "";
    $confirmEmail = $parsedBody['confirmEmail'] ?? "";

    if (!(ValidateService::validEmail($email))) array_push($error, "Vous devez renseigner une adresse e-mail valide");
    if ($email != $confirmEmail) array_push($error, "L'adresse email et la confirmation sont differentes");

    if (count($error) == 0) {

      $otp = rand(100000, 999999); // Génère un code à 6 chiffres
      $this->userModel->updateOTP($user->id, $otp);
      $send_mail = EmailService::send($user, "Votre code de vérification",  $otp, EMAIL_RENDER_SEND_OTP);
      $data = ['email' => $email];
      $accessToken = $jwtCore->generateTokenWithClaims($user, 900); // 15 min

      $payload = ['token' => $accessToken, "message" => "Verifiez vos mails"];
      $payload = array_merge($payload, $send_mail);
      $payload = array_merge($payload, $data);
      $status = 200;
    } else $status = 401;
    return ResponseFormatter::format($response, $status, $error, $payload);
  }
  public function updateOtpEmail(Request $request, Response $response, $_)
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
    $email = $parsedBody['email'] ?? "";
    $otp = $parsedBody['otp'] ?? "";

    if (!(ValidateService::validEmail($email))) array_push($error, "Vous devez renseigner une adresse e-mail valide");
    if ($otp == '') array_push($error, "Vous devez renseigner le code envoye par mail");
    
    if (count($error) == 0) {
      if (!$this->userModel->verifyOTP($user->id, $otp)) return ResponseFormatter::format($response, 401, ["Code pin errone"], $payload);

      $this->userModel->updateEmail($user->id, $email);
      $send_mail = EmailService::send($user, "Votre code de vérification",  "Adresse email modifié!", EMAIL_RENDER_RESET_EMAIL_ADDRESS);
      $data = [
        'email' => $email,
      ];
      $accessToken = $jwtCore->generateTokenWithClaims($user, 900); // 15 min

      $payload = ['token' => $accessToken, "message" => "Adresse email modifié!"];
      $payload = array_merge($payload, $data);
      $payload = array_merge($payload, $send_mail);
      $this->userModel->updateOTP($user->id, "");
      $status = 200;
    } else $status = 401;
    return ResponseFormatter::format($response, $status, $error, $payload);
  }

  public function delete(Request $_, Response $response, $args)
  {
    $error = [];
    $status = 200;
    $payload = null;
    $id = $args['id'];

    if (strlen($id) < 1) {
      // $error[] = "";
      array_push($error, "Identifiant non valide");
    }

    if (count($error) == 0) {
      $user = $this->userModel->getById($id);
      if (!$user) {
        $payload = ["message" => ""];
        array_push($error, "Utilisateur inexistant");
        $status = 404;
      } else {
        $del_user = $this->userModel->delete($id);
        if ($del_user) {
          $status = 204;
          $payload = ["message" => "Utilisateur supprimé avec success"];
        } else {
          $payload = ["message" => ""];
          array_push($error, "Erreur lors de la suppression de l'utilisateur");
          $status = 404;
        }
      }
    } else {
      array_push($error, "Une erreur est survenue lors de la suppression de l'utilisateur");
      $payload = ["message" => ""];
      $status = 500;
    }

    return ResponseFormatter::format($response, $status, $error, $payload);
  }
}
