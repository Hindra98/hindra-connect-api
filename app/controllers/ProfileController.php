<?php

namespace App\Controllers;

use App\Config\LoggerApi;
use App\Core\Services\EmailService;
use App\Core\Services\FileUploader;
use App\Core\Services\ValidateService;
use App\Core\Utils\JWTCore;
use App\Core\Utils\ResponseFormatter;
use App\Models\Profile;
use App\Models\User;
use App\Repositories\ParamsRepository;
use App\Repositories\ProfileRepository;
use App\Repositories\UserRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ProfileController
{

  private $profileModel;
  private $userModel;
  private $logger;

  public function __construct()
  {
    $this->profileModel = new ProfileRepository();
    $this->userModel = new UserRepository();
    $this->logger = new LoggerApi();
  }

  public function getAll(Request $_, Response $response, $args)
  {
    $error = [];
    $status = 200;
    $payload =  $this->profileModel->getAll();

    if (!$payload) {
      $payload = ["message" => ""];
      array_push($error, "Aucun utilisateur trouvé");
      $status = 404;
    }

    return ResponseFormatter::format($response, $status, $error, $payload);
  }

  public function getAllProfileDatas(Request $_, Response $response, $args)
  {
    $error = [];
    $status = 200;
    $payload =  $this->profileModel->getAll();

    if (!$payload) {
      $payload = ["message" => ""];
      array_push($error, "Aucun utilisateur trouvé");
      $status = 404;
    }

    return ResponseFormatter::format($response, $status, $error, $payload);
  }
  public function getOne(Request $request, Response $response, $args)
  {

    $error = [];
    $status = 500;
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

    $profile = $this->profileModel->getByUser($user->id);
    if (!$profile) return ResponseFormatter::error($response, $status, ["Utilisateur inexistant"]);

    $params = (new ParamsRepository())->getByUser($user->id);
    if (!$params) return ResponseFormatter::error($response, $status, ["Utilisateur inexistant"]);

    $status = 200;

    $data = [
      'id' => $profile->id,
      'lastname' => $profile->lastname,
      'firstname' => $profile->firstname,
      'phone' => $profile->phone,
      'gender' => $profile->gender,
      'website' => $profile->website,
      'github' => $profile->github,
      'linkedin' => $profile->linkedin,
      'picture' => $profile->picture,
      'email' => $user->email,
      'role' => $user->role,
      'userlanguage' => $params->userlanguage,
      'theme' => $params->theme,
      'notify_email' => $params->notify_email == 1,
      'notify_phone' => $params->notify_phone == 1,
      'notify_in_app' => $params->notify_in_app == 1,
    ];
    $accessToken = $jwtCore->generateTokenWithClaims($user, 900); // 15 min
    $payload = ['token' => $accessToken, "message" => "Vos informations"];
    $payload = array_merge($payload, $data);
    return ResponseFormatter::format($response, $status, $error, $payload);
  }
  public function updateProfile(Request $request, Response $response, $_)
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
    $lastname = ValidateService::toCapitalize($parsedBody['lastname'] ?? "");
    $firstname = ValidateService::toCapitalize($parsedBody['firstname'] ?? "");
    $gender = ValidateService::toCapitalize($parsedBody['gender'] ?? "");

    if ($lastname == '') array_push($error, "Nom non renseigné");
    if ($firstname == '') array_push($error, "Prénom non renseigné");
    if ($gender == '') array_push($error, "Genre non renseigné");
    if (count($error) == 0) {
      $profile = $this->profileModel->getByUser($user->id);
      $data = ['id' => $profile->id, 'lastname' => $lastname, 'firstname' => $firstname, 'gender' => $gender];
      $updateProfileUser = $this->profileModel->updateProfile(new Profile($data));
      if ($updateProfileUser) {
        $accessToken = $jwtCore->generateTokenWithClaims($user, 900); // 15 min
        $payload = ['token' => $accessToken, "message" => "Profil modifié avec succès"];
        $payload = array_merge($payload, $data);
        $status = 200;
      } else array_push($error, "Erreur lors de la modification de votre profil");
    } else $status = 401;
    return ResponseFormatter::format($response, $status, $error, $payload);
  }
  public function updatePhone(Request $request, Response $response, $_)
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
    $phone = $parsedBody['phone'] ? str_replace(' ', '', $parsedBody['phone']) : "";
    $confirmPhone = $parsedBody['confirmPhone']  ? str_replace(' ', '', $parsedBody['confirmPhone']) : "";
    if ($phone == '') array_push($error, "Vous devez renseigner un numero de telephone valide");
    if (!(ValidateService::validNumber($phone))) array_push($error, "Vous devez renseigner un numero de telephone avec des chiffres valide");
    if ($confirmPhone != $phone) array_push($error, "Le numero de telephone et la confirmation sont differents");

    if (count($error) == 0) {
      $profile = $this->profileModel->getByUser($user->id);
      $data = ['phone' => $phone];
      $updateProfileUser = $this->profileModel->updatePhone($profile->id, $phone);
      if ($updateProfileUser) {
        $accessToken = $jwtCore->generateTokenWithClaims($user, 900); // 15 min
        $payload = ['token' => $accessToken, "message" => "Numero de telephone modifié avec succès"];
        $payload = array_merge($payload, $data);
        $status = 200;
      } else array_push($error, "Erreur lors de la modification de votre numero de telephone");
    } else $status = 401;
    return ResponseFormatter::format($response, $status, $error, $payload);
  }
  public function updatePicture(Request $request, Response $response, $_)
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
    $photoFile = $parsedBody['picture'] ?? "";

    if (strlen($photoFile) < 0) array_push($error, "Vous devez renseigner un numero de telepicture valide");

    if (count($error) == 0) {
      $fileUploader = new FileUploader();
      $profile = $this->profileModel->getByUser($user->id);
      $picture = $fileUploader->upload($photoFile, PICTURES_REPOSITORY);
      $data = ['picture' => $picture];
      $updateProfileUser = $this->profileModel->updatePicture($profile->id, $picture);
      if ($updateProfileUser) {
        $accessToken = $jwtCore->generateTokenWithClaims($user, 900); // 15 min
        $payload = ['token' => $accessToken, "message" => "Photo de profil modifiée avec succès"];
        $payload = array_merge($payload, $data);
        $status = 200;
      } else array_push($error, "Erreur lors de la modification de votre photo de profil");
    } else $status = 401;
    return ResponseFormatter::format($response, $status, $error, $payload);
  }

  public function updateWebsite(Request $request, Response $response, $_)
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
    $linkedin = $parsedBody['linkedin'] ?? "";
    $website = $parsedBody['website'] ?? "";
    $github = $parsedBody['github'] ?? "";

    if (strlen($linkedin) > 0 && (!(ValidateService::validURL($linkedin)))) array_push($error, "Adresse linkedin non valide");
    if (strlen($github) > 0 && (!(ValidateService::validURL($github)))) array_push($error, "Adresse github non valide");
    if (strlen($website) > 0 && (!(ValidateService::validURL($website)))) array_push($error, "Adresse url non valide");

    if (count($error) == 0) {
      $profile = $this->profileModel->getByUser($user->id);
      $data = ['linkedin' => $linkedin, 'website' => $website, 'github' => $github];
      $updateProfileUser = $this->profileModel->updateWebsite($profile->id, $linkedin, $github, $website);
      if ($updateProfileUser) {
        $accessToken = $jwtCore->generateTokenWithClaims($user, 900); // 15 min
        $payload = ['token' => $accessToken, "message" => "Liens externes modifiés avec succès"];
        $payload = array_merge($payload, $data);
        $status = 200;
      } else array_push($error, "Erreur lors de la modification de vos liens externes");
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
