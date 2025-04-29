<?php
// Configuration de base
define('ROOT_PATH', realpath(dirname(__FILE__) . '/../'));
define('UPLOAD_BASE', ROOT_PATH . '/uploads/');

// Types MIME autorisés
$allowedMimeTypes = [
    'image/jpeg',
    'image/png',
    'image/gif',
    'image/webp'
];

// Taille max (5MB)
define('MAX_FILE_SIZE', 5 * 1024 * 1024);

// Connexion DB (exemple avec PostgreSQL)
$db = new PDO('pgsql:host=localhost;dbname=hindra_db', 'username', 'password');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);