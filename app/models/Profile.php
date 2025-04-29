<?php

namespace App\Models;

class Profile
{
  public string $id;
  public string $user_id;
  public string $lastname;
  public string $firstname;
  public ?string $picture;
  public ?string $phone;
  public ?string $google;
  public ?string $linkedin;
  public ?string $gender;
  public ?string $userlanguage;
  public string $updated_at;

  public function __construct(array $data)
  {
    $this->id = $data['id'] ?? "";
    $this->user_id = $data['user_id'] ?? "";
    $this->lastname = $data['lastname'] ?? "";
    $this->firstname = $data['firstname'] ?? "";
    $this->picture = $data['picture'] ?? null;
    $this->phone = $data['phone'] ?? null;
    $this->linkedin = $data['linkedin'] ?? null;
    $this->google = $data['google'] ?? null;
    $this->gender = $data['gender'] ?? null;
    $this->userlanguage = $data['userlanguage'] ?? "fr";
    $this->updated_at = $data['updated_at'] ?? time();
  }
}
