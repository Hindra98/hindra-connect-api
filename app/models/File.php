<?php
namespace App\Models;

class File
{
  public string $id;
  public string $user_id;
  public ?string $path;
  public ?string $type;
  public ?int $original_name;
  public ?int $mime_type;
  public ?int $size;
  public string $updated_at;

  public function __construct(array $data)
  {
    $this->id = $data['id'] ?? "";
    $this->user_id = $data['user_id'] ?? "";
    $this->path = $data['path'] ?? "system";
    $this->type = $data['type'] ?? "fr";
    $this->original_name = $data['original_name'] ?? 1;
    $this->mime_type = $data['mime_type'] ?? 0;
    $this->size = $data['size'] ?? 0;
    $this->updated_at = $data['updated_at'] ?? time();
  }
}