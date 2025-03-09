export function saveToken(token) {
    localStorage.setItem('humbleBase_token', token);
}

export function getToken() {
    return localStorage.getItem('humbleBase_token');
}

export function clearToken() {
    localStorage.removeItem('humbleBase_token');
}