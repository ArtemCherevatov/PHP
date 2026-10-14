<?php
session_start();
require_once 'db.php';
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (isset($_GET['role'])) {
    $_SESSION['is_admin'] = ($_GET['role'] === 'admin');
}
$isAdmin = $_SESSION['is_admin'] ?? true;

$message = '';
$error = '';

if (isset($_GET['action']) && $_GET['action'] === 'updatePoints') {
    http_response_code(405);
    $error = 'Помилка 405 (Method Not Allowed): Нарахування очок через GET-параметр заборонено! Використовуйте POST-форму.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Крок 3: Перевірка CSRF-токена через hash_equals()
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        $error = 'Помилка 403 (Forbidden): Недійсний або відсутній CSRF-токен! Запит відхилено.';
    } else {
        $action = $_POST['action'] ?? '';

        try {
            if ($action === 'updatePoints') {
                if (!$isAdmin) {
                    http_response_code(403);
                    $error = 'Помилка 403 (Forbidden): Відмовлено в доступі! Нараховувати очки може лише адміністратор.';
                } else {
                    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
                    $points = filter_input(INPUT_POST, 'points', FILTER_VALIDATE_INT);

                    if (!$id || $points === false || $points <= 0 || $points > 100) {
                        http_response_code(400);
                        $error = 'Помилка 400 (Валідація): Очки мають бути додатним цілим числом від 1 до 100.';
                    } else {
                        $stmt = $pdo->prepare('UPDATE teams SET points = points + :points WHERE id = :id');
                        $stmt->execute([':points' => $points, ':id' => $id]);

                        $cacheFile = sys_get_temp_dir() . '/cache_standings.json';
                        if (is_file($cacheFile)) unlink($cacheFile);

                        $message = "Успішно! Команді #$id нараховано +$points очок (перевірено CSRF та права адміна).";
                    }
                }
            } elseif ($action === 'addTeam') {
                $name = trim($_POST['name'] ?? '');
                $city = trim($_POST['city'] ?? '');
                $points = filter_input(INPUT_POST, 'points', FILTER_VALIDATE_INT);

                if ($name === '' || $city === '' || mb_strlen($name) > 100 || mb_strlen($city) > 100) {
                    http_response_code(400);
                    $error = 'Помилка 400 (Валідація): Поля Назва та Місто не можуть бути порожніми.';
                } elseif ($points === false || $points < 0) {
                    http_response_code(400);
                    $error = 'Помилка 400 (Валідація): Початкові очки не можуть бути від\'ємними.';
                } else {
                    $stmt = $pdo->prepare('INSERT INTO teams (name, city, points) VALUES (:name, :city, :points)');
                    $stmt->execute([':name' => $name, ':city' => $city, ':points' => $points]);
                    $message = 'Нову команду успішно додано!';
                }
            }
        } catch (PDOException $e) {
            error_log("Database Error in index.php: " . $e->getMessage());
            http_response_code(500);
            $error = 'Внутрішня помилка сервера при збереженні даних.';
        }
    }
}


$cityFilter = trim($_GET['city'] ?? '');
try {
    if ($cityFilter !== '') {
        $stmt = $pdo->prepare('SELECT * FROM teams WHERE city = :city ORDER BY points DESC');
        $stmt->execute([':city' => $cityFilter]);
        $teams = $stmt->fetchAll();
    } else {
        $stmt = $pdo->query('SELECT * FROM teams ORDER BY points DESC');
        $teams = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    error_log("Database Select Error: " . $e->getMessage());
    $teams = [];
    $error = 'Внутрішня помилка завантаження даних.';
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Захищена версія</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f4fbf7; }
        table { border-collapse: collapse; width: 85%; background: #fff; margin-top: 15px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background: #d1e7dd; }
        .box { background: #fff; border: 1px solid #badbcc; padding: 15px; margin-bottom: 15px; width: 83%; }
        .msg { color: #0f5132; background: #d1e7dd; padding: 10px; border: 1px solid #badbcc; width: 83%; font-weight: bold; }
        .err { color: #842029; background: #f8d7da; padding: 10px; border: 1px solid #f5c2c7; width: 83%; font-weight: bold; }
        .inline-form { display: inline-block; margin: 0; }
        .test-links { background: #fff3cd; border: 1px solid #ffecb5; padding: 10px; margin-bottom: 15px; width: 83%; }
    </style>
</head>
<body>
    <h2>Спортивна ліга</h2>

    <div class="test-links">
        <strong>Швидкі тести безпеки для скріншотів:</strong><br>
        1. Роль користувача: <strong><?= $isAdmin ? 'Адміністратор' : 'Гість (без прав)' ?></strong>
        [ <a href="index.php?role=admin">Увімкнути Адміна</a> | <a href="index.php?role=user">Увімкнути Гостя</a> ]<br>

        <a href="index.php?action=updatePoints&id=1&points=3">Спробувати нарахувати очки через GET-посилання (викличе Помилку 405)</a>
    </div>

    <?php if ($message): ?><p class="msg"><?= htmlspecialchars($message, ENT_QUOTES) ?></p><?php endif; ?>
    <?php if ($error): ?><p class="err"><?= htmlspecialchars($error, ENT_QUOTES) ?></p><?php endif; ?>

    <div class="box">
        <h4>1. Фільтр за містом:</h4>
        <form method="GET" action="index.php">
            <input type="text" name="city" placeholder="Введіть місто..." value="<?= htmlspecialchars($cityFilter, ENT_QUOTES) ?>" style="width: 250px;">
            <button type="submit">Фільтрувати</button>
            <a href="index.php">Скинути</a>
        </form>
    </div>

    <div class="box">
        <h4>2. Додати команду:</h4>
        <form method="POST" action="index.php">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES) ?>">
            <input type="hidden" name="action" value="addTeam">
            <input type="text" name="name" placeholder="Назва команди">
            <input type="text" name="city" placeholder="Місто">
            <input type="number" name="points" placeholder="Очки" value="0">
            <button type="submit">Додати команду</button>
        </form>
    </div>

    <table>
        <tr>
            <th>ID</th>
            <th>Назва команди (htmlspecialchars)</th>
            <th>Місто (htmlspecialchars)</th>
            <th>Очки</th>
            <th>Нарахування очок (POST + CSRF + Admin + Confirm)</th>
        </tr>
        <?php foreach ($teams as $row): ?>
        <tr>
            <td><?= (int)$row['id'] ?></td>
            <td><?= htmlspecialchars($row['name'], ENT_QUOTES) ?></td>
            <td><?= htmlspecialchars($row['city'], ENT_QUOTES) ?></td>
            <td><?= (int)$row['points'] ?></td>
            <td>
                <form method="POST" action="index.php" class="inline-form" onsubmit="return confirm('Підтвердити нарахування очок команді?');">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES) ?>">
                    <input type="hidden" name="action" value="updatePoints">
                    <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                    <input type="number" name="points" value="3" style="width: 55px;">
                    <button type="submit">Нарахувати очки</button>
                </form>
                <form method="POST" action="index.php" class="inline-form">
                    <input type="hidden" name="csrf_token" value="wrong_invalid_token_123">
                    <input type="hidden" name="action" value="updatePoints">
                    <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                    <input type="hidden" name="points" value="3">
                    <button type="submit" style="background:#f8d7da; border:1px solid #dc3545; cursor:pointer;" title="Тест атаки без правильного CSRF-токена">Тест CSRF-атаки (403)</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>