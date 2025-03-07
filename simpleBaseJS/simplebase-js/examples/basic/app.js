const BASE_URL = 'http://localhost'; // Adjust based on your server

import { apiCall } from '../../src/core/api.js';
import { createToken } from '../../src/core/token.js';
import { saveToken, getToken } from '../../src/utils/storage.js';
import { createTable, writeData, getData, updateData } from '../../src/core/database.js';
import { displayShareUI } from '../../src/ui/share.js';

document.addEventListener('DOMContentLoaded', () => {
    const token = createToken();
    saveToken(token);
    displayShareUI(token);

    document.getElementById('createTableBtn').addEventListener('click', () => {
        createTable();
    });

    document.getElementById('writeDataBtn').addEventListener('click', () => {
        writeData();
    });

    document.getElementById('getDataBtn').addEventListener('click', () => {
        getData();
    });

    document.getElementById('updateDataBtn').addEventListener('click', () => {
        updateData();
    });
});