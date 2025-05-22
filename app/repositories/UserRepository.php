<?php

namespace App\Repositories;

use App\Config\Database;
use App\Models\User;
use DateTime;

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
    return $results == null ? [] : array_map(fn($row) => new User($row), $results);
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
    $query = "UPDATE \"$this->table\" SET is_verified = :is_verified, is_verify_2fa = :is_verify_2fa, is_connected = :is_connected, updated_at = :updated_at WHERE id = :id";
    $stmt = $this->pdo->prepare($query);

    $stmt->bindParam(":id", $user->id);
    $stmt->bindParam(":is_verified", $user->is_verified);
    $stmt->bindParam(":is_verify_2fa", $user->is_verify_2fa);
    $stmt->bindParam(":is_connected", $user->is_connected);
    $stmt->bindParam(":updated_at", ((new DateTime())->__serialize())['date']);

    if ($stmt->execute()) {
      return true;
    }
    return false;
  }

  public function updateEmail(string $id, string $email)
  {
    $query = "UPDATE  \"$this->table\" SET email = :email, updated_at = :updated_at WHERE id = :id";
    $stmt = $this->pdo->prepare($query);

    $stmt->bindParam(":id", $id);
    $stmt->bindParam(":email", $email);
    $stmt->bindParam(":updated_at", ((new DateTime())->__serialize())['date']);

    if ($stmt->execute()) {
      return true;
    }
    return false;
  }

  public function updateVerifyRegistration(string $id, int $is_verified)
  {
    $query = "UPDATE  \"$this->table\" SET is_verified = :is_verified, updated_at = :updated_at WHERE id = :id";
    $stmt = $this->pdo->prepare($query);

    $stmt->bindParam(":id", $id);
    $stmt->bindParam(":is_verified", $is_verified);
    $stmt->bindParam(":updated_at", ((new DateTime())->__serialize())['date']);

    if ($stmt->execute()) {
      return true;
    }
    return false;
  }

  public function updateUserConnected(string $id, int $is_connected)
  {
    $query = "UPDATE  \"$this->table\" SET is_connected = :is_connected, updated_at = :updated_at WHERE id = :id";
    $stmt = $this->pdo->prepare($query);

    $stmt->bindParam(":id", $id);
    $stmt->bindParam(":is_connected", $is_connected);
    $stmt->bindParam(":updated_at", ((new DateTime())->__serialize())['date']);

    if ($stmt->execute()) {
      return true;
    }
    return false;
  }

  public function updatePassword(string $id, string $password)
  {
    $query = "UPDATE \"$this->table\" SET password = :password, updated_at = :updated_at WHERE id = :id";
    $stmt = $this->pdo->prepare($query);

    $stmt->bindParam(":id", $id);
    $stmt->bindParam(":password", $password);
    $stmt->bindParam(":updated_at", ((new DateTime())->__serialize())['date']);

    if ($stmt->execute()) {
      return true;
    }
    return false;
  }

  public function updateOTP(string $id, string $otp)
  {
    $query = "UPDATE \"$this->table\" SET otp = :otp, updated_at = :updated_at WHERE id = :id";
    $stmt = $this->pdo->prepare($query);
    $stmt->bindParam(":id", $id);
    $stmt->bindParam(":otp", $otp);
    $stmt->bindParam(":updated_at", ((new DateTime())->__serialize())['date']);

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
