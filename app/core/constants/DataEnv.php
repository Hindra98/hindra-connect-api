<?php

namespace App\Core\Constants;

class DataEnv
{
  public function ParamsJWT()
  {
    return [
      'JWT_SECRET' => "7f58dcfb3a4a9b9be8e0ae0914e8738a9b4c9860f28b199b3f0c7d3f7b5e23d1",
      'JWT_HASH' => 'HS256' // ou RS256
    ];
  }
  public function ParamsDatabase()
  {
    return [
      'DB_HOST' => "localhost",
      'DB_NAME' => 'hindra-exchange',
      'DB_USER' => 'root',
      'DB_PASS' => '',
    ];
  }
  public function OAuthGoogle()
  {
    return [
      'GOOGLE_CLIENT_ID' => "",
      'GOOGLE_CLIENT_SECRET' => '',
    ];
  }
  public function OAuthLinkedin()
  {
    return [
      'LINKEDIN_CLIENT_ID' => "78cksln55ythia",
      'LINKEDIN_CLIENT_SECRET' => 'WPL_AP1.ePgxYmD2IiS7QvdE.hLyWbA==',
    ];
  }
}
