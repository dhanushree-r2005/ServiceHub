function toggleFields() {
    const role = document.getElementById('signup-role').value;
    const userFields = document.getElementById('user-fields');
    const workerFields = document.getElementById('worker-fields');

    // Show/hide fields based on role selection
    if (role === 'user') {
        userFields.classList.remove('hidden');
        workerFields.classList.add('hidden');
    } else if (role === 'worker') {
        workerFields.classList.remove('hidden');
        userFields.classList.add('hidden');
    } else {
        userFields.classList.add('hidden');
        workerFields.classList.add('hidden');
    }
}

// Initialize fields visibility on page load
document.addEventListener('DOMContentLoaded', function () {
    toggleFields();
});
