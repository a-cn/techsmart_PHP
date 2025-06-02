function validatePasswordForm() {
    const newPassword = document.getElementById("newPassword").value;
    const confirmPassword = document.getElementById("confirmPassword").value;
    const errorDiv = document.getElementById("error-message");

    if (newPassword !== confirmPassword) {
        errorDiv.textContent = "As senhas não coincidem.";
        return false;
    }

    errorDiv.textContent = "";
    return true;
}