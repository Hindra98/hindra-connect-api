<?php

namespace App\Models;

class Params
{
  public string $id;
  public string $user_id;
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
    $this->theme = $data['theme'] ?? "system";
    $this->userlanguage = $data['userlanguage'] ?? "fr";
    $this->notify_email = $data['notify_email'] ?? 1;
    $this->notify_phone = $data['notify_phone'] ?? 0;
    $this->notify_in_app = $data['notify_in_app'] ?? 0;
    $this->updated_at = $data['updated_at'] ?? time();
  }
}
