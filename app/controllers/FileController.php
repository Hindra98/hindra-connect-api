<?php
namespace App\Controllers;

use App\Models\File;
use Intervention\Image\ImageManager;
use League\Flysystem\Filesystem;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class FileController
{
    private $storage;
    private $db;
    private $imageManager;

    public function __construct(Filesystem $storage, \PDO $db)
    {
        $this->storage = $storage;
        $this->db = $db;
        $this->imageManager = new ImageManager(['driver' => 'gd']);
    }

    public function upload(Request $request, Response $response): Response
    {
        $userId = $request->getAttribute('userId');
        $uploadedFiles = $request->getUploadedFiles();
        $data = $request->getParsedBody();

        if (empty($uploadedFiles['file'])) {
            return $this->errorResponse($response, 'Aucun fichier uploadé', 400);
        }

        $file = $uploadedFiles['file'];
        $fileType = $data['file_type'] ?? '';

        // Validation
        if (!in_array($fileType, ['profile', 'service'])) {
            return $this->errorResponse($response, 'Type de fichier invalide', 400);
        }

        // Traitement du fichier
        try {
            $stream = $file->getStream();
            $mimeType = $file->getClientMediaType();
            $extension = pathinfo($file->getClientFilename(), PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $extension;
            $directory = $fileType . 's/'; // profiles/ ou services/

            // Optimisation de l'image si c'est une image
            if (strpos($mimeType, 'image/') === 0) {
                $image = $this->imageManager->make($stream);
                $image->resize(1200, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                $stream = $image->stream()->detach();
            }

            // Sauvegarde du fichier
            $this->storage->writeStream(
                $directory . $filename,
                $stream
            );

            // Enregistrement en base
            $fileModel = new File($this->db);
            $fileId = $fileModel->create([
                'user_id' => $userId,
                'path' => $directory . $filename,
                'type' => $fileType,
                'original_name' => $file->getClientFilename(),
                'mime_type' => $mimeType,
                'size' => $file->getSize()
            ]);

            return $this->successResponse($response, [
                'id' => $fileId,
                'path' => "/files/{$fileId}",
                'type' => $fileType
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse($response, 'Erreur lors du traitement du fichier', 500);
        }
    }

    public function getFile(Request $request, Response $response, array $args): Response
    {
        $fileModel = new File($this->db);
        $file = $fileModel->find($args['id']);

        if (!$file) {
            return $this->errorResponse($response, 'Fichier non trouvé', 404);
        }

        try {
            $stream = $this->storage->readStream($file['path']);
            
            $response = $response
                ->withHeader('Content-Type', $file['mime_type'])
                ->withHeader('Content-Length', $this->storage->fileSize($file['path']));
            
            $response->getBody()->write(stream_get_contents($stream));
            fclose($stream);
            
            return $response;
        } catch (\Exception $e) {
            return $this->errorResponse($response, 'Erreur lors de la récupération du fichier', 500);
        }
    }

    private function errorResponse(Response $response, string $message, int $code): Response
    {
        $response->getBody()->write(json_encode(['error' => $message]));
        return $response
            ->withStatus($code)
            ->withHeader('Content-Type', 'application/json');
    }

    private function successResponse(Response $response, array $data): Response
    {
        $response->getBody()->write(json_encode($data));
        return $response
            ->withStatus(200)
            ->withHeader('Content-Type', 'application/json');
    }
}