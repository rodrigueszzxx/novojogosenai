<?php
/** Configuração local; preserve seus ajustes ao atualizar o projeto. */
function conectarBanco(): PDO
{
    $host = getenv('DB_HOST') ?: 'localhost';
    $port = getenv('DB_PORT') ?: '3306';
    $user = getenv('DB_USER') ?: 'root';
    $pass = getenv('DB_PASSWORD') ?: '';
    $dbname = getenv('DB_NAME') ?: 'hero_jump';
    if (!preg_match('/^[A-Za-z0-9_]+$/', $dbname)) {
        throw new RuntimeException('Nome de banco inválido.');
    }
    $pdo = new PDO("mysql:host=$host;port=$port;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$dbname`");
    $pdo->exec('CREATE TABLE IF NOT EXISTS ranking (
        id INT AUTO_INCREMENT PRIMARY KEY, nome VARCHAR(50) NOT NULL,
        pontos INT NOT NULL, data_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )');
    return $pdo;
}
