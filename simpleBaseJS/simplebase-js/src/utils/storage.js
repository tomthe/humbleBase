export function saveToken(token) {
    localStorage.setItem('simplebase_token', token);
}

export function getToken() {
    return localStorage.getItem('simplebase_token');
}

export function clearToken() {
    localStorage.removeItem('simplebase_token');
}