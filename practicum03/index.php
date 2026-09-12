<?php
require_once 'classes/Team.php';
require_once 'classes/PromotedTeam.php';
require_once 'classes/League.php';
require_once 'lib/functions.php';

$league = new League();

// Додавання об'єктів[cite: 3]
$league->addTeam(new Team('Динамо', 'Київ', 45));
$league->addTeam(new Team('Шахтар', 'Донецьк', 48));
$league->addTeam(new Team('Зоря', 'Луганськ', 36));
$league->addTeam(new PromotedTeam('Полісся', 'Житомир', 32, 'Перша ліга'));
$league->addTeam(new PromotedTeam('Оболонь', 'Київ', 25, 'Перша ліга'));

$standings = $league->standings();
$kyivTeams = $league->findByCity('Київ');
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Спортивна ліга</title>
    <style>
        body { font-family: sans-serif; background: #f4f7f6; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; background: #fff; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        th { background: #0056b3; color: #fff; }
        .rank-badge { background: #ffc107; color: #000; padding: 3px 8px; border-radius: 4px; font-weight: bold; }
    </style>
</head>
<body>
    <h2>Турнірна таблиця</h2>
    <table>
        <tr>
            <th>Позиція</th>
            <th>Інформація про команду</th>
            <th>Очки</th>
        </tr>
        <?php 
        $rank = 1;
        foreach ($standings as $team): ?>
            <tr>
                <td><?= rankLabel($rank++) ?></td>
                <td><?= $team->getInfo() ?></td>
                <td><?= formatPoints($team->getPoints()) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <h2>Команди з Києва (результат пошуку)</h2>
    <ul>
        <?php foreach ($kyivTeams as $team): ?>
            <li><?= $team->getInfo() ?></li>
        <?php endforeach; ?>
    </ul>
</body>
</html>