// filepath: /simplebase-js/simplebase-js/src/ui/share.js
import { saveToken } from '../utils/storage.js';
import { createToken } from '../core/token.js';

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

export { createShareUI };