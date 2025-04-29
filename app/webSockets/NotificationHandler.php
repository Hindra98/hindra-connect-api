<?php
namespace App\WebSockets;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use SplObjectStorage;

class NotificationHandler implements MessageComponentInterface {
    protected $clients;
    protected $userConnections;

    public function __construct() {
        $this->clients = new SplObjectStorage;
        $this->userConnections = [];
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);
        
        // Authentification de l'utilisateur
        if ($data['type'] === 'auth' && isset($data['token'])) {
            $userId = $this->authenticateUser($data['token']);
            if ($userId) {
                $this->userConnections[$userId] = $from;
                echo "User {$userId} authenticated\n";
            }
            return;
        }
        
        // Envoi de notification
        if ($data['type'] === 'notification' && isset($data['userId'])) {
            $this->sendToUser($data['userId'], json_encode($data));
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} has disconnected\n";
        
        // Nettoyer les connexions utilisateur
        foreach ($this->userConnections as $userId => $connection) {
            if ($connection === $conn) {
                unset($this->userConnections[$userId]);
                break;
            }
        }
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }
    
    protected function authenticateUser($token) {
        // Implémentez la vérification du token JWT
        // Retourne l'ID utilisateur ou false
    }
    
    protected function sendToUser($userId, $message) {
        if (isset($this->userConnections[$userId])) {
            $this->userConnections[$userId]->send($message);
        }
    }
}