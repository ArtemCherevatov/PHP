<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare('INSERT INTO teams (name, city, points) VALUES (:name, :city, :points)');
    $stmt->execute([
        ':name' => $_POST['name'],
        ':city' => $_POST['city'],
        ':points' => $_POST['points']
    ]);
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="uk">
<head><meta charset="UTF-8"><title>Додати команду</title></head>
<body style="font-family: sans-serif; margin: 20px;">
    <h2>Додати нову команду</h2>
    <form method="post">
        <p><label>Назва команди: <br><input type="text" name="name" required></label></p>
        <p><label>Місто: <br><input type="text" name="city" required></label></p>
        <p><label>Очки: <br><input type="number" name="points" value="0" required></label></p>
        <button type="submit">Зберегти</button>
    </form>
    <p><a href="index.php">Назад до списку</a></p>
</body>
</html>