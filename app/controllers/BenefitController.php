<?php

namespace App\Controllers;

use App\Config\LoggerApi;
use App\Core\Services\EmailService;
use App\Core\Utils\JWTCore;
use App\Core\Utils\ResponseFormatter;
use App\Models\Benefit;
use App\Models\User;
use App\Repositories\BenefitRepository;
use App\Repositories\UserRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class BenefitController
{

  private $userModel;
  private $benefitModel;
  private $logger;

  public function __construct()
  {
    $this->userModel = new UserRepository();
    $this->benefitModel = new BenefitRepository();
    $this->logger = new LoggerApi();
  }

  public function getAll(Request $_, Response $response, $args)
  {
    $data = $this->benefitModel->getAll();
    if (count($data) < 1) return ResponseFormatter::error($response, 404, ["Aucune prestation disponible"]);

    $payload = ["message" => "Categories disponibles", ["benefits" => $data]];
    return ResponseFormatter::format($response, 200, [], $payload);
  }
  public function getOne(Request $_, Response $response, $args)
  {
    $id = $args['id'];
    $data = $this->benefitModel->getById($id);
    if (!$data) return ResponseFormatter::error($response, 404, ["Prestation inexistante"]);
    $payload = ["message" => "Informations sur la prestation", ["benefit" => $data]];
    return ResponseFormatter::format($response, 200, [], $payload);
  }

  public function create(Request $request, Response $response, $_)
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

    $user = $this->userModel->getById($decoded->userId);
    if ($user == null) return ResponseFormatter::format($response, 401, ["Token d'authentification compromis! Reessayez de vous connecter!"], $payload);


    $parsedBody = $request->getParsedBody();
    $title = $parsedBody['title'] ?? "";
    $description = $parsedBody['description'] ?? "";
    $price = $parsedBody['price'] ?? "";
    $location = $parsedBody['location'] ?? "";
    $category_id = $parsedBody['category_id'] ?? "";
    $availability = $parsedBody['availability'] ?? "";

    if ($price == '') array_push($error, "Vous devez renseigner un prix valide");
    if ($title == '') array_push($error, "Vous devez renseigner un titre valide");
    if ($category_id == '') array_push($error, "Vous devez renseigner une catégorie valide");
    if ($availability == '') array_push($error, "Vous devez renseigner une disponibilité valide");

    if (count($error) == 0) {
      $id = uniqid();
      $data = [
        'id' => $id,
        'user_id' => $user->id,
        'title' => $title,
        'description' => $description,
        'price' => $price,
        'location' => $location,
        'category_id' => $category_id,
        'availability' => $availability,
      ];
      $benefit = new Benefit($data);
      if ($this->benefitModel->create($benefit)) {
        $payload = ["message" => "Prestation créé avec succès"];
        $payload = array_merge($payload, $data);
        $status = 201;
      } else {
        $payload = ["message" => ""];
        array_push($error, "Erreur lors de la creation du prestation");
        $status = 500;
      }
    } else {
      array_push($error, "Erreur lors de la création");
      $payload = ["message" => ""];
      $status = 500;
    }
    return ResponseFormatter::format($response, $status, $error, $payload);
  }

  public function updateBenefitTitle(Request $request, Response $response, $_)
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

    $user = $this->userModel->getById($decoded->userId);
    if ($user == null) return ResponseFormatter::format($response, 401, ["Token d'authentification compromis! Reessayez de vous connecter!"], $payload);


    $parsedBody = $request->getParsedBody();
    $id = $parsedBody['id'] ?? "";
    $title = $parsedBody['title'] ?? "";
    $description = $parsedBody['description'] ?? "";
    $price = $parsedBody['price'] ?? "";

    if (strlen($id) < 1) {
      // $error[] = "";
      array_push($error, "Prestation non valide");
    }

    if ($price == '') array_push($error, "Vous devez renseigner un prix valide");
    if ($title == '') array_push($error, "Vous devez renseigner un titre valide");

    if (count($error) == 0) {
      $data = [
        'id' => $id,
        'title' => $title,
        'description' => $description,
        'price' => $price,
      ];
      if ($this->benefitModel->updateBenefitTitle($id, $title, $description, $price)) {
        $payload = ["message" => "Prestation mis à jour avec succès"];
        $payload = array_merge($payload, $data);
        $status = 200;
      } else {
        $payload = ["message" => ""];
        array_push($error, "Erreur lors de la mise à jour du prestation");
        $status = 500;
      }
    } else {
      array_push($error, "Erreur lors de la mise à jour");
      $payload = ["message" => ""];
      $status = 500;
    }
    return ResponseFormatter::format($response, $status, $error, $payload);
  }

  public function updateBenefitCategory(Request $request, Response $response, $_)
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

    $user = $this->userModel->getById($decoded->userId);
    if ($user == null) return ResponseFormatter::format($response, 401, ["Token d'authentification compromis! Reessayez de vous connecter!"], $payload);


    $parsedBody = $request->getParsedBody();
    $id = $parsedBody['id'] ?? "";
    $location = $parsedBody['location'] ?? "";
    $category_id = $parsedBody['category_id'] ?? "";
    $availability = $parsedBody['availability'] ?? "";

    if (strlen($id) < 1) {
      // $error[] = "";
      array_push($error, "Prestation non valide");
    }

    if ($category_id == '') array_push($error, "Vous devez renseigner une catégorie valide");
    if ($availability == '') array_push($error, "Vous devez renseigner une disponibilité valide");

    if (count($error) == 0) {
      $data = [
        'id' => $id,
        'location' => $location,
        'category_id' => $category_id,
        'availability' => $availability,
      ];
      if ($this->benefitModel->updateBenefitCategory($id, $location, $category_id, $availability)) {
        $payload = ["message" => "Prestation mis à jour avec succès"];
        $payload = array_merge($payload, $data);
        $status = 200;
      } else {
        $payload = ["message" => ""];
        array_push($error, "Erreur lors de la mise à jour du prestation");
        $status = 500;
      }
    } else {
      array_push($error, "Erreur lors de la mise à jour");
      $payload = ["message" => ""];
      $status = 500;
    }
    return ResponseFormatter::format($response, $status, $error, $payload);
  }

  public function delete(Request $request, Response $response, $args)
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

    $user = $this->userModel->getById($decoded->userId);
    if ($user == null) return ResponseFormatter::format($response, 401, ["Token d'authentification compromis! Reessayez de vous connecter!"], $payload);

    $id = $args['id'];
    if (strlen($id) < 1) array_push($error, "Identifiant non valide");

    if (count($error) == 0) {
      $benefit = $this->benefitModel->getById($id);
      if (!$benefit) {
        array_push($error, "Prestation inexistant");
        $status = 404;
      } else {
        $del_benefit = $this->benefitModel->delete($id);
        if ($del_benefit) {
          $status = 204;
          $accessToken = $jwtCore->generateTokenWithClaims($user, 900); // 15 min
          $payload = ['token' => $accessToken, "message" => "Prestation supprimée avec success"];
        } else array_push($error, "Erreur lors de la suppression de la prestation");
      }
    } else array_push($error, "Une erreur est survenue lors de la suppression de la prestation");

    return ResponseFormatter::format($response, $status, $error, $payload);
  }
}
