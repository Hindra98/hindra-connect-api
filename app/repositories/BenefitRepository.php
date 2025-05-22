<?php

namespace App\Repositories;

use App\Config\Database;
use App\Models\Benefit;
use DateTime;

class BenefitRepository
{
  private $table = "benefits";

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
    return $results == null ? [] : array_map(fn($row) => new Benefit($row), $results);
  }

  public function getById($id)
  {
    $query = "SELECT * FROM \"$this->table\" WHERE id = :id";
    $stmt = $this->pdo->prepare($query);
    $stmt->bindParam(":id", $id);
    $stmt->execute();
    $result = $stmt->fetch() ?: null;
    return $result ? new Benefit($result) : null;
  }

  public function getByTitle($title)
  {
    $query = "SELECT * FROM \"$this->table\" WHERE title = :title";
    $stmt = $this->pdo->prepare($query);
    $stmt->bindParam(":title", $title);
    $stmt->execute();
    $results = $stmt->fetchAll() ?: null;
    return $results == null ? [] : array_map(fn($row) => new Benefit($row), $results);
  }

  public function create(Benefit $benefit)
  {
    $query = "INSERT INTO \"$this->table\" (id, user_id, title, description, price, location, category_id, availability) VALUES (:id, :user_id, :title, :description, :price, :location, :category_id, :availability)";
    $stmt = $this->pdo->prepare($query);

    $stmt->bindParam(":id", $benefit->id);
    $stmt->bindParam(":user_id", $benefit->user_id);
    $stmt->bindParam(":title", $benefit->title);
    $stmt->bindParam(":description", $benefit->description);
    $stmt->bindParam(":price", $benefit->price);
    $stmt->bindParam(":location", $benefit->location);
    $stmt->bindParam(":category_id", $benefit->category_id);
    $stmt->bindParam(":availability ", $benefit->availability);

    if ($stmt->execute()) {
      return true;
    }
    return false;
  }

  public function update(Benefit $benefit)
  {
    $query = "UPDATE \"$this->table\" SET title = :title, description = :description, price = :price, location = :location, category_id = :category_id, availability = :availability, updated_at = :updated_at WHERE id = :id";
    $stmt = $this->pdo->prepare($query);

    $stmt->bindParam(":id", $benefit->id);
    $stmt->bindParam(":title", $benefit->title);
    $stmt->bindParam(":description", $benefit->description);
    $stmt->bindParam(":price", $benefit->price);
    $stmt->bindParam(":location", $benefit->location);
    $stmt->bindParam(":category_id", $benefit->category_id);
    $stmt->bindParam(":availability ", $benefit->availability);
    $stmt->bindParam(":updated_at", ((new DateTime())->__serialize())['date']);


    if ($stmt->execute()) {
      return true;
    }
    return false;
  }

  public function updateBenefitTitle($id,$title,$description,$price)
  {
    $query = "UPDATE \"$this->table\" SET title = :title, description = :description, price = :price, updated_at = :updated_at WHERE id = :id";
    $stmt = $this->pdo->prepare($query);

    $stmt->bindParam(":id", $id);
    $stmt->bindParam(":title", $title);
    $stmt->bindParam(":description", $description);
    $stmt->bindParam(":price", $price);
    $stmt->bindParam(":updated_at", ((new DateTime())->__serialize())['date']);


    if ($stmt->execute()) {
      return true;
    }
    return false;
  }

  public function updateBenefitCategory($id,$location,$category_id,$availability)
  {
    $query = "UPDATE \"$this->table\" SET location = :location, category_id = :category_id, availability = :availability, updated_at = :updated_at WHERE id = :id";
    $stmt = $this->pdo->prepare($query);

    $stmt->bindParam(":id", $id);
    $stmt->bindParam(":location", $location);
    $stmt->bindParam(":category_id", $category_id);
    $stmt->bindParam(":availability ", $availability);
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
