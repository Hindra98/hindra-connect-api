<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class Authenticate
{
  private $table = "users";

  private $pdo;

  public function __construct()
  {
    $this->pdo = Database::getInstance();
  }

  public function getAll()
  {
    $query = "SELECT * FROM \"$this->table\"";
    $stmt = $this->pdo->query($query);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getOne($id)
  {
    $query = "SELECT * FROM \"$this->table\" WHERE id = :id";
    $stmt = $this->pdo->prepare($query);
    $stmt->bindParam(":id", $id);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  public function register($nom, $email, $password)
  {
    $query = "INSERT INTO \"$this->table\"  (nom, email, password) VALUES (:nom, :email, :password)";
    $stmt = $this->pdo->prepare($query);

    $stmt->bindParam(":nom", $nom);
    $stmt->bindParam(":email", $email);
    $stmt->bindParam(":password", $password);

    if ($stmt->execute()) {
      return true;
    }
    return false;
  }

  public function update($id, $nom, $contact, $capacite, $adresse)
  {
    $query = "UPDATE \"$this->table\" SET nom = :nom, contact = :contact, capacite = :capacite, adresse = :adresse WHERE id = :id";
    $stmt = $this->pdo->prepare($query);

    $stmt->bindParam(":id", $id);
    $stmt->bindParam(":nom", $nom);
    $stmt->bindParam(":contact", $contact);
    $stmt->bindParam(":capacite", $capacite);
    $stmt->bindParam(":adresse", $adresse);

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
