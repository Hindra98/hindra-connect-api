<?php

namespace App\Repositories;

use App\Config\Database;
use App\Models\Params;
use DateTime;

class ParamsRepository
{
  private $table = "params";

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
    return $results == null ? [] : array_map(fn($row) => new Params($row), $results);

  }

  public function getById($id)
  {
    $query = "SELECT * FROM \"$this->table\" WHERE id = :id";
    $stmt = $this->pdo->prepare($query);
    $stmt->bindParam(":id", $id);
    $stmt->execute();
    $result = $stmt->fetch() ?: null;
    return $result ? new Params($result) : null;
  }

  public function getByUser($user_id)
  {
    $query = "SELECT * FROM \"$this->table\" WHERE user_id = :user_id";
    $stmt = $this->pdo->prepare($query);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();
    $result = $stmt->fetch() ?: null;
    return $result ? new Params($result) : null;
  }

  public function create(Params $params)
  {
    if ($this->getByUser($params->user_id)) return false;
    $query = "INSERT INTO \"$this->table\" (id, user_id, theme, userlanguage, notify_email, notify_phone, notify_in_app) VALUES (:id, :user_id, :theme, :userlanguage, :notify_email, :notify_phone, :notify_in_app)";
    $stmt = $this->pdo->prepare($query);

    $stmt->bindParam(":id", $params->id);
    $stmt->bindParam(":user_id", $params->user_id);
    $stmt->bindParam(":theme", $params->theme);
    $stmt->bindParam(":userlanguage", $params->userlanguage);
    $stmt->bindParam(":notify_email", $params->notify_email);
    $stmt->bindParam(":notify_phone", $params->notify_phone);
    $stmt->bindParam(":notify_in_app", $params->notify_in_app);

    if ($stmt->execute()) {
      return true;
    }
    return false;
  }

  public function update(Params $params)
  {
    $query = "UPDATE \"$this->table\" SET theme = :theme, userlanguage = :userlanguage, notify_email = :notify_email, notify_phone = :notify_phone, notify_in_app = :notify_in_app, updated_at = :updated_at WHERE id = :id";
    $stmt = $this->pdo->prepare($query);

    $stmt->bindParam(":id", $params->id);
    $stmt->bindParam(":theme", $params->theme);
    $stmt->bindParam(":userlanguage", $params->userlanguage);
    $stmt->bindParam(":notify_email", $params->notify_email);
    $stmt->bindParam(":notify_phone", $params->notify_phone);
    $stmt->bindParam(":notify_in_app", $params->notify_in_app);
    $stmt->bindParam(":updated_at", ((new DateTime())->__serialize())['date']);

    if ($stmt->execute()) {
      return true;
    }
    return false;
  }

  public function updateNotification(string $id, int $notify_email, int $notify_in_app, int $notify_phone)
  {
    $query = "UPDATE \"$this->table\" SET notify_email = :notify_email, notify_phone = :notify_phone, notify_in_app = :notify_in_app, updated_at = :updated_at WHERE id = :id";
    $stmt = $this->pdo->prepare($query);

    $stmt->bindParam(":id", $id);
    $stmt->bindParam(":notify_email", $notify_email);
    $stmt->bindParam(":notify_phone", $notify_phone);
    $stmt->bindParam(":notify_in_app", $notify_in_app);
    $stmt->bindParam(":updated_at", ((new DateTime())->__serialize())['date']);

    if ($stmt->execute()) {
      return true;
    }
    return false;
  }
  public function updateParams(string $id, string $userlanguage, string $theme)
  {
    $query = "UPDATE  \"$this->table\" SET userlanguage = :userlanguage,theme = :theme, updated_at = :updated_at WHERE id = :id";
    $stmt = $this->pdo->prepare($query);

    $stmt->bindParam(":id", $id);
    $stmt->bindParam(":userlanguage", $userlanguage);
    $stmt->bindParam(":theme", $theme);
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
