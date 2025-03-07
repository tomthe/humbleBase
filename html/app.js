const BASE_URL = 'http://localhost'; // Adjust based on your server
const responseEl = document.getElementById('response');

function getToken() {
    return document.getElementById('token').value;
}

async function apiCall(script, data) {
    try {
        const response = await fetch(`${BASE_URL}/${script}?token=${getToken()}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await response.json();
        responseEl.textContent = JSON.stringify(result, null, 2);
    } catch (error) {
        responseEl.textContent = `Error: ${error.message}`;
    }
}

function createTable() {
    const data = {
        query: 'createTable',
        tablename: 'users',
        columns: [
            { cname: 'name', type: 'VARCHAR' },
            { cname: 'age', type: 'INTEGER' }
        ]
    };
    apiCall('createDB.php', data);
}

function writeData() {
    const data = {
        query: 'newRow',
        tablename: 'users',
        newdata: [
            { cname: 'name', value: 'John' },
            { cname: 'age', value: 30 }
        ]
    };
    apiCall('writeData.php', data);
}

function getData() {
    const data = {
        query: 'getall',
        tablename: 'users'
    };
    apiCall('getData.php', data);
}

function updateData() {
    const data = {
        query: 'updateRow',
        tablename: 'users',
        where: 'name="John"',
        newdata: { cname: 'age', value: 31 }
    };
    apiCall('updateData.php', data);
}

function viewAdmin() {
    window.open(`${BASE_URL}/admin.php?token=${getToken()}`, '_blank');
}