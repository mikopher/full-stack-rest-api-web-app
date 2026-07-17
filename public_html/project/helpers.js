// mrc82 - 2026-07-17
// Provides reusable browser-side validation for project account forms.

function validateEmail(input, errors) {
    if (input.validity.valueMissing) {
        errors.push("Enter your email address.");
        return false;
    }

    if (!input.validity.valid) {
        errors.push("Enter a valid email address.");
        return false;
    }

    return true;
}

function validateUsername(input, errors) {
    if (
        input.validity.valueMissing ||
        input.validity.tooShort ||
        input.validity.tooLong ||
        input.validity.patternMismatch
    ) {
        errors.push(
            "Use 3-30 lowercase letters, numbers, underscores, or hyphens."
        );
        return false;
    }

    return true;
}

function validatePassword(input, errors) {
    if (input.validity.valueMissing) {
        errors.push("Enter your password.");
        return false;
    }

    if (input.validity.tooShort) {
        errors.push("Password must be at least 8 characters.");
        return false;
    }

    return true;
}

function validatePasswordsMatch(passwordInput, confirmInput, errors) {
    if (passwordInput.value !== confirmInput.value) {
        errors.push("Passwords must match.");
        return false;
    }

    return true;
}

function showValidationErrors(messageElement, errors) {
    messageElement.innerHTML = errors.join("<br>");
    return errors.length === 0;
}