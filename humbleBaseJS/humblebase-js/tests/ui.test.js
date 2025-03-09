import { createToken } from '../src/core/token.js';
import { saveToken, getToken } from '../src/utils/storage.js';
import { createTable, writeData, getData, updateData } from '../src/core/database.js';
import { displayShareUI } from '../src/ui/share.js';

describe('humbleBase UI Tests', () => {
    beforeEach(() => {
        localStorage.clear();
    });

    test('should generate a random token', () => {
        const token = createToken();
        expect(token).toBeDefined();
        expect(typeof token).toBe('string');
        expect(token.length).toBeGreaterThan(0);
    });

    test('should save and retrieve token from localStorage', () => {
        const token = createToken();
        saveToken(token);
        const retrievedToken = getToken();
        expect(retrievedToken).toBe(token);
    });

    test('should create a table', async () => {
        const data = {
            query: 'createTable',
            tablename: 'test_users',
            columns: [
                { cname: 'name', type: 'VARCHAR' },
                { cname: 'age', type: 'INTEGER' }
            ]
        };
        const response = await createTable(data);
        expect(response).toHaveProperty('success', true);
    });

    test('should write data to the table', async () => {
        const data = {
            query: 'newRow',
            tablename: 'test_users',
            newdata: [
                { cname: 'name', value: 'Alice' },
                { cname: 'age', value: 25 }
            ]
        };
        const response = await writeData(data);
        expect(response).toHaveProperty('success', true);
    });

    test('should retrieve data from the table', async () => {
        const data = {
            query: 'getall',
            tablename: 'test_users'
        };
        const response = await getData(data);
        expect(response).toHaveProperty('data');
        expect(Array.isArray(response.data)).toBe(true);
    });

    test('should update data in the table', async () => {
        const data = {
            query: 'updateRow',
            tablename: 'test_users',
            where: 'name="Alice"',
            newdata: { cname: 'age', value: 26 }
        };
        const response = await updateData(data);
        expect(response).toHaveProperty('success', true);
    });

    test('should display share UI', () => {
        const shareUI = displayShareUI();
        expect(shareUI).toBeDefined();
        expect(shareUI).toContain('Share this URL');
    });
});