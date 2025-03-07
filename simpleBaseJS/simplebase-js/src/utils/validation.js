export function validateToken(token) {
    const tokenPattern = /^[a-zA-Z0-9]{10,}$/; // Example pattern for a valid token
    return tokenPattern.test(token);
}

export function validateUrl(url) {
    try {
        new URL(url);
        return true;
    } catch {
        return false;
    }
}