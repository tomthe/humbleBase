// filepath: /humbleBase-js/humbleBase-js/examples/advanced/app.js
import { createToken } from '../../src/core/token.js';
import { createTable, writeData, getData, updateData } from '../../src/core/database.js';
import { saveToken, getToken } from '../../src/utils/storage.js';
import { displayShareUI } from '../../src/ui/share.js';

const BASE_URL = 'http://localhost';
const responseEl = document.getElementById('response');

// Initialize the application
function init() {
    const token = createToken();
    saveToken(token);
    displayShareUI(token);
}

// Create a new table
async function handleCreateTable() {
    const data = {
        query: 'createTable',
        tablename: 'users',
        columns: [
            { cname: 'name', type: 'VARCHAR' },
            { cname: 'age', type: 'INTEGER' }
        ]
    };
    await createTable(data);
}

// Write new data
async function handleWriteData() {
    const data = {
        query: 'newRow',
        tablename: 'users',
        newdata: [
            { cname: 'name', value: 'John' },
            { cname: 'age', value: 30 }
        ]
    };
    await writeData(data);
}

// Get all data
async function handleGetData() {
    const data = {
        query: 'getall',
        tablename: 'users'
    };
    const result = await getData(data);
    responseEl.textContent = JSON.stringify(result, null, 2);
}

// Update existing data
async function handleUpdateData() {
    const data = {
        query: 'updateRow',
        tablename: 'users',
        where: 'name="John"',
        newdata: { cname: 'age', value: 31 }
    };
    await updateData(data);
}

// Event listeners for buttons
document.getElementById('createTableBtn').addEventListener('click', handleCreateTable);
document.getElementById('writeDataBtn').addEventListener('click', handleWriteData);
document.getElementById('getDataBtn').addEventListener('click', handleGetData);
document.getElementById('updateDataBtn').addEventListener('click', handleUpdateData);

// Initialize the app on load
window.onload = init;