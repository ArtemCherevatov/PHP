<?php
$errors = [];
$successMessage = '';

// Отримання даних форми[cite: 2]
$teamHome = $_POST['teamHome'] ?? '';
$teamAway = $_POST['teamAway'] ?? '';
$scoreHome = $_POST['scoreHome'] ?? '';
$scoreAway = $_POST['scoreAway'] ?? '';

// Серверна обробка й валідація[cite: 2]
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (trim($teamHome) === '' || trim($teamAway) === '') {
        $errors['teams'] = 'Назви команд є обов\'язковими.';
    } elseif (trim($teamHome) === trim($teamAway)) {
        $errors['teams'] = 'Команда господарів не може збігатися з командою гостей.';
    }

    if (!is_numeric($scoreHome) || (int)$scoreHome < 0) {
        $errors['scoreHome'] = 'Рахунок господарів має бути цілим числом >= 0.';
    }
    
    if (!is_numeric($scoreAway) || (int)$scoreAway < 0) {
        $errors['scoreAway'] = 'Рахунок гостей має бути цілим числом >= 0.';
    }

    if (empty($errors)) {
        $successMessage = "Матч успішно збережено: " . htmlspecialchars($teamHome) . " " . htmlspecialchars($scoreHome) . " - " . htmlspecialchars($scoreAway) . " " . htmlspecialchars($teamAway);
    }
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Варіант 20 - Спортивна ліга</title>
    <style>
        body { font-family: sans-serif; margin: 20px; }
        .error { color: red; font-size: 0.9em; display: block; margin-top: 5px; }
        .success { color: green; font-weight: bold; }
        .form-group { margin-bottom: 15px; }
    </style>
</head>
<body>
    <!-- Елемент для демонстрації localStorage[cite: 2] -->
    <div class="form-group">
        <label for="leagueFilter">Фільтр ліги/туру:</label>
        <select id="leagueFilter">
            <option value="tour1">Тур 1 (Прем'єр-ліга)</option>
            <option value="tour2">Тур 2 (Перша ліга)</option>
            <option value="tour3">Тур 3 (Кубок)</option>
        </select>
    </div>
    
    <hr>

    <?php if ($successMessage): ?>
        <p class="success"><?= $successMessage ?></p>
    <?php endif; ?>

    <!-- HTML-форма з клієнтською валідацією[cite: 2] -->
    <form id="matchForm" method="post" action="form.php">
        <div class="form-group">
            <label>Команда господарів:</label><br>
            <input type="text" name="teamHome" id="teamHome" value="<?= htmlspecialchars($teamHome) ?>" required>
        </div>
        
        <div class="form-group">
            <label>Команда гостей:</label><br>
            <input type="text" name="teamAway" id="teamAway" value="<?= htmlspecialchars($teamAway) ?>" required>
            <?php if (isset($errors['teams'])): ?>
                <span class="error"><?= $errors['teams'] ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label>Рахунок господарів:</label><br>
            <input type="number" name="scoreHome" id="scoreHome" min="0" value="<?= htmlspecialchars($scoreHome) ?>" required>
            <?php if (isset($errors['scoreHome'])): ?>
                <span class="error"><?= $errors['scoreHome'] ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label>Рахунок гостей:</label><br>
            <input type="number" name="scoreAway" id="scoreAway" min="0" value="<?= htmlspecialchars($scoreAway) ?>" required>
            <?php if (isset($errors['scoreAway'])): ?>
                <span class="error"><?= $errors['scoreAway'] ?></span>
            <?php endif; ?>
        </div>

        <button type="submit">Зберегти результат</button>
    </form>

    <script>
        // Клієнтська JavaScript-валідація[cite: 2]
        document.getElementById('matchForm').addEventListener('submit', function(event) {
            const teamHome = document.getElementById('teamHome').value.trim();
            const teamAway = document.getElementById('teamAway').value.trim();
            const scoreHome = parseInt(document.getElementById('scoreHome').value, 10);
            const scoreAway = parseInt(document.getElementById('scoreAway').value, 10);

            if (teamHome === teamAway && teamHome !== '') {
                alert('Помилка: Команда господарів і гостей не можуть збігатися!');
                event.preventDefault();
                return;
            }

            if (scoreHome < 0 || scoreAway < 0) {
                alert('Помилка: Рахунок не може бути від\'ємним!');
                event.preventDefault();
            }
        });

        // Сценарій збереження стану у localStorage[cite: 2]
        const leagueFilter = document.getElementById('leagueFilter');
        
        // Відновлення значення при завантаженні сторінки
        const savedLeague = localStorage.getItem('selectedLeague');
        if (savedLeague) {
            leagueFilter.value = savedLeague;
        }

        // Збереження вибору при зміні
        leagueFilter.addEventListener('change', function() {
            localStorage.setItem('selectedLeague', this.value);
        });
    </script>
</body>
</html>