<?php

namespace App\Core\Services;

class SecurePassword
{
  /**
   * Hash un mot de passe en utilisant BCRYPT
   *
   * @param string $password Mot de passe en clair
   * @return string Le mot de passe hashé
   */
  public function hashPassword(string $password=""): string
  {
    // Options pour BCRYPT
    $options = [
      'cost' => 12, // Coût du hash (entre 10 et 12 est idéal)
  ];
    return password_hash($password, PASSWORD_BCRYPT, $options);
  }

  /**
   * Vérifie si un mot de passe correspond à son hash
   *
   * @param string $password Mot de passe en clair à vérifier
   * @param string $hash Hash stocké en base de données
   * @return bool True si le mot de passe est valide, false sinon
   */
  public function verifyPassword(string $password="", string $hash=""): bool
  {
    return password_verify($password, $hash);
  }
}