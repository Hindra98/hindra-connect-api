<?php

namespace App\Repositories;

use App\Config\Database;
use App\Models\User;

class UserRepository
{
  private $table = "user";

  private $pdo;

  public function __construct()
  {
    $this->pdo = Database::getInstance();
  }

  public function getAll()
  {
    $query = "SELECT * FROM \"$this->table\"";
    $stmt = $this->pdo->query($query);
    $results = $stmt->fetchAll() ?: null;
    return array_map(fn($row) => new User($row), $results);
  }

  public function getById($id)
  {
    $query = "SELECT * FROM \"$this->table\" WHERE id = :id";
    $stmt = $this->pdo->prepare($query);
    $stmt->bindParam(":id", $id);
    $stmt->execute();
    $result = $stmt->fetch() ?: null;
    return $result ? new User($result) : null;
  }

  public function getByEmail($email)
  {
    $query = "SELECT * FROM \"$this->table\" WHERE email = :email";
    $stmt = $this->pdo->prepare($query);
    $stmt->bindParam(":email", $email);
    $stmt->execute();
    $result = $stmt->fetch() ?: null;
    return $result ? new User($result) : null;
  }

  public function create(User $user)
  {
    if ($this->getByEmail($user->email)) return 2;
    $query = "INSERT INTO \"$this->table\" (id, email, password, is_verified, is_verify_2fa, is_connected, role, otp) VALUES (:id, :email, :password, :is_verified, :is_verify_2fa, :is_connected, :role, :otp)";
    $stmt = $this->pdo->prepare($query);

    $stmt->bindParam(":id", $user->id);
    $stmt->bindParam(":email", $user->email);
    $stmt->bindParam(":password", $user->password);
    $stmt->bindParam(":is_verified", $user->is_verified);
    $stmt->bindParam(":is_verify_2fa", $user->is_verify_2fa);
    $stmt->bindParam(":is_connected", $user->is_connected);
    $stmt->bindParam(":role", $user->role);
    $stmt->bindParam(":otp", $user->otp);

    if ($stmt->execute()) {
      return 1;
    }
    return 0;
  }

  public function update(User $user)
  {
    $query = "UPDATE \"$this->table\" SET is_verified = :is_verified, is_verify_2fa = :is_verify_2fa, is_connected = :is_connected WHERE id = :id";
    $stmt = $this->pdo->prepare($query);

    $stmt->bindParam(":id", $user->id);
    $stmt->bindParam(":is_verified", $user->is_verified);
    $stmt->bindParam(":is_verify_2fa", $user->is_verify_2fa);
    $stmt->bindParam(":is_connected", $user->is_connected);

    if ($stmt->execute()) {
      return true;
    }
    return false;
  }

  public function updateEmail(string $id, string $email)
  {
    $query = "UPDATE  \"$this->table\" SET email = :email WHERE id = :id";
    $stmt = $this->pdo->prepare($query);

    $stmt->bindParam(":id", $id);
    $stmt->bindParam(":email", $email);

    if ($stmt->execute()) {
      return true;
    }
    return false;
  }

  public function updateVerifyRegistration(string $id, bool $is_verified)
  {
    $query = "UPDATE  \"$this->table\" SET is_verified = :is_verified WHERE id = :id";
    $stmt = $this->pdo->prepare($query);

    $stmt->bindParam(":id", $id);
    $stmt->bindParam(":is_verified", $is_verified);

    if ($stmt->execute()) {
      return true;
    }
    return false;
  }

  public function updateUserConnected(string $id, bool $is_connected)
  {
    $query = "UPDATE  \"$this->table\" SET is_connected = :is_connected WHERE id = :id";
    $stmt = $this->pdo->prepare($query);

    $stmt->bindParam(":id", $id);
    $stmt->bindParam(":is_connected", $is_connected);

    if ($stmt->execute()) {
      return true;
    }
    return false;
  }

  public function updatePassword(string $id, string $password)
  {
    $query = "UPDATE \"$this->table\" SET password = :password WHERE id = :id";
    $stmt = $this->pdo->prepare($query);

    $stmt->bindParam(":id", $id);
    $stmt->bindParam(":password", $password);

    if ($stmt->execute()) {
      return true;
    }
    return false;
  }

  public function updateOTP(string $id, string $otp)
  {
    $query = "UPDATE \"$this->table\" SET otp = :otp WHERE id = :id";
    $stmt = $this->pdo->prepare($query);

    $stmt->bindParam(":id", $id);
    $stmt->bindParam(":otp", $otp);

    if ($stmt->execute()) {
      return true;
    }
    return false;
  }

  public function verifyOTP(string $id, string $otp)
  {
    $query = "SELECT * FROM \"$this->table\" WHERE id = :id AND otp = :otp";
    $stmt = $this->pdo->prepare($query);
    $stmt->bindParam(":id", $id);
    $stmt->bindParam(":otp", $otp);
    $stmt->execute();
    $result = $stmt->fetch() ?: null;
    return $result !== null;
  }

  public function delete($id)
  {
    $query = "DELETE FROM \"$this->table\" WHERE id = :id";
    $stmt = $this->pdo->prepare($query);
    $stmt->bindParam(":id", $id);

    if ($stmt->execute()) {
      return true;
    }
    return false;
  }
}
