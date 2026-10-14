<?php
require_once 'db.php';

$msg = '';

if (isset($_GET['action']) && $_GET['action'] === 'updatePoints' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $points = $_GET['points'] ?? 3;
    $pdo->query("UPDATE teams SET points = points + $points WHERE id = $id");
    $msg = "Очки (+3) нараховано через незахищене GET-посилання для команди ID #$id!";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_team'])) {
    $name = $_POST['name'];
    $city = $_POST['city'];
    $points = $_POST['points'];
    $stmt = $pdo->prepare("INSERT INTO teams (name, city, points) VALUES (?, ?, ?)");
    $stmt->execute([$name, $city, $points]);
    $msg = "Команду додано (без перевірки XSS та валідації)!";
}

$cityFilter = $_GET['city'] ?? '';
if ($cityFilter !== '') {
    $sql = "SELECT * FROM teams WHERE city = '$cityFilter' ORDER BY points DESC";
    $teams = $pdo->query($sql)->fetchAll();
} else {
    $sql = "SELECT * FROM teams ORDER BY points DESC";
    $teams = $pdo->query($sql)->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Вразлива версія</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #fff5f5; }
        table { border-collapse: collapse; width: 80%; background: #fff; margin-top: 15px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background: #f8d7da; }
        .box { background: #fff; border: 1px solid #f5c2c7; padding: 15px; margin-bottom: 15px; width: 78%; }
        .alert { color: #842029; background: #f8d7da; padding: 10px; border: 1px solid #f5c2c7; width: 78%; }
    </style>
</head>
<body>
    <h2>Спортивна ліга</h2>
    <p><a href="index.php">Перейти до ЗАХИЩЕНОЇ версії &rarr;</a></p>

    <?php if ($msg): ?><p class="alert"><strong><?= $msg ?></strong></p><?php endif; ?>

    <div class="box">
        <h4>1. Фільтр за містом:</h4>
        <form method="GET" action="vulnerable.php">
            <input type="text" name="city" placeholder="Введіть місто" value="<?= htmlspecialchars($cityFilter) ?>" style="width: 250px;">
            <button type="submit">Знайти</button>
            <a href="vulnerable.php">Скинути</a>
        </form>
    </div>

    <div class="box">
        <h4>2. Додати команду:</h4>
        <form method="POST" action="vulnerable.php">
            <input type="text" name="name" placeholder="Назва команди">
            <input type="text" name="city" placeholder="Місто">
            <input type="number" name="points" placeholder="Очки" value="0">
            <button type="submit" name="add_team">Додати</button>
        </form>
    </div>

    <table>
        <tr><th>ID</th><th>Назва команди</th><th>Місто</th><th>Очки</th><th>Небезпечна дія (GET)</th></tr>
        <?php foreach ($teams as $row): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= $row['name'] ?></td>
            <td><?= $row['city'] ?></td>
            <td><?= $row['points'] ?></td>
            <td>
                <a href="vulnerable.php?action=updatePoints&id=<?= $row['id'] ?>&points=3">+3 очки (через GET)</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>