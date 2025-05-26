<?php

namespace App\Core\Services;

use finfo;
use Slim\Psr7\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile as UploadedFileSS;
use Psr\Http\Message\ServerRequestInterface as Request;

class FileUploader
{
  private function isValid(UploadedFile $file)
  {
    $isValid = false;
    $payload = null;

    $img = ['jpg', 'jpeg', 'png', 'svg', 'webp', 'gif', 'ico', 'tiff', 'tif', 'bmp', 'heic', 'heif', 'avif'];
    $vid = ['mp4', 'avi', 'flv', 'mpeg', '3gp', 'webm', 'm4v', 'mov', 'mkv', 'wmv', 'h264', 'hevc', 'ogv', 'vob', 'mpg', 'ogv', 'vob', 'mts', 'x-flv', 'x-msvideo', 'x-matroska', 'mp2t', 'quicktime', 'x-m4v', 'x-ms-wmv'];
    $aud = ['mp3', 'wav', 'aac', 'ogg', 'flac', 'wma', 'mid', 'opus', 'm4a', 'aiff', 'mpeg', 'mp4', 'midi', 'x-ms-wma', 'x-m4a', 'webm'];
    $doc = ['pdf', 'docx', 'doc', 'xlsx', 'xls', 'txt', 'rtf', 'odt', 'epub', 'csv', 'pptx', 'ppt', 'mobi'];
    $docMimeType = ['text/plain', 'text/csv', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/msword', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/vnd.ms-powerpoint', 'application/vnd.oasis.opendocument.text', 'application/pdf', 'application/rtf', 'application/epub+zip', 'application/x-mobipocket-ebook'];

    if ($file->getError() !== UPLOAD_ERR_OK) return ['isValid' => $isValid, 'error' => "Erreur lors du transfert du fichier", 'payload' => $payload];

    $extension = strtolower(pathinfo($file->getClientFilename(), PATHINFO_EXTENSION));
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $detectedMime = $finfo->file($file->getFilePath());
    // $detectedMime = $finfo->file($file->getClientFilename());
    if (str_contains($detectedMime, 'image') && in_array($extension, $img)) { // Image
      $payload = "img";
      if (!in_array(mb_split("/", $detectedMime)[1], $img)) return ['isValid' => $isValid, 'error' => "Image non supporté", 'payload' => $payload];
    } elseif (str_contains($detectedMime, 'video') && in_array($extension, $vid)) { // Video
      $payload = "vid";
      if (!in_array(mb_split("/", $detectedMime)[1], $vid)) return ['isValid' => $isValid, 'error' => "Video non supporté", 'payload' => $payload];
    } elseif ((str_contains($detectedMime, 'audio') || $detectedMime == 'application/ogg') && in_array($extension, $aud)) { // Audio
      $payload = "aud";
      if (!in_array(mb_split("/", $detectedMime)[1], $aud)) return ['isValid' => $isValid, 'error' => "Audio non supporté", 'payload' => $payload];
    } elseif (in_array($extension, $doc)) { // Document
      $payload = "doc";
      if (!in_array($detectedMime, $docMimeType)) return ['isValid' => $isValid, 'error' => "Document non supporté", 'payload' => $payload];
    } else return ['isValid' => $isValid, 'error' => "Format de fichier invalide", 'payload' => $payload];

    return ['isValid' => true, 'error' => null, 'payload' => "hc-$payload-"];
  }
  public function upload(Request $request, UploadedFile $file, string $directory, string $id)
  {
    // $originalFilename = pathinfo($file->getClientFilename(), PATHINFO_FILENAME);
    $isValid = $this->isValid($file);
    if (!$isValid['isValid']) return ['isValid' => false, 'error' => $isValid['error'], 'payload' => null];

    $fileName = $isValid['payload'] . $id . uniqid();
    $extension = pathinfo($file->getClientFilename(), PATHINFO_EXTENSION);
    $fileName = strtolower($fileName . '.' . $extension);
    try {
      $file->moveTo($directory . DIRECTORY_SEPARATOR . $fileName);
    } catch (FileException $e) {
      // ... handle exception if something happens during file upload
      return ['isValid' => false, 'error' => $e->getMessage(), 'payload' => null];
    }
    $serverParams = $request->getServerParams();
    $host = $serverParams['REQUEST_SCHEME'] . '://' . $serverParams['HTTP_HOST'];
    $uris = mb_split('/', $serverParams['REQUEST_URI']);
    array_pop($uris);
    $uri = join("/", $uris);
    $fileName = $host . $uri . '/files/' . $fileName;
    return ['isValid' => true, 'error' => null, 'payload' => $fileName];
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
