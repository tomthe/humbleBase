import { createTable, writeData, getData, updateData } from '../src/core/database';
import { createToken } from '../src/core/token';
import { saveToken, getToken } from '../src/utils/storage';

describe('humbleBase Core Functions', () => {
    let token;

    beforeEach(() => {
        token = createToken();
        saveToken(token);
    });

    afterEach(() => {
        localStorage.clear();
    });

    test('should create a new table', async () => {
        const response = await createTable('testTable', [
            { cname: 'name', type: 'VARCHAR' },
            { cname: 'age', type: 'INTEGER' }
        ]);
        expect(response).toHaveProperty('success', true);
    });

    test('should write data to the table', async () => {
        await createTable('testTable', [
            { cname: 'name', type: 'VARCHAR' },
            { cname: 'age', type: 'INTEGER' }
        ]);
        const response = await writeData('testTable', [
            { cname: 'name', value: 'Alice' },
            { cname: 'age', value: 25 }
        ]);
        expect(response).toHaveProperty('success', true);
    });

    test('should get data from the table', async () => {
        await createTable('testTable', [
            { cname: 'name', type: 'VARCHAR' },
            { cname: 'age', type: 'INTEGER' }
        ]);
        await writeData('testTable', [
            { cname: 'name', value: 'Bob' },
            { cname: 'age', value: 30 }
        ]);
        const response = await getData('testTable');
        expect(response).toHaveProperty('data');
        expect(response.data).toHaveLength(1);
        expect(response.data[0]).toHaveProperty('name', 'Bob');
    });

    test('should update data in the table', async () => {
        await createTable('testTable', [
            { cname: 'name', type: 'VARCHAR' },
            { cname: 'age', type: 'INTEGER' }
        ]);
        await writeData('testTable', [
            { cname: 'name', value: 'Charlie' },
            { cname: 'age', value: 35 }
        ]);
        const response = await updateData('testTable', 'name="Charlie"', { cname: 'age', value: 36 });
        expect(response).toHaveProperty('success', true);
    });
});