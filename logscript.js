function showLoginForm(role) {
    document.getElementById('loginForm').classList.add('active');
    document.getElementById('role').value = role;
    document.getElementById('formTitle').innerText = role.charAt(0).toUpperCase() + role.slice(1) + ' Login';
}
