<?php
// Підключення бази даних через PDO (з попередніх практикумів)
require_once 'db.php';

// Крок 2: Встановлення заголовку application/json та отримання методу й параметрів
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$resource = $_GET['resource'] ?? null;
$id = $_GET['id'] ?? null;
$action = $_GET['action'] ?? null;

// Крок 3: Маршрутизація. Перевірка ресурсу
if ($resource !== 'teams') {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Невідомий ресурс або метод']);
    exit;
}

// Обробка запитів залежно від HTTP-методу
if ($method === 'GET') {
    // Крок 4: Реалізація GET (список або одиничний запис)[cite: 3]
    if ($id === null) {
        // Отримати весь список команд, відсортований за очками
        $stmt = $pdo->query('SELECT * FROM teams ORDER BY points DESC');
        $teams = $stmt->fetchAll();
        echo json_encode(['success' => true, 'data' => $teams]);
    } else {
        // Отримати один запис за id або 404, якщо такого немає[cite: 3]
        $stmt = $pdo->prepare('SELECT * FROM teams WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $team = $stmt->fetch();
        
        if ($team) {
            echo json_encode(['success' => true, 'data' => $team]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Команду не знайдено']);
        }
    }
} elseif ($method === 'POST') {
    // Отримання даних з POST-запиту або JSON-тіла[cite: 3]
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    // Крок 6: Доменна дія (action=addPoints) — нарахувати очки команді
    if ($action === 'addPoints') {
        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Не вказано ID команди']);
            exit;
        }

        $pointsToAdd = $input['points'] ?? null;
        if ($pointsToAdd === null || !is_numeric($pointsToAdd)) {
            http_response_code(400); // 400 Bad Request при некоректних даних[cite: 3]
            echo json_encode(['success' => false, 'error' => 'Некоректна кількість очок']);
            exit;
        }

        $update = $pdo->prepare('UPDATE teams SET points = points + :points WHERE id = :id');
        $update->execute([':points' => (int)$pointsToAdd, ':id' => $id]);

        if ($update->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Очки успішно нараховано']);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Команду не знайдено']);
        }
    } else {
        // Крок 5: Створення запису (звичайний POST)[cite: 3]
        $name = $input['name'] ?? '';
        $city = $input['city'] ?? '';
        $points = $input['points'] ?? 0;

        // Перевірка обов'язкових полів (400 Bad Request, якщо чогось бракує)[cite: 3]
        if (empty($name) || empty($city)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Обов\'язкові поля (name, city) відсутні']);
            exit;
        }

        // Виконання INSERT через підготовлений запит (захист від SQL-ін'єкцій)[cite: 3]
        $stmt = $pdo->prepare('INSERT INTO teams (name, city, points) VALUES (:name, :city, :points)');
        $stmt->execute([
            ':name' => $name,
            ':city' => $city,
            ':points' => $points
        ]);

        http_response_code(201); // 201 Created — успішно створено новий запис[cite: 3]
        echo json_encode([
            'success' => true, 
            'data' => [
                'id' => $pdo->lastInsertId(), 
                'name' => $name, 
                'city' => $city, 
                'points' => $points
            ]
        ]);
    }
} else {
    // Крок 7: Метод, що не підтримується для ресурсу, повертає 405 Method Not Allowed[cite: 3]
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Метод не підтримується']);
}
?>