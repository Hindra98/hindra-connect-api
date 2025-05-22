<?php

namespace App\Controllers;

use App\Config\LoggerApi;
use App\Core\Services\EmailService;
use App\Core\Services\SecurePassword;
use App\Core\Services\ValidateService;
use App\Core\Utils\JWTCore;
use App\Core\Utils\ResponseFormatter;
use App\Models\Params;
use App\Models\Profile;
use App\Models\User;
use App\Repositories\ParamsRepository;
use App\Repositories\ProfileRepository;
use App\Repositories\UserRepository;
use League\OAuth2\Client\Provider\Google;
use League\OAuth2\Client\Provider\LinkedIn;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthController
{
  private $userModel;
  private $logger;

  public function __construct()
  {
    $this->userModel = new UserRepository();
    $this->logger = new LoggerApi();
  }
  public function login(Request $request, Response $response)
  {
    $params = $request->getParsedBody();
    $email = $params['email'] ?? "";
    $password = $params['password'] ?? "";

    $payload = ["message" => ""];
    $jwtCore = new JWTCore();
    if (strlen($email) < 1 || strlen($password) < 1) return ResponseFormatter::format($response, 401, ["Identifiants manquants"], $payload);
    $user = $this->userModel->getByEmail($email);
    $verifyPassword = (new SecurePassword())->verifyPassword($password, $user->password ?? "");
    // $verifyPassword = password_verify($password, $user->password ?? "");

    if (!$user || $verifyPassword != 1) {
      return ResponseFormatter::format($response, 401, ["Identifiants incorrects"], $payload);
    } else {
      if ($user->is_verified == 0) {

        $otp = rand(100000, 999999); // Génère un code à 6 chiffres
        $this->userModel->updateOTP($user->id, $otp);

        $data_verif_registration = ['id' => $user->id, 'otp' => $otp];
        $jwtCore = new JWTCore();
        $accessToken = $jwtCore->generateToken($data_verif_registration, 900); // 15 min

        $message_email = "token=$accessToken&otp=$otp";

        $send_mail = EmailService::send($user, "Hindra-Connect - Lien de vérification",  $message_email, EMAIL_RENDER_VERIFY_EMAIL);
        $payload = ['email' => $user->email, 'is_verified' => $user->is_verified == 1, "message" => 'Votre compte n\'est pas vérifié! ' . $send_mail["message_email"]];
        return ResponseFormatter::format($response, 200, [], $payload);
      }
      // $this->userModel->updateUserConnected($user->id, true);
      $data = ['id' => $user->id, 'email' => $user->email, 'is_verified' => $user->is_verified == 1, 'is_verify_2fa' => $user->is_verify_2fa == 1, 'is_connected' => true, 'role' => $user->role];
      if ($user->is_verify_2fa) {

        $otp = rand(100000, 999999); // Génère un code à 6 chiffres
        $this->userModel->updateOTP($user->id, $otp);

        $send_mail = EmailService::send($user, "Votre code de vérification",  $otp, EMAIL_RENDER_SEND_OTP);

        $accessToken = $jwtCore->generateToken($data, 900); // 15 min
        $refreshToken = $jwtCore->generateToken($data, 604800); // 7 jours
        $payload = ['token' => $accessToken, 'refresh_token' => $refreshToken];
        $payload = array_merge($payload, $send_mail);
        $payload = array_merge($payload, $data);
        return ResponseFormatter::format($response, 200, [], $payload);
      } else {

        $accessToken = $jwtCore->generateTokenWithClaims($user, 900); // 15 min
        $refreshToken = $jwtCore->generateTokenWithClaims($user, 604800); // 7 jours

        $payload = ['token' => $accessToken, 'refresh_token' => $refreshToken];
        $payload = array_merge($payload, $data);
        return ResponseFormatter::format($response, 200, [], $payload)
          ->withHeader('Set-Cookie', "refresh_token={$refreshToken}; HttpOnly; Secure; SameSite=Strict");
      }
    }
  }
  public function register(Request $request, Response $response)
  {

    $error = [];
    $status = 401;
    $payload = null;
    $parsedBody = $request->getParsedBody();
    $is_verify_2fa = $parsedBody['is_verify_2fa'] == "true" ? 1 : 0;
    $email = $parsedBody['email'] ?? "";
    $lastName = $parsedBody['lastName'] ?? "";
    $firstName = $parsedBody['firstName'] ?? "";
    $password = $parsedBody['password'] ?? "";
    $confirmPassword = $parsedBody['confirmPassword'] ?? "";
    $userlanguage = $parsedBody['userlanguage'] ?? "fr";
    $theme = $parsedBody['theme'] ?? "system";

    if ($confirmPassword != $password) array_push($error, "Le mot de passe et la confirmation sont differents");

    if ($password == '') array_push($error, "Vous devez renseigner un mot de passe");
    if ($lastName == '') array_push($error, "Vous devez renseigner un nom");
    if ($firstName == '') array_push($error, "Vous devez renseigner un prenom");

    if (!(ValidateService::validEmail($email))) array_push($error, "Vous devez renseigner une adresse e-mail valide");

    $payload = ["message" => ""];
    if (count($error) == 0) {
      $id = uniqid();
      $hashPassword = (new SecurePassword())->hashPassword($password);
      $data = ['id' => $id, 'email' => $email, 'password' => $hashPassword, 'is_verify_2fa' => $is_verify_2fa == 1];
      $user = new User($data);
      $createUser = $this->userModel->create($user);
      switch ($createUser) {
        case 0: // Erreur survenue lors de la creation
          array_push($error, "Une erreur est survenue lors de la creation de cet utilisateur");
          break;
        case 1: // Ok

          // Insertion des parametres de langue et de mode (theme)
          $paramsModel = new ParamsRepository();
          $paramsId = uniqid();
          $dataParams = ['id' => $paramsId, 'user_id' => $user->id, 'userlanguage' => $userlanguage, 'theme' => $theme];
          $params = new Params($dataParams);
          $createParams = $paramsModel->create($params);

          // Insertion du profil utilisateur
          $profileModel = new ProfileRepository();
          $profileId = uniqid();
          $dataProfile = ['id' => $profileId, 'user_id' => $user->id, 'lastname' => $lastName, 'firstname' => $firstName];
          $profile = new Profile($dataProfile);
          $createProfile = $profileModel->create($profile);

          $otp = rand(100000, 999999); // Génère un code à 6 chiffres
          $this->userModel->updateOTP($user->id, $otp);

          $data_verif_registration = ['id' => $user->id, 'otp' => $otp];
          $jwtCore = new JWTCore();
          $accessToken = $jwtCore->generateToken($data_verif_registration, 900); // 15 min

          $message_email = "token=$accessToken&otp=$otp";

          $send_mail = EmailService::send($user, "Inscription sur Hindra-Connect - Lien de vérification",  $message_email, EMAIL_RENDER_VERIFY_EMAIL, $params->userlanguage);

          $status = 201;
          $payload = ["message" => ($createProfile && $createParams) ? "Utilisateur enregistre | " . $send_mail["message_email"] : "Toutes vos données n'ont pas été enregistrés, vous le ferez manuellement plutard"];
          break;
        case 2: // Doublon d'adresse e-mail
          array_push($error, "Cette adresse e-mail est deja utilise");
          break;
        default:
      }
    }
    return ResponseFormatter::format($response, $status, $error, $payload);
  }

  public function verifyIdentity(Request $request, Response $response)
  {
    $params = $request->getParsedBody();
    $id = $params['id'] ?? "";
    $otp = $params['otp'] ?? "";
    $token = $params['token'] ?? "";
    $error = [];
    $status = 401;
    $payload = null;

    if ($otp == '') array_push($error, "Vous devez renseigner le code envoye par mail");
    if ($token == '' || $id == '') array_push($error, "Echec de connexion::Identifiants manquants! Reessayez de vous connecter!");


    $payload = ["message" => ""];
    if (count($error) == 0) {
      $jwtCore = new JWTCore();
      $decoded = $jwtCore->decodeToken($token);
      if ($decoded == null) return ResponseFormatter::format($response, 401, ["Echec de connexion::Identifiants invalides! Reessayez de vous connecter!"], $payload);

      $user = $this->userModel->getById($id);
      if ($user == null || $id != $decoded->id) return ResponseFormatter::format($response, 401, ["Echec de connexion! Reessayez de vous connecter!"], $payload);
      if (!$this->userModel->verifyOTP($user->id, $otp)) return ResponseFormatter::format($response, 401, ["Code pin errone"], $payload);


      $data = ['id' => $user->id, 'email' => $user->email, 'is_verified' => $user->is_verified == 1, 'is_verify_2fa' => $user->is_verify_2fa == 1, 'is_connected' => true, 'role' => $user->role];
      $accessToken = $jwtCore->generateTokenWithClaims($user, 900); // 15 min
      $refreshToken = $jwtCore->generateTokenWithClaims($user, 604800); // 7 jours
      $payload = ['token' => $accessToken, 'refresh_token' => $refreshToken, "message" => "Authentification reussie"];
      $payload = array_merge($payload, $data);
      return ResponseFormatter::format($response, 200, [], $payload)
        ->withHeader('Set-Cookie', "refresh_token={$refreshToken}; HttpOnly; Secure; SameSite=Strict");
    }
    return ResponseFormatter::format($response, $status, $error, $payload);
  }

  public function resendPinCode(Request $request, Response $response)
  {
    $params = $request->getParsedBody();
    $id = $params['id'] ?? "";
    $email = $params['email'] ?? "";
    $token = $params['token'] ?? "";
    $type = $params['type'] ?? 0;
    $error = [];
    $status = 401;
    $payload = null;

    if ($token == '' || $id == '' || $email == '') array_push($error, "Identifiants manquants! Reessayez de vous connecter!");

    $payload = ["message" => ""];
    if (count($error) == 0) {
      $jwtCore = new JWTCore();
      $decoded = $jwtCore->decodeToken($token);
      if ($decoded == null) return ResponseFormatter::format($response, 401, ["Identifiants invalides! Reessayez de vous connecter!"], $payload);

      $user = $this->userModel->getById($id);
      if ($user == null || $id != $decoded->id || $email != $decoded->email) return ResponseFormatter::format($response, 401, ["Echec de connexion! Reessayez de vous connecter!"], $payload);

      $otp = rand(100000, 999999); // Génère un code à 6 chiffres
      $this->userModel->updateOTP($user->id, $otp);
      $send_mail = EmailService::send($user, "Votre code de vérification",  $otp, EMAIL_RENDER_SEND_OTP);
      $data = ['id' => $user->id, 'email' => $user->email];

      if ($type === 1) {
        $accessToken = $jwtCore->generateTokenWithClaims($user, 900); // 15 min
        $refreshToken = $jwtCore->generateTokenWithClaims($user, 604800); // 7 jours
        $payload = ['token' => $accessToken, 'refresh_token' => $refreshToken, "message" => "Code pin renvoyé"];
        $payload = array_merge($payload, $send_mail);
        $payload = array_merge($payload, $data);
        return ResponseFormatter::format($response, 200, [], $payload)
          ->withHeader('Set-Cookie', "refresh_token={$refreshToken}; HttpOnly; Secure; SameSite=Strict");
      }

      $accessToken = $jwtCore->generateToken($data, 900); // 15 min
      $refreshToken = $jwtCore->generateToken($data, 604800); // 7 jours
      $payload = ['token' => $accessToken, 'refresh_token' => $refreshToken, "message" => "Code pin renvoyé"];
      $payload = array_merge($payload, $send_mail);
      $payload = array_merge($payload, $data);
      $status = 200;
    }
    return ResponseFormatter::format($response, $status, $error, $payload);
  }

  public function verifyRegistration(Request $request, Response $response)
  {
    $params = $request->getParsedBody();
    $token = $params['token'] ?? "";
    $otp = $params['otp'] ?? "";

    $payload = ["message" => ""];
    $jwtCore = new JWTCore();
    $decoded = $jwtCore->decodeToken($token);
    if (!$decoded) return ResponseFormatter::format($response, 401, ["Lien de reinitialisation expiré"], $payload);
    $otp_token = $decoded->otp;
    $id = $decoded->id;

    if ($otp != $otp_token) return ResponseFormatter::format($response, 401, ["Lien de reinitialisation invalide"], $payload);
    $user = $this->userModel->getById($id);
    if (!$user || !$this->userModel->verifyOTP($user->id, $otp)) {
      return ResponseFormatter::format($response, 401, ["Code pin errone"], $payload);
    } else {
      $this->userModel->updateVerifyRegistration($user->id, true);
      $payload = ["message" => "Felicitation et bienvenue à vous sur notre plateforme! Vous pouvez desormais vous connecter"];
      $this->userModel->updateOTP($user->id, "");
      return ResponseFormatter::format($response, 200, [], $payload);
    }
  }

  public function forgotPassword(Request $request, Response $response)
  {
    $params = $request->getParsedBody();
    $email = $params['email'] ?? "";
    $confirmEmail = $params['confirmEmail'] ?? "";
    $status = 401;
    $error = [];

    if ($confirmEmail != $email) array_push($error, "L'adresse email et la confirmation sont differentes");
    if ($email == '') array_push($error, "Vous devez renseigner une adresse email");
    if (!(ValidateService::validEmail($email))) array_push($error, "Vous devez renseigner une adresse e-mail valide");

    $payload = ["message" => ""];
    if (count($error) == 0) {
      $user = $this->userModel->getByEmail($email);
      if (!$user) return ResponseFormatter::format($response, $status, ["Identifiants incorrects"], $payload);
      else {

        $jwtCore = new JWTCore();

        $otp = rand(100000, 999999); // Génère un code à 6 chiffres
        $this->userModel->updateOTP($user->id, $otp);

        $data_forgot_password = ['id' => $user->id, 'otp' => $otp];
        $jwtCore = new JWTCore();
        $accessToken = $jwtCore->generateToken($data_forgot_password, 900); // 15 min

        $message_email = "token=$accessToken&otp=$otp";

        $send_mail = EmailService::send($user, "Hindra-Connect - Lien de réinitialisation",  $message_email, EMAIL_RENDER_RESET_PASSWORD);

        $status = 200;
        $payload = ["message" => $send_mail["message_email"]];
      }
    }
    return ResponseFormatter::format($response, $status, $error, $payload);
  }

  public function resetPassword(Request $request, Response $response)
  {
    $params = $request->getParsedBody();
    $token = $params['token'] ?? "";
    $otp = $params['otp'] ?? "";
    $password = $params['password'] ?? "";
    $confirmPassword = $params['confirmPassword'] ?? "";
    $error = [];

    if ($confirmPassword != $password) array_push($error, "Le mot de passe et la confirmation sont differents");
    if ($password == '') array_push($error, "Vous devez renseigner un mot de passe");

    $payload = ["message" => ""];
    if (count($error) > 0) return ResponseFormatter::format($response, 401, $error, $payload);
    $jwtCore = new JWTCore();
    $decoded = $jwtCore->decodeToken($token);
    if (!$decoded) return ResponseFormatter::format($response, 401, ["Lien de reinitialisation expiré"], $payload);
    $otp_token = $decoded->otp;
    $id = $decoded->id;

    if ($otp != $otp_token) return ResponseFormatter::format($response, 401, ["Lien de reinitialisation invalide"], $payload);
    $user = $this->userModel->getById($id);
    if (!$user || !$this->userModel->verifyOTP($user->id, $otp)) {
      return ResponseFormatter::format($response, 401, ["Code pin errone"], $payload);
    } else {
      $hashPassword = (new SecurePassword())->hashPassword($password);
      $this->userModel->updatePassword($user->id, $hashPassword);
      $payload = ["message" => "Mot de passe modifié! Connectez-vous!"];
      $this->userModel->updateOTP($user->id, "");
      return ResponseFormatter::format($response, 200, [], $payload);
    }
  }

  public function signOut(Request $request, Response $response)
  {
    $params = $request->getHeader("Authorization");
    $token = $params[0] ?? "";
    $payload = ["message" => ""];
    $jwtCore = new JWTCore();
    $decoded = $jwtCore->decodeToken($token);
    if (!$decoded) return ResponseFormatter::format($response, 401, ["Jeton expiré"], $payload);

    $handleChangeUserConnected = $this->userModel->updateUserConnected($decoded->id, false);
    if (!$handleChangeUserConnected) return ResponseFormatter::format($response, 401, ["Erreur lors de la deconnexion"], $payload);
    else {
      $payload = ["message" => "Vous etes a present deconnectes !"];
      return ResponseFormatter::format($response, 200, [], $payload);
    }
  }

  public function refreshToken(Request $request, Response $response)
  {
    $params = (array) $request->getParsedBody();
    $refreshToken = $params['refresh_token'] ?? '';

    $payload = ["message" => ""];
    if (!$refreshToken) {
      ResponseFormatter::format($response, 401, ["Refresh token manquant"], $payload);
    }

    try {
      $jwtCore = new JWTCore();
      $decoded = $jwtCore->decodeToken($refreshToken);
      $data = ['email' => $decoded->email];
      $newAccessToken = $jwtCore->generateToken($data, 900); // 15 min
      $payload = ['token' => $newAccessToken, 'refresh_token' => $refreshToken, "message" => "Rafraichissement du token reussi"];
      return ResponseFormatter::format($response, 200, [], $payload)->withHeader('Set-Cookie', "refresh_token={$refreshToken}; HttpOnly; Secure; SameSite=Strict");
    } catch (\Exception $e) {
      return ResponseFormatter::format($response, 401, ["Refresh token invalide", $e->getMessage()], $payload);
    }
  }
  public function loginWithGoogle(Request $request, Response $response)
  {
    $googleProvider = new Google([
      'clientId' => 'GOOGLE_CLIENT_ID',
      'clientSecret' => 'GOOGLE_CLIENT_SECRET',
      'redirectUri' => 'http://localhost:8080/api-exchange/public/api/google/callback',
    ]);
    $authUrl = $googleProvider->getAuthorizationUrl();
    return ResponseFormatter::format($response, 200, [], ["url" => $authUrl, "message" => ""]);
  }
  public function googleCallback(Request $request, Response $response)
  {
    $googleProvider = new Google([
      'clientId' => 'GOOGLE_CLIENT_ID',
      'clientSecret' => 'GOOGLE_CLIENT_SECRET',
      'redirectUri' => 'http://localhost:8080/api-exchange/public/api/google/callback',
    ]);
    $token = $googleProvider->getAccessToken('authorization_code', [
      'code' => $request->getQueryParams()['code']
    ]);

    $userGoogle = $googleProvider->getResourceOwner($token);
    // $email = $userGoogle->getEmail();
    $email = $userGoogle->getId();

    $user = $this->userModel->getByEmail($email);
    if (!$user) {
      $id = uniqid();
      $data = ['id' => $id, 'email' => $email, 'password' => "", 'is_verified' => true, 'is_connected' => 1];
      $user = new User($data);
      $this->userModel->create($user);
    }

    return ResponseFormatter::format($response, 200, [], ["user" => $user, "message" => "connexion reussie"]);
  }

  public function loginWithLinkedIn(Request $request, Response $response)
  {
    $linkedinProvider = new LinkedIn([
      'clientId' => 'LINKEDIN_CLIENT_ID',
      'clientSecret' => 'LINKEDIN_CLIENT_SECRET',
      'redirectUri' => 'http://localhost:8080/api-exchange/public/api/linkedin/callback',
    ]);
    $authUrl = $linkedinProvider->getAuthorizationUrl();
    return ResponseFormatter::format($response, 200, [], ["url" => $authUrl, "message" => ""]);
  }
  public function linkedinCallback(Request $request, Response $response)
  {
    $linkedinProvider = new LinkedIn([
      'clientId' => 'LINKEDIN_CLIENT_ID',
      'clientSecret' => 'LINKEDIN_CLIENT_SECRET',
      'redirectUri' => 'http://localhost:8080/api-exchange/public/api/linkedin/callback',
    ]);
    $token = $linkedinProvider->getAccessToken('authorization_code', [
      'code' => $request->getQueryParams()['code']
    ]);

    $userLinkedin = $linkedinProvider->getResourceOwner($token);
    // $email = $userLinkedin->getEmail();
    $email = $userLinkedin->getId();

    $user = $this->userModel->getByEmail($email);
    if (!$user) {
      $id = uniqid();
      $data = ['id' => $id, 'email' => $email, 'password' => "", 'is_verified' => true, 'is_connected' => 1];
      $user = new User($data);
      $this->userModel->create($user);
    }

    return ResponseFormatter::format($response, 200, [], ["user" => $user, "message" => "connexion reussie"]);
  }
}
