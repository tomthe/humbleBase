// filepath: /humbleBase-js/humbleBase-js/src/core/database.js
import { apiCall } from './api.js';

export function createTable(token, tablename, columns) {
    const data = {
        query: 'createTable',
        tablename: tablename,
        columns: columns
    };
    return apiCall(`createDB.php?token=${token}`, data);
}

export function writeData(token, tablename, newdata) {
    const data = {
        query: 'newRow',
        tablename: tablename,
        newdata: newdata
    };
    return apiCall(`writeData.php?token=${token}`, data);
}

export function getData(token, tablename) {
    const data = {
        query: 'getall',
        tablename: tablename
    };
    return apiCall(`getData.php?token=${token}`, data);
}

export function updateData(token, tablename, where, newdata) {
    const data = {
        query: 'updateRow',
        tablename: tablename,
        where: where,
        newdata: newdata
    };
    return apiCall(`updateData.php?token=${token}`, data);
}