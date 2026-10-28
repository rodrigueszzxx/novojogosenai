<?php
/**
 * Endpoint para obter o TOP 10 do ranking
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/conexao.php';
try { $pdo = conectarBanco(); } catch (PDOException $e) {
    http_response_code(503);
    echo json_encode(['status' => 'error', 'message' => 'Banco de dados temporariamente indisponível.']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT id, nome, pontos, DATE_FORMAT(data_registro, '%d/%m/%Y %H:%i') as data 
        FROM ranking 
        ORDER BY pontos DESC, id ASC 
        LIMIT 10
    ");
    $stmt->execute();
    $ranking = $stmt->fetchAll();

    echo json_encode([
        'status' => 'success',
        'ranking' => $ranking
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Erro ao consultar ranking: ' . $e->getMessage()
    ]);
}