<?php
require_once 'db.php';

$id = $_GET['id'] ?? null;
if (!$id) { header('Location: index.php'); exit; }

$stmt = $pdo->prepare('SELECT * FROM teams WHERE id = :id');
$stmt->execute([':id' => $id]);
$team = $stmt->fetch();

if (!$team) { die("Команду не знайдено."); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $update = $pdo->prepare('UPDATE teams SET points = :points WHERE id = :id');
    $update->execute([':points' => $_POST['points'], ':id' => $id]);
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="uk">
<head><meta charset="UTF-8"><title>Редагувати</title></head>
<body style="font-family: sans-serif; margin: 20px;">
    <h2>Редагувати очки для: <?= htmlspecialchars($team['name']) ?></h2>
    <form method="post">
        <p><label>Очки: <br><input type="number" name="points" value="<?= htmlspecialchars($team['points']) ?>" required></label></p>
        <button type="submit">Оновити</button>
    </form>
    <p><a href="index.php">Назад до списку</a></p>
</body>
</html>