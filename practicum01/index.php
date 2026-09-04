<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$teams = [
    ['name' => 'Динамо', 'city' => 'Київ', 'points' => 18, 'played' => 10],
    ['name' => 'Шахтар', 'city' => 'Донецьк', 'points' => 25, 'played' => 10],
    ['name' => 'Зоря', 'city' => 'Луганськ', 'points' => 21, 'played' => 10],
    ['name' => 'Ворскла', 'city' => 'Полтава', 'points' => 15, 'played' => 9],
];

function formatTeam(array $team): string {
    return "<strong>{$team['name']}</strong> ({$team['city']})";
}

$totalPlayedMatches = 0;
foreach ($teams as $team) {
    $totalPlayedMatches += $team['played'];
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Спортивна ліга</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Турнірна таблиця (Спортивна ліга)</h1>
    
    <table>
        <thead>
            <tr>
                <th>Команда</th>
                <th>Місто</th>
                <th>Очки</th>
                <th>Зіграно матчів</th>
                <th>Статус</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($teams as $team) {
                $label = 'В нормі';
                $class = '';
                if ($team['points'] >= 20) {
                    $label = 'Лідер туру';
                    $class = 'leader';
                }
                echo '<tr>';
                echo '<td>' . formatTeam($team) . '</td>';
                echo '<td>' . $team['city'] . '</td>';
                echo '<td>' . $team['points'] . '</td>';
                echo '<td>' . $team['played'] . '</td>';
                echo '<td class="' . $class . '">' . $label . '</td>';
                echo '</tr>';
            }
            ?>
        </tbody>
    </table>

    <h2>Статистика ліги</h2>
    <p>Сумарна кількість зіграних матчів по всіх командах: <strong><?= $totalPlayedMatches ?></strong></p>
</body>
</html>