// filepath: /simplebase-js/simplebase-js/src/core/token.js
function createToken() {
    return Math.random().toString(36).substr(2, 10);
}

export { createToken };