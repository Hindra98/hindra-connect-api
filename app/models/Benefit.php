<?php

namespace App\Models;

class Benefit
{
  public string $id;
  public string $user_id;
  public string $category_id;
  public string $title;
  public ?string $location;
  public ?string $description;
  public int $price;
  public array $availability;
  public string $created_at;
  public string $updated_at;

  public function __construct($data)
  {
    $this->id = $data['id'];
    $this->user_id = $data['user_id'];
    $this->category_id = $data['category_id'];
    $this->title = $data['title'];
    $this->location = $data['location'];
    $this->description = $data['description'];
    $this->price = $data['price'];
    $this->availability = $data['availability'];
    $this->created_at = $data['created_at'];
    $this->updated_at = $data['updated_at'];
  }
}
