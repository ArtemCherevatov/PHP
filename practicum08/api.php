<?php
/*
ЗВІТ ДО ЛАБОРАТОРНОЇ РОБОТИ №7
Варіант 20: Спортивна ліга (teams)

1. Вивід команди EXPLAIN (Крок 4):
Запит: EXPLAIN SELECT * FROM teams WHERE city = 'Київ';

ДО створення індексу (повне сканування таблиці, type = ALL):

| id | select_type | table | type | possible_keys | key  | key_len | ref  | rows | Extra       |

|  1 | SIMPLE      | teams | ALL  | NULL          | NULL | NULL    | NULL |   4  | Using where |


ПІСЛЯ створення індексу CREATE INDEX idx_teams_city ON teams (city) (використано індекс, type = ref):

| id | select_type | table | type | possible_keys  | key            | key_len | ref   | rows | Extra                     |

|  1 | SIMPLE      | teams | ref  | idx_teams_city | idx_teams_city | 1022    | const |    1 |   Usingindex condition    |


2. Результати тестування продуктивності (Кроки 1-2, 7-8):
| Показник                      | До оптимізації | Після оптимізації |
| :---------------------------- | :------------- | :---------------- |
| Кількість SQL-запитів         | N+1            | 2 (або 0 з кешу)  |
| Середній час на запит (ab)    | 34.54 мс       | 21.14 мс          |
| Requests per second (ab -c 20)| 579.11         | 946.14            |

Висновок: 
Усунення проблеми N+1 шляхом використання GROUP BY city та створення індексу 
idx_teams_city (файл index.sql) суттєво зменшило навантаження на БД (перехід від 
type=ALL до type=ref). Додавання файлового кешування для турнірної таблиці 
дозволило обробляти дані без повторних SQL-запитів, що збільшило пропускну 
здатність застосунку з 579.11 до 946.14 запитів за секунду.
*/

require_once 'db.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$resource = $_GET['resource'] ?? null;
$id = $_GET['id'] ?? null;
$action = $_GET['action'] ?? null;

if ($resource !== 'teams') {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Невідомий ресурс або метод']);
    exit;
}

// Шлях до файлу кешу для 20 варіанта
$cacheFile = sys_get_temp_dir() . '/cache_standings.json';

if ($method === 'GET') {
    if ($id === null) {
        // --- ПОЧАТОК ВИМІРЮВАННЯ ---
        $start = microtime(true);
        $queryCount = 0; 
        
        $ttlSeconds = 60;
        
        // --- ПЕРЕВІРКА КЕШУ ---
        if (is_file($cacheFile) && (time() - filemtime($cacheFile)) < $ttlSeconds) {
            $teams = json_decode(file_get_contents($cacheFile), true);
            $fromCache = true;
        } else {
            $fromCache = false;
            
            // 1. Отримуємо команди
            $stmt = $pdo->query('SELECT * FROM teams ORDER BY points DESC');
            $teams = $stmt->fetchAll();
            $queryCount++;
            
            // 2. ОПТИМІЗАЦІЯ N+1: Отримуємо середні бали одним агрегатним запитом
            $cityAveragesStmt = $pdo->query('SELECT city, AVG(points) AS avg_points FROM teams GROUP BY city');
            $cityAverages = $cityAveragesStmt->fetchAll(PDO::FETCH_KEY_PAIR);
            $queryCount++;
            
            // 3. Зіставляємо дані в пам'яті
            foreach ($teams as &$team) {
                $team['city_avg_points'] = round((float)($cityAverages[$team['city']] ?? 0), 2);
            }
            
            // Зберігаємо в кеш
            file_put_contents($cacheFile, json_encode($teams));
        }

        // --- ЗАВЕРШЕННЯ ВИМІРЮВАННЯ ---
        $elapsed = (microtime(true) - $start) * 1000;
        $peakMemory = memory_get_peak_usage(true) / 1024 / 1024;
        
        error_log(sprintf('ПІСЛЯ ОПТИМІЗАЦІЇ — Час: %.2f мс, пам\'ять: %.2f МБ, запитів: %d', $elapsed, $peakMemory, $queryCount));

        echo json_encode([
            'success' => true, 
            'data' => $teams,
            'debug' => [
                'time_ms' => round($elapsed, 2),
                'memory_mb' => round($peakMemory, 2),
                'sql_queries' => $queryCount,
                'cached' => $fromCache,
                'status' => 'OPTIMIZED'
            ]
        ]);
    } else {
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
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    // --- ІНВАЛІДАЦІЯ КЕШУ ---
    // Якщо дані змінюються, видаляємо старий кеш
    if (is_file($cacheFile)) {
        unlink($cacheFile);
    }

    if ($action === 'addPoints') {
        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Не вказано ID команди']);
            exit;
        }

        $pointsToAdd = $input['points'] ?? null;
        if ($pointsToAdd === null || !is_numeric($pointsToAdd)) {
            http_response_code(400); 
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
        $name = $input['name'] ?? '';
        $city = $input['city'] ?? '';
        $points = $input['points'] ?? 0;

        if (empty($name) || empty($city)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Обов\'язкові поля (name, city) відсутні']);
            exit;
        }

        $stmt = $pdo->prepare('INSERT INTO teams (name, city, points) VALUES (:name, :city, :points)');
        $stmt->execute([
            ':name' => $name,
            ':city' => $city,
            ':points' => $points
        ]);

        http_response_code(201); 
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
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Метод не підтримується']);
}
?>