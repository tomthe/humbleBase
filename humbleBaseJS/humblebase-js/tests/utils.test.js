// filepath: /humbleBase-js/humbleBase-js/tests/utils.test.js
import { saveToken, getToken } from '../src/utils/storage.js';

describe('Utility Functions', () => {
    beforeEach(() => {
        localStorage.clear();
    });

    test('saveToken should save a token to localStorage', () => {
        const token = 'testToken123';
        saveToken(token);
        expect(localStorage.getItem('humbleBase_token')).toBe(token);
    });

    test('getToken should retrieve the token from localStorage', () => {
        const token = 'testToken123';
        localStorage.setItem('humbleBase_token', token);
        expect(getToken()).toBe(token);
    });

    test('getToken should return null if no token is saved', () => {
        expect(getToken()).toBeNull();
    });
});