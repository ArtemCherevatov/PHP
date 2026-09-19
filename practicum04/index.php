<?php
require_once 'db.php';

// 1. Отримуємо значення міста з адресного рядка (якщо воно передано через форму)
$city = $_GET['city'] ?? '';

// 2. Якщо місто введено, використовуємо підготовлений запит із фільтрацією (findByCity)
if (!empty($city)) {
    $stmt = $pdo->prepare('SELECT * FROM teams WHERE city = :city ORDER BY points DESC');
    $stmt->execute([':city' => $city]);
} else {
    // Якщо місто не введено, виводимо загальну турнірну таблицю (standings)
    $stmt = $pdo->query('SELECT * FROM teams ORDER BY points DESC');
}

$teams = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Спортивна ліга</title>
    <style>
        body { font-family: sans-serif; margin: 20px; background: #f9f9f9; }
        table { border-collapse: collapse; width: 100%; margin-top: 15px; background: #fff; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background-color: #0056b3; color: white; }
        a { color: #0056b3; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .filter-form { margin: 15px 0; padding: 15px; background: #e9ecef; border: 1px solid #ccc; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>Турнірна таблиця (Спортивна ліга)</h1>
    <p><a href="add.php">➕ Додати нову команду</a></p>

    <!-- Додана форма фільтрації за містом -->
    <div class="filter-form">
        <form method="GET" action="index.php">
            <label for="city">Фільтр за містом:</label>
            <input type="text" name="city" id="city" value="<?= htmlspecialchars($city) ?>" placeholder="Введіть місто (наприклад: Київ)">
            <button type="submit">Знайти</button>
            <?php if (!empty($city)): ?>
                <a href="index.php" style="margin-left: 15px; color: #dc3545; font-weight: bold;">❌ Скинути фільтр</a>
            <?php endif; ?>
        </form>
    </div>

    <table>
        <tr>
            <th>ID</th>
            <th>Назва команди</th>
            <th>Місто</th>
            <th>Очки</th>
            <th>Дії</th>
        </tr>
        <?php foreach ($teams as $team): ?>
        <tr>
            <td><?= htmlspecialchars($team['id']) ?></td>
            <td><?= htmlspecialchars($team['name']) ?></td>
            <td><?= htmlspecialchars($team['city']) ?></td>
            <td><?= htmlspecialchars($team['points']) ?></td>
            <td>
                <a href="update.php?id=<?= $team['id'] ?>">Редагувати</a> | 
                <a href="delete.php?id=<?= $team['id'] ?>" onclick="return confirm('Дійсно видалити цю команду?');" style="color: red;">Видалити</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>