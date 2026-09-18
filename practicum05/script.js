document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('search');
    const addForm = document.getElementById('addForm');
    const errorMsg = document.getElementById('error-msg');

    // Завантажити список через AJAX при відкритті сторінки[cite: 12]
    loadList();

    // Живий пошук за містом без перезавантаження[cite: 12]
    searchInput.addEventListener('input', (e) => {
        loadList(e.target.value);
    });

    // Додати запис через AJAX без перезавантаження сторінки[cite: 12]
    addForm.addEventListener('submit', async (e) => {
        e.preventDefault(); 
        errorMsg.textContent = '';
        
        const formData = new FormData(addForm);
        
        try {
            const response = await fetch('api_add.php', {
                method: 'POST',
                body: formData
            });
            
            // Перевірка response.ok для виявлення помилок сервера[cite: 12]
            if (!response.ok) throw new Error('Помилка при додаванні команди');
            
            addForm.reset();
            // Турнірна таблиця перераховується й оновлюється одразу[cite: 12]
            loadList(searchInput.value); 
        } catch (error) {
            errorMsg.textContent = error.message;
        }
    });
});

async function loadList(query = '') {
    const errorMsg = document.getElementById('error-msg');
    try {
        // Виконання GET-запиту з параметром пошуку[cite: 12]
        const response = await fetch('api_list.php?q=' + encodeURIComponent(query));
        
        if (!response.ok) throw new Error('Помилка завантаження даних');
        
        // Перетворення відповіді у JSON[cite: 12]
        const data = await response.json();
        renderTable(data);
        errorMsg.textContent = '';
    } catch (error) {
        // Опрацювання мережевих помилок і виведення на сторінку[cite: 12]
        errorMsg.textContent = error.message;
    }
}

// Оновлення DOM: створення елементів без перезавантаження[cite: 12]
function renderTable(teams) {
    const tbody = document.getElementById('results');
    tbody.innerHTML = ''; 
    
    teams.forEach(team => {
        const tr = document.createElement('tr');
        
        // Екранування даних здійснюється через textContent
        const tdId = document.createElement('td'); tdId.textContent = team.id;
        const tdName = document.createElement('td'); tdName.textContent = team.name;
        const tdCity = document.createElement('td'); tdCity.textContent = team.city;
        const tdPoints = document.createElement('td'); tdPoints.textContent = team.points;
        
        tr.appendChild(tdId);
        tr.appendChild(tdName);
        tr.appendChild(tdCity);
        tr.appendChild(tdPoints);
        
        tbody.appendChild(tr);
    });
}