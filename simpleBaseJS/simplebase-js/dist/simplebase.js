// filepath: /simplebase-js/simplebase-js/dist/simplebase.js
import { apiCall } from '../src/core/api.js';
import { createToken } from '../src/core/token.js';
import { createTable, writeData, getData, updateData } from '../src/core/database.js';
import { displayShareUI } from '../src/ui/share.js';
import { saveToken, getToken } from '../src/utils/storage.js';
import { validateToken, validateURL } from '../src/utils/validation.js';

const SimpleBase = {
    apiCall,
    createToken,
    createTable,
    writeData,
    getData,
    updateData,
    displayShareUI,
    saveToken,
    getToken,
    validateToken,
    validateURL
};

export default SimpleBase;