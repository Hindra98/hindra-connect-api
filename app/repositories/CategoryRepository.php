<?php

namespace App\Repositories;

use App\Config\Database;
use App\Models\Category;
use DateTime;

class CategoryRepository
{
  private $table = "category";

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
    return $results == null ? [] : array_map(fn($row) => new Category($row), $results);
  }

  public function getById($id)
  {
    $query = "SELECT * FROM \"$this->table\" WHERE id = :id";
    $stmt = $this->pdo->prepare($query);
    $stmt->bindParam(":id", $id);
    $stmt->execute();
    $result = $stmt->fetch() ?: null;
    return $result ? new Category($result) : null;
  }

  public function getByTitle(string $title)
  {
    $query = "SELECT * FROM \"$this->table\" WHERE title = :title";
    $stmt = $this->pdo->prepare($query);
    $stmt->bindParam(":title", $title);
    $stmt->execute();
    $result = $stmt->fetch() ?: null;
    return $result ? new Category($result) : null;
  }

  public function create(Category $category)
  {
    if ($this->getByTitle($category->title)) return 2;
    $query = "INSERT INTO \"$this->table\" (id, title, picture, description) VALUES (:id, :title, :picture, :description)";
    $stmt = $this->pdo->prepare($query);

    $stmt->bindParam(":id", $category->id);
    $stmt->bindParam(":title", $category->title);
    $stmt->bindParam(":picture", $category->picture);
    $stmt->bindParam(":description", $category->description);

    if ($stmt->execute()) {
      return 1;
    }
    return 0;
  }

  public function update(Category $category)
  {
    $query = "UPDATE \"$this->table\" SET picture = :picture, description = :description, title = :title, updated_at = :updated_at WHERE id = :id";
    $stmt = $this->pdo->prepare($query);

    $stmt->bindParam(":id", $category->id);
    $stmt->bindParam(":picture", $category->picture);
    $stmt->bindParam(":description", $category->description);
    $stmt->bindParam(":title", $category->title);
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
