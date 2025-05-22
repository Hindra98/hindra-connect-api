<?php

namespace App\Models;

class ProfileDatas
{
  public string $id;
  public string $user_id;
  public string $lastname;
  public string $firstname;
  public ?string $picture;
  public ?string $phone;
  public ?string $google;
  public ?string $linkedin;
  public ?string $website;
  public ?string $github;
  public ?string $gender;

  
  public string $email;
  public ?string $password;
  public ?int $is_verified;
  public ?int $is_verify_2fa;
  public ?int $is_connected;
  public ?string $created_at;
  public ?string $role; // "USER", "ADMIN"

  
  public ?string $theme;
  public ?string $userlanguage;
  public ?int $notify_email;
  public ?int $notify_phone;
  public ?int $notify_in_app;
  
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
    $this->website = $data['website'] ?? null;
    $this->github = $data['github'] ?? null;
    $this->google = $data['google'] ?? null;
    $this->gender = $data['gender'] ?? null;

    
    $this->email = $data['email'];
    $this->password = $data['password'];
    // $this->password = $data['password'] ?? "***************";
    $this->is_verified = $data['is_verified'] ?? 0;
    $this->is_verify_2fa = $data['is_verify_2fa'] ?? 0;
    $this->role = $data['role'] ?? "USER";
    $this->is_connected = $data['is_connected'] ?? 0;

    $this->theme = $data['theme'] ?? "system";
    $this->userlanguage = $data['userlanguage'] ?? "fr";
    $this->notify_email = $data['notify_email'] ?? 1;
    $this->notify_phone = $data['notify_phone'] ?? 0;
    $this->notify_in_app = $data['notify_in_app'] ?? 0;

    $this->updated_at = $data['updated_at'] ?? time();
  }
}
