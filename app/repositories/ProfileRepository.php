<?php

namespace App\Repositories;

use App\Config\Database;
use App\Models\Profile;
use DateTime;

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
    return $results == null ? [] : array_map(fn($row) => new Profile($row), $results);
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
    $query = "INSERT INTO \"$this->table\" (id, user_id, lastname, firstname, phone, picture, gender, google, linkedin) VALUES (:id, :user_id, :lastname, :firstname, :phone, :picture, :gender, :google, :linkedin)";
    $stmt = $this->pdo->prepare($query);

    $stmt->bindParam(":id", $profile->id);
    $stmt->bindParam(":user_id", $profile->user_id);
    $stmt->bindParam(":lastname", $profile->lastname);
    $stmt->bindParam(":firstname", $profile->firstname);
    $stmt->bindParam(":phone", $profile->phone);
    $stmt->bindParam(":picture", $profile->picture);
    $stmt->bindParam(":gender", $profile->gender);
    $stmt->bindParam(":google", $profile->google);
    $stmt->bindParam(":linkedin", $profile->linkedin);

    if ($stmt->execute()) {
      return true;
    }
    return false;
  }

  public function updateProfile(Profile $profile)
  {
    $query = "UPDATE \"$this->table\" SET firstname = :firstname, lastname = :lastname, gender = :gender, updated_at = :updated_at WHERE id = :id";
    $stmt = $this->pdo->prepare($query);

    $stmt->bindParam(":id", $profile->id);
    $stmt->bindParam(":firstname", $profile->firstname);
    $stmt->bindParam(":lastname", $profile->lastname);
    $stmt->bindParam(":gender", $profile->gender);
    $stmt->bindParam(":updated_at", ((new DateTime())->__serialize())['date']);

    if ($stmt->execute()) {
      return true;
    }
    return false;
  }
  public function updatePicture(string $id, string $picture)
  {
    $query = "UPDATE \"$this->table\" SET picture = :picture, updated_at = :updated_at WHERE id = :id";
    $stmt = $this->pdo->prepare($query);

    $stmt->bindParam(":id", $id);
    $stmt->bindParam(":picture", $picture);
    $stmt->bindParam(":updated_at", ((new DateTime())->__serialize())['date']);

    if ($stmt->execute()) {
      return true;
    }
    return false;
  }
  public function updateWebsite(string $id, string $linkedin, string $github, string $website)
  {
    $query = "UPDATE  \"$this->table\" SET linkedin = :linkedin, github = :github, website = :website, updated_at = :updated_at WHERE id = :id";
    $stmt = $this->pdo->prepare($query);

    $stmt->bindParam(":id", $id);
    $stmt->bindParam(":linkedin", $linkedin);
    $stmt->bindParam(":github", $github);
    $stmt->bindParam(":website", $website);
    $stmt->bindParam(":updated_at", ((new DateTime())->__serialize())['date']);

    if ($stmt->execute()) {
      return true;
    }
    return false;
  }
  public function updatePhone(string $id, string $phone)
  {
    $query = "UPDATE  \"$this->table\" SET phone = :phone, updated_at = :updated_at WHERE id = :id";
    $stmt = $this->pdo->prepare($query);

    $stmt->bindParam(":id", $id);
    $stmt->bindParam(":phone", $phone);
    $stmt->bindParam(":updated_at", ((new DateTime())->__serialize())['date']);

    if ($stmt->execute()) {
      return true;
    }
    return false;
  }
  public function updateGoogle(string $id, string $google)
  {
    $query = "UPDATE  \"$this->table\" SET google = :google, updated_at = :updated_at WHERE id = :id";
    $stmt = $this->pdo->prepare($query);

    $stmt->bindParam(":id", $id);
    $stmt->bindParam(":google", $google);
    $stmt->bindParam(":updated_at", ((new DateTime())->__serialize())['date']);

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
