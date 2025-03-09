// filepath: /humbleBase-js/humbleBase-js/src/ui/components.js
export function createButton(label, onClick) {
    const button = document.createElement('button');
    button.textContent = label;
    button.addEventListener('click', onClick);
    return button;
}

export function createInput(placeholder) {
    const input = document.createElement('input');
    input.placeholder = placeholder;
    return input;
}

export function createLabel(text) {
    const label = document.createElement('label');
    label.textContent = text;
    return label;
}

export function createDiv() {
    return document.createElement('div');
}

export function createForm(onSubmit) {
    const form = document.createElement('form');
    form.addEventListener('submit', (event) => {
        event.preventDefault();
        onSubmit();
    });
    return form;
}

export function createTokenInput() {
    const input = createInput('Enter your token');
    input.id = 'token';
    return input;
}

export function createShareUrlInput() {
    const input = createInput('Share URL');
    input.id = 'share-url';
    return input;
}

export function createResponseElement() {
    const responseEl = createDiv();
    responseEl.id = 'response';
    return responseEl;
}