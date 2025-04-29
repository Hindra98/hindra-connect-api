<?php

namespace App\Controllers;

use App\Config\LoggerApi;
use App\Core\Services\EmailService;
use App\Core\Utils\ResponseFormatter;
use App\Models\Benefit;
use App\Models\User;
use App\Repositories\BenefitRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class BenefitController
{

  private $benefitModel;
  private $logger;

  public function __construct()
  {
    $this->benefitModel = new BenefitRepository();
    $this->logger = new LoggerApi();
  }

  public function getAll(Request $_, Response $response, $args)
  {
    $error = [];
    $status = 200;
    $payload =  $this->benefitModel->getAll();

    if ($payload) {
      $status = 200;
    } else {
      $payload = ["message" => ""];
      array_push($error, "Aucun prestation trouvé");
      $status = 404;
    }
    // $user = new User(['email'=> "test@hin.com"]);
    // $send_mail = EmailService::send($user, "Votre code de vérification",  987654, EMAIL_RENDER_RESET_PASSWORD);
    // array_push($payload, $send_mail);

    return ResponseFormatter::format($response, $status, $error, $payload);
  }
  public function getOne(Request $_, Response $response, $args)
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
      $payload = $this->benefitModel->getById($id);
      $status = 200;
      if (!$payload) {
        $payload = ["message" => ""];
        array_push($error, "Prestation inexistant");
        $status = 404;
      }
    } else {
      array_push($error, "Erreur lors de la lecture des données du prestation");
      $payload = ["message" => ""];
      $status = 500;
    }

    return ResponseFormatter::format($response, $status, $error, $payload);
  }

  public function create(Request $request, Response $response, $_)
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
      $id = uniqid("ag");
      $data = [
        'id' => $id,
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

  public function update(Request $request, Response $response, $_)
  {
    $error = [];
    $status = 200;
    $payload = null;
    $parsedBody = $request->getParsedBody();
    $id = $parsedBody['id'] ?? "";
    $title = $parsedBody['title'] ?? "";
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
    if ($title == '') array_push($error, "Vous devez renseigner un titre valide");
    if ($category_id == '') array_push($error, "Vous devez renseigner une catégorie valide");
    if ($availability == '') array_push($error, "Vous devez renseigner une disponibilité valide");


    if (count($error) == 0) {
      $data = [
        'id' => $id,
        'title' => $title,
        'location' => $location,
        'description' => $description,
        'price' => $price,
        'category_id' => $category_id,
        'availability' => $availability,
      ];
      $benefit = new Benefit($data);
      if ($this->benefitModel->update($benefit)) {
        $payload = ["message" => "Prestation mis à jour avec succès"];
        $status = 201;
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
      $benefit = $this->benefitModel->getById($id);
      if (!$benefit) {
        $payload = ["message" => ""];
        array_push($error, "Prestation inexistant");
        $status = 404;
      } else {
        $del_benefit = $this->benefitModel->delete($id);
        if ($del_benefit) {
          $status = 204;
          $payload = ["message" => "Prestation supprimé avec success"];
        } else {
          $payload = ["message" => ""];
          array_push($error, "Erreur lors de la suppression du prestation");
          $status = 404;
        }
      }
    } else {
      array_push($error, "Une erreur est survenue lors de la suppression du prestation");
      $payload = ["message" => ""];
      $status = 500;
    }

    return ResponseFormatter::format($response, $status, $error, $payload);
  }
}
