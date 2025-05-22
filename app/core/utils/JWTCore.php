<?php

namespace App\Core\Utils;

use App\Core\Constants\DataEnv;
use App\Models\User;
use App\Repositories\ParamsRepository;
use App\Repositories\ProfileRepository;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTCore
{
  public function __construct() {}

  public function generateToken(array $data, int $expiry = 60)
  {
    $jwtParams = (new DataEnv())->ParamsJWT();
    $tokenParams = [
      'iss' => 'Hindra98', // Émetteur
      'iat' => time(), // Date d'émission
      'exp' => time() + $expiry, // Expiration dans $expiry secondes
      'nbf' => time() + 5, // Non valide avant 5 secondes
    ];
    $data = array_merge($data, $tokenParams);
    $token = "Bearer " . JWT::encode($data, $jwtParams['JWT_SECRET'], $jwtParams['JWT_HASH']);
    return $token;
  }
  public function generateTokenWithClaims(User $user, int $exp = 60)
  {
    $profile = (new ProfileRepository())->getByUser($user->id);
    $params = (new ParamsRepository())->getByUser($user->id);
    $data = [
      'email' => $user->email,
      'userId' => $user->id,
      'role' => $user->role,
      'fullname' => $profile ? "$profile->firstname $profile->lastname" : "",
      'userlanguage' => $params->userlanguage ?? "fr",
      'exp' => (time() + $exp)
    ];
    return $this->generateToken($data, $exp);
  }
  public function decodeToken($token)
  {
    $token = str_replace('Bearer ', '', $token);
    $jwtParams = (new DataEnv())->ParamsJWT();
    try {
      $decoded = JWT::decode($token, new Key($jwtParams['JWT_SECRET'], $jwtParams['JWT_HASH']));
      return $decoded;
    } catch (ExpiredException $e) {
      return null; // Token expiré
    } catch (\Exception $e) {
      return null; // Token invalide
    }
  }
}
