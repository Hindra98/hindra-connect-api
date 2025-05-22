<?php

namespace App\Repositories;

use App\Config\Database;
use App\Models\File;
use DateTime;

class FileRepository
{
  private $table = "file";

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
    return array_map(fn($row) => new File($row), $results);
  }

  public function getById($id)
  {
    $query = "SELECT * FROM \"$this->table\" WHERE id = :id";
    $stmt = $this->pdo->prepare($query);
    $stmt->bindParam(":id", $id);
    $stmt->execute();
    $result = $stmt->fetch() ?: null;
    return $result ? new File($result) : null;
  }

  public function getByUser($user_id)
  {
    $query = "SELECT * FROM \"$this->table\" WHERE user_id = :user_id";
    $stmt = $this->pdo->prepare($query);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();
    $result = $stmt->fetch() ?: null;
    return $result ? new File($result) : null;
    /*
      $results = $stmt->fetchAll() ?: null;
      return array_map(fn($row) => new File($row), $results);
    */
  }

  public function create(File $file)
  {
    if ($this->getByUser($file->user_id)) return false;
    // $query = "INSERT INTO \"$this->table\" (id, user_id, path, type, original_name, mime_type, size) VALUES (:id, :user_id, :path, :type, :original_name, :mime_type, :size) RETURNING id";
    $query = "INSERT INTO \"$this->table\" (id, user_id, path, type, original_name, mime_type, size) VALUES (:id, :user_id, :path, :type, :original_name, :mime_type, :size)";
    $stmt = $this->pdo->prepare($query);

    $stmt->bindParam(":id", $file->id);
    $stmt->bindParam(":user_id", $file->user_id);
    $stmt->bindParam(":path", $file->path);
    $stmt->bindParam(":type", $file->type);
    $stmt->bindParam(":original_name", $file->original_name);
    $stmt->bindParam(":mime_type", $file->mime_type);
    $stmt->bindParam(":size", $file->size);

    if ($stmt->execute()) {
      return true;
    }
    return false;
  }

  public function update(File $file)
  {
    $query = "UPDATE \"$this->table\" SET path = :path, type = :type, original_name = :original_name, mime_type = :mime_type, size = :size, updated_at = :updated_at WHERE id = :id";
    $stmt = $this->pdo->prepare($query);

    $stmt->bindParam(":id", $file->id);
    $stmt->bindParam(":path", $file->path);
    $stmt->bindParam(":type", $file->type);
    $stmt->bindParam(":original_name", $file->original_name);
    $stmt->bindParam(":mime_type", $file->mime_type);
    $stmt->bindParam(":size", $file->size);
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
