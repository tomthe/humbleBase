let BASE_URL = 'http://localhost'; // Default value, can be modified

/**
 * Sets the base URL for API calls
 * @param {string} url - The base URL to use for all API requests
 */
function setBaseUrl(url) {
    BASE_URL = url;
}

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

export { apiCall, setBaseUrl };