<?php

namespace App\Models;

class Category
{
  public string $id;
  public string $title;
  public ?string $picture;
  public ?string $description;
  public string $created_at;
  public string $updated_at;

  public function __construct(array $data)
  {
    $this->id = $data['id'] ?? "";
    $this->title = $data['title'] ?? "";
    $this->picture = $data['picture'] ?? "";
    $this->description = $data['description'] ?? "";
    $this->created_at = $data['created_at'] ?? time();
    $this->updated_at = $data['updated_at'] ?? time();
  }
}
