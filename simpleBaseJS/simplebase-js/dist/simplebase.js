(function (global, factory) {
    typeof exports === 'object' && typeof module !== 'undefined' ? factory(exports) :
    typeof define === 'function' && define.amd ? define(['exports'], factory) :
    (global = typeof globalThis !== 'undefined' ? globalThis : global || self, factory(global.SimpleBase = {}));
})(this, (function (exports) { 'use strict';

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

    // filepath: /simplebase-js/simplebase-js/src/core/token.js
    function createToken() {
        return Math.random().toString(36).substr(2, 10);
    }

    // filepath: /simplebase-js/simplebase-js/src/core/database.js

    function createTable(token, tablename, columns) {
        const data = {
            query: 'createTable',
            tablename: tablename,
            columns: columns
        };
        return apiCall(`createDB.php?token=${token}`, data);
    }

    function writeData(token, tablename, newdata) {
        const data = {
            query: 'newRow',
            tablename: tablename,
            newdata: newdata
        };
        return apiCall(`writeData.php?token=${token}`, data);
    }

    function getData(token, tablename) {
        const data = {
            query: 'getall',
            tablename: tablename
        };
        return apiCall(`getData.php?token=${token}`, data);
    }

    function updateData(token, tablename, where, newdata) {
        const data = {
            query: 'updateRow',
            tablename: tablename,
            where: where,
            newdata: newdata
        };
        return apiCall(`updateData.php?token=${token}`, data);
    }

    function saveToken(token) {
        localStorage.setItem('simplebase_token', token);
    }

    function getToken() {
        return localStorage.getItem('simplebase_token');
    }

    // filepath: /simplebase-js/simplebase-js/src/ui/share.js

    function createShareUI() {
        const container = document.createElement('div');
        container.id = 'share-ui';

        const tokenInput = document.createElement('input');
        tokenInput.type = 'text';
        tokenInput.placeholder = 'Enter your token';
        tokenInput.id = 'token-input';

        const urlInput = document.createElement('input');
        urlInput.type = 'text';
        urlInput.placeholder = 'Shareable URL';
        urlInput.id = 'url-input';

        const generateButton = document.createElement('button');
        generateButton.textContent = 'Generate Token';
        generateButton.onclick = () => {
            const token = createToken();
            tokenInput.value = token;
            saveToken(token);
            updateShareableURL(token);
        };

        const shareButton = document.createElement('button');
        shareButton.textContent = 'Share URL';
        shareButton.onclick = () => {
            const url = urlInput.value;
            if (url) {
                navigator.clipboard.writeText(url).then(() => {
                    alert('URL copied to clipboard!');
                });
            }
        };

        container.appendChild(tokenInput);
        container.appendChild(urlInput);
        container.appendChild(generateButton);
        container.appendChild(shareButton);
        document.body.appendChild(container);
    }

    function updateShareableURL(token) {
        const baseUrl = window.location.origin; // Adjust as necessary
        const urlInput = document.getElementById('url-input');
        urlInput.value = `${baseUrl}?token=${token}`;
    }

    function validateToken(token) {
        const tokenPattern = /^[a-zA-Z0-9]{10,}$/; // Example pattern for a valid token
        return tokenPattern.test(token);
    }

    exports.apiCall = apiCall;
    exports.createShareUI = createShareUI;
    exports.createTable = createTable;
    exports.createToken = createToken;
    exports.getData = getData;
    exports.getToken = getToken;
    exports.saveToken = saveToken;
    exports.updateData = updateData;
    exports.validateToken = validateToken;
    exports.writeData = writeData;

    Object.defineProperty(exports, '__esModule', { value: true });

}));
//# sourceMappingURL=simplebase.js.map
