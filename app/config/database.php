<?php

namespace App\Config;

use PDO;
use PDOException;

class Database
{
  private static $instance = null;
  private $pdo;

  private function __construct()
  {
    // Configuration locale pour MySQL
    // $db_name = "hindra-exchange";
    // $username = "root";
    // $password = "";

    // Configuration Locale pour PostgreSQL
    // $host = "localhost";
    // $db_name = "hindra-exchange";
    // $username = "postgres";
    // $password = "admin";

    // Configuration Production pour Heroku PostgreSQL
    $host = $_ENV['DB_HOST']?? "localhost";
    $db_name = $_ENV['DB_NAME']?? "hindra-exchange";
    $username = $_ENV['DB_USER']?? "postgres";
    $password = $_ENV['DB_PASSWORD']?? "admin";

    try {
      // $this->pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $username, $password, [
      $this->pdo = new PDO("pgsql:host=$host;dbname=$db_name", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
      ]);
    } catch (PDOException $e) {
      throw new PDOException("Erreur de connexion : " . $e->getMessage(), (int)$e->getCode());
    }
  }

  public static function getInstance()
  {
    if (!self::$instance) {
      self::$instance = new Database();
    }
    return self::$instance->pdo;
  }
}
