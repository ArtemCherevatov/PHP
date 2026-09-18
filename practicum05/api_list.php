<?php
require_once 'db.php';
// Встановлюємо заголовок для повернення даних у форматі JSON
header('Content-Type: application/json');

$cityQuery = $_GET['q'] ?? '';

try {
    if ($cityQuery !== '') {
        // Пошук: фільтрація за містом
        $stmt = $pdo->prepare('SELECT * FROM teams WHERE city LIKE :city ORDER BY points DESC');
        $stmt->execute([':city' => '%' . $cityQuery . '%']);
        $rows = $stmt->fetchAll();
    } else {
        // Виведення всієї турнірної таблиці, відсортованої за очками
        $rows = $pdo->query('SELECT * FROM teams ORDER BY points DESC')->fetchAll();
    }
    echo json_encode($rows);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>