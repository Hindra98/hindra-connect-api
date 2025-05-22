<?php

namespace App\Controllers;

use App\Config\LoggerApi;
use App\Core\Utils\JWTCore;
use App\Core\Utils\ResponseFormatter;
use App\Models\Category;
use App\Repositories\CategoryRepository;
use App\Repositories\UserRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CategoryController
{

  private $userModel;
  private $categoryModel;
  private $logger;

  public function __construct()
  {
    $this->userModel = new UserRepository();
    $this->categoryModel = new CategoryRepository();
    $this->logger = new LoggerApi();
  }
  public function getAll(Request $_, Response $response, $args)
  {
    $data = $this->categoryModel->getAll();
    if (count($data) < 1) return ResponseFormatter::error($response, 404, ["Aucune categorie disponible"]);

    $payload = ["message" => "Categories disponibles", ["categories" => $data]];
    return ResponseFormatter::format($response, 200, [], $payload);
  }

  public function getOne(Request $_, Response $response, $args)
  {

    $id = $args['id'];
    $data = $this->categoryModel->getById($id);
    if (!$data) return ResponseFormatter::error($response, 404, ["Categorie inexistante"]);

    $payload = ["message" => "Informations sur la categorie", ["category" => $data]];
    return ResponseFormatter::format($response, 200, [], $payload);
  }

  public function create(Request $request, Response $response, $args)
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
    $picture = $parsedBody['picture'] ?? "";
    $description = $parsedBody['description'] ?? "";

    if ($title == '') array_push($error, "Titre non defini");

    if (count($error) == 0) {
      $categoryId = uniqid();
      $data = ['id' => $categoryId, 'title' => $title, 'description' => $description, 'picture' => $picture];
      $category = new Category($data);
      $createCategory = $this->categoryModel->create($category);
      switch ($createCategory) {
        case 0: // Erreur survenue lors de la creation
          array_push($error, "Une erreur est survenue lors de la creation de cette categorie");
          break;
        case 1: // Ok
          $accessToken = $jwtCore->generateTokenWithClaims($user, 900); // 15 min
          $payload = ['token' => $accessToken, "message" => "Categorie enregistre"];
          $payload = array_merge($payload, $data);
          $status = 201;
          break;
        case 2: // Doublon de nom de categorie
          array_push($error, "Ce nom de categorie est deja utilise");
          break;
        default:
      }
    } else $status = 404;
    return ResponseFormatter::format($response, $status, $error, $payload);
  }

  public function update(Request $request, Response $response, $_)
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

    $user = $this->userModel->getById($decoded->userId);
    if ($user == null) return ResponseFormatter::format($response, 401, ["Token d'authentification compromis! Reessayez de vous connecter!"], $payload);

    $parsedBody = $request->getParsedBody();
    $id = $parsedBody['id'] ?? "";
    $title = $parsedBody['title'] ?? "";
    $picture = $parsedBody['picture'] ?? "";
    $description = $parsedBody['description'] ?? "";

    if ($title == '') array_push($error, "Titre non defini");
    if (!($this->categoryModel->getById($id))) return ResponseFormatter::format($response, 404, ["Categorie non selectionne!"], $payload);

    if (count($error) == 0) {
      $data = ['id' => $id, 'title' => $title, 'description' => $description, 'picture' => $picture];
      $category = new Category($data);
      $this->categoryModel->update($category);
      $accessToken = $jwtCore->generateTokenWithClaims($user, 900); // 15 min

      $payload = ['token' => $accessToken, "message" => "Modification de la categorie reussie"];
      $payload = array_merge($payload, $data);
      $status = 200;
    } else $status = 401;
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
      $category = $this->categoryModel->getById($id);
      if (!$category) {
        array_push($error, "Categorie inexistante");
        $status = 404;
      } else {
        $del_category = $this->categoryModel->delete($id);
        if ($del_category) {
          $status = 204;

          $accessToken = $jwtCore->generateTokenWithClaims($user, 900); // 15 min
          $payload = ['token' => $accessToken, "message" => "Categorie supprimée avec success"];
        } else array_push($error, "Erreur lors de la suppression de la categorie");
      }
    } else array_push($error, "Une erreur est survenue lors de la suppression de la categorie");


    return ResponseFormatter::format($response, $status, $error, $payload);
  }
}
