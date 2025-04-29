<?php
namespace App\Models;

class File
{
    private $db;

    public function __construct(\PDO $db)
    {
        $this->db = $db;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO user_files 
            (user_id, path, type, original_name, mime_type, size)
            VALUES (:user_id, :path, :type, :original_name, :mime_type, :size)
            RETURNING id
        ");

        $stmt->execute([
            ':user_id' => $data['user_id'],
            ':path' => $data['path'],
            ':type' => $data['type'],
            ':original_name' => $data['original_name'],
            ':mime_type' => $data['mime_type'],
            ':size' => $data['size']
        ]);

        return $stmt->fetchColumn();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM user_files WHERE id = :id
        ");

        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }
}