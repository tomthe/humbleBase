// filepath: /simplebase-js/simplebase-js/src/core/api.js
const BASE_URL = 'http://localhost'; // Adjust based on your server

async function apiCall(script, data) {
    try {
        const response = await fetch(`${BASE_URL}/${script}?token=${data.token}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await response.json();
        return result;
    } catch (error) {
        throw new Error(`API call failed: ${error.message}`);
    }
}

export { apiCall };