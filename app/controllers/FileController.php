<?php

namespace App\Controllers;

use App\Config\LoggerApi;
use App\Core\Services\FileUploader;
use App\Core\Utils\JWTCore;
use App\Core\Utils\ResponseFormatter;
use App\Repositories\ProfileRepository;
use App\Repositories\UserRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Factory\StreamFactory;

class FileController
{
  private $streamFactory;

  private $profileModel;
  private $userModel;
  private $logger;

  public function __construct()
  {
    $this->profileModel = new ProfileRepository();
    $this->userModel = new UserRepository();
    $this->logger = new LoggerApi();
    $this->streamFactory = new StreamFactory();
  }

  public function updatePicture(Request $request, Response $response, $_)
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

    $uploadedFiles = $request->getUploadedFiles();
    $parsedBody = $request->getParsedBody();


    $photoFile = $uploadedFiles['picture'];
    $fileType = $parsedBody['destination'] ?? '';

    if (empty($uploadedFiles['picture'])) array_push($error, "Aucun fichier uploadé");
    if (!in_array($fileType, ['profile', 'benefit'])) array_push($error, "Une erreur est survenue lors de la modification de votre profil");

    if (count($error) == 0) {
      $fileUploader = new FileUploader();
      $profile = $this->profileModel->getByUser($user->id);

      try {
        $fileName = 'HC-'.$id.'-'.$fileType[0].'-'.uniqid();
        $extension = pathinfo($photoFile->getClientFilename(), PATHINFO_EXTENSION);
        $fileName = $fileName . '.' . $extension;
        $directory = PICTURES_REPOSITORY.$fileType.'s'; // profiles/ ou benefits/
        $directory = $directory . DIRECTORY_SEPARATOR . $fileName;

        $picture = $fileUploader->upload($photoFile, $directory, $id.'-'.$fileType[0]);
        $data = ['picture' => $picture];
        $updateProfileUser = $this->profileModel->updatePicture($profile->id, $picture);
        
        if ($updateProfileUser) {
          $accessToken = $jwtCore->generateTokenWithClaims($user, 900); // 15 min
          $payload = ['token' => $accessToken, "message" => "Photo de profil modifiée avec succès"];
          $payload = array_merge($payload, $data);
          $status = 200;
        } else array_push($error, "Erreur lors de la modification de votre photo de profil");
        return ResponseFormatter::format($response, $status, $error, $payload);
      } catch (\Exception $e) {
        return ResponseFormatter::format($response, 500, ["Erreur lors du traitement du fichier: " . $e], $payload);
      }
    } else $status = 401;
    return ResponseFormatter::format($response, $status, $error, $payload);
  }

  public function getFile(Request $_, Response $response, $args): Response
  {

    try {
      $directory = PICTURES_REPOSITORY.'profiles\\'.$args['id'];
      $stream = $this->streamFactory->createStreamFromFile($directory);
      $extension = mb_split('.', $args['id']);
      $ext = $extension[count($extension)-1];

      return $response->withBody($stream)->withHeader('Content-Length', $stream->getSize())->withHeader('Content-Type', $ext);
    } catch (\Exception $e) {
      return ResponseFormatter::format($response, 500, ["Erreur lors de la récupération du fichier"]);
    }
  }
}
