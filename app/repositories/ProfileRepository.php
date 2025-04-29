<?php

namespace App\Repositories;

use App\Config\Database;
use App\Models\Profile;

class ProfileRepository
{
  private $table = "profile";

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
    return array_map(fn($row) => new Profile($row), $results);
  }

  public function getById($id)
  {
    $query = "SELECT * FROM \"$this->table\" WHERE id = :id";
    $stmt = $this->pdo->prepare($query);
    $stmt->bindParam(":id", $id);
    $stmt->execute();
    $result = $stmt->fetch() ?: null;
    return $result ? new Profile($result) : null;
  }

  public function getByUser($user_id)
  {
    $query = "SELECT * FROM \"$this->table\" WHERE user_id = :user_id";
    $stmt = $this->pdo->prepare($query);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();
    $result = $stmt->fetch() ?: null;
    return $result ? new Profile($result) : null;
  }

  public function create(Profile $profile)
  {
    if ($this->getByUser($profile->user_id)) return false;
    $query = "INSERT INTO \"$this->table\" (id, user_id, lastname, firstname, phone, picture, gender, userlanguage, google, linkedin) VALUES (:id, :user_id, :lastname, :firstname, :phone, :picture, :gender, :userlanguage, :google, :linkedin)";
    $stmt = $this->pdo->prepare($query);

    $stmt->bindParam(":id", $profile->id);
    $stmt->bindParam(":user_id", $profile->user_id);
    $stmt->bindParam(":lastname", $profile->lastname);
    $stmt->bindParam(":firstname", $profile->firstname);
    $stmt->bindParam(":phone", $profile->phone);
    $stmt->bindParam(":picture", $profile->picture);
    $stmt->bindParam(":gender", $profile->gender);
    $stmt->bindParam(":userlanguage", $profile->userlanguage);
    $stmt->bindParam(":google", $profile->google);
    $stmt->bindParam(":linkedin", $profile->linkedin);

    if ($stmt->execute()) {
      return true;
    }
    return false;
  }

  public function update(Profile $profile)
  {
    $query = "UPDATE \"$this->table\" SET firstname = :firstname, lastname = :lastname, gender = :gender, userlanguage = :userlanguage WHERE id = :id";
    $stmt = $this->pdo->prepare($query);

    $stmt->bindParam(":id", $profile->id);
    $stmt->bindParam(":firstname", $profile->firstname);
    $stmt->bindParam(":lastname", $profile->lastname);
    $stmt->bindParam(":gender", $profile->gender);
    $stmt->bindParam(":userlanguage", $profile->userlanguage);

    if ($stmt->execute()) {
      return true;
    }
    return false;
  }

  public function updateGoogle(string $id, string $google)
  {
    $query = "UPDATE  \"$this->table\" SET google = :google WHERE id = :id";
    $stmt = $this->pdo->prepare($query);

    $stmt->bindParam(":id", $id);
    $stmt->bindParam(":google", $google);

    if ($stmt->execute()) {
      return true;
    }
    return false;
  }

  public function updateLinkedin(string $id, string $linkedin)
  {
    $query = "UPDATE  \"$this->table\" SET linkedin = :linkedin WHERE id = :id";
    $stmt = $this->pdo->prepare($query);

    $stmt->bindParam(":id", $id);
    $stmt->bindParam(":linkedin", $linkedin);

    if ($stmt->execute()) {
      return true;
    }
    return false;
  }

  public function updatePhone(string $id, string $phone)
  {
    $query = "UPDATE  \"$this->table\" SET phone = :phone WHERE id = :id";
    $stmt = $this->pdo->prepare($query);

    $stmt->bindParam(":id", $id);
    $stmt->bindParam(":phone", $phone);

    if ($stmt->execute()) {
      return true;
    }
    return false;
  }
  public function updatePicture(string $id, string $picture)
  {
    $query = "UPDATE \"$this->table\" SET picture = :picture WHERE id = :id";
    $stmt = $this->pdo->prepare($query);

    $stmt->bindParam(":id", $id);
    $stmt->bindParam(":picture", $picture);

    if ($stmt->execute()) {
      return true;
    }
    return false;
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
