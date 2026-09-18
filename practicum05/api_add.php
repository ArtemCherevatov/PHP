<?php
require_once 'db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $city = $_POST['city'] ?? '';
    $points = $_POST['points'] ?? 0;

    if (!empty($name) && !empty($city)) {
        $stmt = $pdo->prepare('INSERT INTO teams (name, city, points) VALUES (:name, :city, :points)');
        $stmt->execute([
            ':name' => $name,
            ':city' => $city,
            ':points' => $points
        ]);
        
        // Повертаємо код 201 та JSON про успіх[cite: 12]
        http_response_code(201);
        echo json_encode(['status' => 'success', 'id' => $pdo->lastInsertId()]);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Некоректні дані']);
    }
}
?>