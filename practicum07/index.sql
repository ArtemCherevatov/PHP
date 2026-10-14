
USE practicum4;

-- 1. Перевірка плану виконання запиту ДО створення індексу
EXPLAIN SELECT * FROM teams WHERE city = 'Київ';

-- 2. Створення індексу на стовпець фільтрації city
CREATE INDEX idx_teams_city ON teams (city);

-- 3. Перевірка плану виконання запиту ПІСЛЯ створення індексу
EXPLAIN SELECT * FROM teams WHERE city = 'Київ';