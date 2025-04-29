<?php

namespace App\Core\Services;

class ValidateService
{

  public static function validEmail(string $email)
  {
    return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
  }
  public static function validURL(string $url)
  {
    return (bool) preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $url);
  }
}
