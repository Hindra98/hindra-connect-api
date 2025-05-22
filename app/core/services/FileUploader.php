<?php

namespace App\Core\Services;

use Slim\Psr7\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile as UploadedFileSS;

class FileUploader
{
  public function upload(UploadedFile $file, string $directory, string $id): string
  {
    // $originalFilename = pathinfo($file->getClientFilename(), PATHINFO_FILENAME);
    $fileName = 'HC-'.$id.'-'.uniqid();
    $extension = pathinfo($file->getClientFilename(), PATHINFO_EXTENSION);
    $fileName = strtolower($fileName . '.' . $extension);
    try {
      $file->moveTo($directory . DIRECTORY_SEPARATOR . $fileName);
    } catch (FileException $e) {
      // ... handle exception if something happens during file upload
      $e->getMessage();
    }
    return $fileName;
  }
  public function uploads(UploadedFileSS $file, string $directory): array
  {
    $vid = ['mp4', 'avi', 'flv', 'mpeg', '3gp', 'webm', 'm4v', 'mov', 'mkv', 'wmv'];
    $is_vid = false;
    $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
    $safeFilename = strtr($originalFilename, ' ', '-');
    $fileName = $safeFilename . '-' . uniqid();

    if (in_array($file->guessExtension(), $vid)) { // C'est une video
      $is_vid = true;
      $fileName = $fileName . '.webm';
    } else $fileName = $fileName . '.webp';

    $fileName = strtolower($fileName);
    try {
      $file->move($directory, $fileName);
    } catch (FileException $e) {
      // ... handle exception if something happens during file upload
      $e->getMessage();
    }
    return [$fileName, $is_vid];
  }
}
