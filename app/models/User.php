<?php

namespace App\Models;

class User
{
  public string $id;
  public string $email;
  public ?string $password;
  public ?int $is_verified;
  public ?int $is_verify_2fa;
  public ?int $is_connected;
  public ?string $created_at;
  public ?string $role; // "USER", "ADMIN"
  public ?string $otp; // Champ pour stocker le code de double authentification

  public function __construct(array $data)
  {
    $this->id = $data['id'] ?? "";
    $this->email = $data['email'];
    $this->password = $data['password'];
    // $this->password = $data['password'] ?? "***************";
    $this->is_verified = $data['is_verified'] ?? 0;
    $this->is_verify_2fa = $data['is_verify_2fa'] ?? 0;
    $this->role = $data['role'] ?? "USER";
    $this->is_connected = $data['is_connected'] ?? 0;
    $this->created_at = $data['created_at'] ?? time();
    $this->otp = $data['otp'] ?? null;
  }
  public function getMaskedEmail(): string
  {
    $visible = 2;
    $email_array = mb_split("@", $this->email);
    $forward = substr($email_array[0], 0, $visible) . "*" * (strlen($email_array[0]) - $visible);
    $back_temp = mb_split(".", $email_array[1]);
    $backward = "*" * (strlen($back_temp[0]) - 1) . "." . $back_temp[1];
    return "$forward@$backward";
  }
}
