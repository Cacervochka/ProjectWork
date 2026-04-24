// JavaScript for the frontend
document.addEventListener('DOMContentLoaded', function() {
    const app = document.getElementById('app');
    
    // Example: Fetch users from PHP API
    fetch('php/api.php')
        .then(response => response.json())
        .then(data => {
            app.innerHTML = '<h2>Users:</h2><ul>' + 
                data.map(user => `<li>${user.username} (${user.email})</li>`).join('') + 
                '</ul>';
        })
        .catch(error => console.error('Error:', error));
    
    // Example: Add user form
    const form = document.createElement('form');
    form.innerHTML = `
        <h2>Add User</h2>
        <input type="text" id="username" placeholder="Username" required>
        <input type="email" id="email" placeholder="Email" required>
        <input type="password" id="password" placeholder="Password" required>
        <button type="submit">Add User</button>
    `;
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const username = document.getElementById('username').value;
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        
        fetch('php/api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username, email, password })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('User added successfully!');
                location.reload();
            } else {
                alert('Error: ' + data.error);
            }
        });
    });
    app.appendChild(form);
});