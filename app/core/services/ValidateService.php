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
  public static function validNumber(string $number)
  {
    return (bool) filter_var($number, FILTER_VALIDATE_INT);
  }
  public static function toCapitalize(string $word)
  {
    $words = array_map(function ($w) {
      $w[0] = strtoupper($w[0]);
      return $w;
    }, mb_split(" ", strtolower($word)));
    return join(" ", $words);
  }
}
