<?php

namespace App\Controllers;

use App\Config\LoggerApi;
use App\Core\Services\EmailService;
use App\Core\Utils\JWTCore;
use App\Core\Utils\ResponseFormatter;
use App\Models\User;
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
  public function getOne(Request $_, Response $response, $args)
  {
    $error = [];
    $status = 200;
    $payload = null;
    $id = $args['id'];
    $jwtCore = new JWTCore();

    if (strlen($id) < 1) {
      // $error[] = "";
      array_push($error, "Identifiant non valide");
    }

    if(count($error) == 0) {
      $payload = $this->userModel->getById($id);
      $status = 200;
      if (!$payload) {
        $payload = ["message" => ""];
        array_push($error, "Utilisateur inexistant");
        $status = 404;
      }
    } else {
      array_push($error, "Erreur lors de la lecture des données de l'utilisateur");
      $payload = ["message" => ""];
      $status = 500;
    }
    
    $data = ['id' => $payload->id, 'email' => $payload->email, 'is_verified' => $payload->is_verified, 'is_connected' => $payload->is_connected, 'temps' => time(), 'temps2' => time()+900];
    $accessToken = $jwtCore->generateToken($data, 900); // 15 min
    $refreshToken = $jwtCore->generateToken($data, 604800); // 7 jours
    $payload = ['access_token' => $accessToken, 'refresh_token' => $refreshToken];
    $decoded = $jwtCore->decodeToken($accessToken);
    

    return ResponseFormatter::format($response, $status, $error, $payload);
  }

  // public function create(Request $request, Response $response, $_)
  // {
  //   $error = [];
  //   $status = 200;
  //   $payload = null;

  //   $parsedBody = $request->getParsedBody();
  //   $email = $parsedBody['email'] ?? "";
  //   $description = $parsedBody['description'] ?? "";
  //   $price = $parsedBody['price'] ?? "";
  //   $location = $parsedBody['location'] ?? "";
  //   $category_id = $parsedBody['category_id'] ?? "";
  //   $availability = $parsedBody['availability']??"";

  //   if ($price == '') array_push($error, "Vous devez renseigner un prix valide");
  //   if ($email == '') array_push($error, "Vous devez renseigner un titre valide");
  //   if ($category_id == '') array_push($error, "Vous devez renseigner une catégorie valide");
  //   if ($availability == '') array_push($error, "Vous devez renseigner une disponibilité valide");

  //   if (count($error) == 0) {
  //     $id = uniqid("ag");
  //     $data = [
  //       'id' => $id,
  //       'email' => $email,
  //       'description' => $description,
  //       'price' => $price,
  //       'location' => $location,
  //       'category_id' => $category_id,
  //       'availability' => $availability,
  //     ];
  //     $user = new User($data);
  //     if ($this->userModel->create($user)) {
  //       $payload = ["message" => "Utilisateur créé avec succès"];
  //       $status = 201;
  //     } else {
  //       $payload = ["message" => ""];
  //       array_push($error, "Erreur lors de la creation de l'utilisateur");
  //       $status = 500;
  //     }
  //   } else {
  //     array_push($error, "Erreur lors de la création");
  //     $payload = ["message" => ""];
  //     $status = 500;
  //   }
  //   return ResponseFormatter::format($response, $status, $error, $payload);
  // }

  public function update(Request $request, Response $response, $_)
  {
    $error = [];
    $status = 200;
    $payload = null;
    
    $header = $request->getHeader('Authorization');
    if (empty($header)) {
      $this->logger->getError($request);
      return ResponseFormatter::error($response, 401, ["Token manquant"]);
    }

    $parsedBody = $request->getParsedBody();
    $id = $parsedBody['id'] ?? "";
    $email = $parsedBody['email'] ?? "";
    $description = $parsedBody['description'] ?? "";
    $price = $parsedBody['price'] ?? "";
    $location = $parsedBody['location'] ?? "";
    $category_id = $parsedBody['category_id'] ?? "";
    $availability = $parsedBody['availability'] ?? "";

    if (strlen($id) < 1) {
      // $error[] = "";
      array_push($error, "Identifiant non valide");
    }

    if ($price == '') array_push($error, "Vous devez renseigner un prix valide");
    if ($email == '') array_push($error, "Vous devez renseigner un titre valide");
    if ($category_id == '') array_push($error, "Vous devez renseigner une catégorie valide");
    if ($availability == '') array_push($error, "Vous devez renseigner une disponibilité valide");


    if (count($error) == 0) {
      $data = [
        'id' => $id,
        'email' => $email,
        'location' => $location,
        'description' => $description,
        'price' => $price,
        'category_id' => $category_id,
        'availability' => $availability,
      ];
      $user = new User($data);
      if ($this->userModel->update($user)) {
        $payload = ["message" => "Utilisateur mis à jour avec succès"];
        $status = 201;
      } else {
        $payload = ["message" => ""];
        array_push($error, "Erreur lors de la mise à jour de l'utilisateur");
        $status = 500;
      }
    } else {
      array_push($error, "Erreur lors de la mise à jour");
      $payload = ["message" => ""];
      $status = 500;
    }
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
