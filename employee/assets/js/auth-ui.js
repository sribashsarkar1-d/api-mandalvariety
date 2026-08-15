// assets/js/auth-ui.js

document.addEventListener('DOMContentLoaded', () => {
    
    // Toggle Password Visibility
    const togglePasswordBtns = document.querySelectorAll('.auth-password-toggle');
    
    togglePasswordBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });
    });

    // Role Tab Switching
    const roleTabs = document.querySelectorAll('.auth-tab');
    const roleInput = document.getElementById('role'); // Existing hidden input
    
    if (roleTabs.length > 0 && roleInput) {
        roleTabs.forEach(tab => {
            tab.addEventListener('click', function() {
                // Remove active class from all
                roleTabs.forEach(t => t.classList.remove('active'));
                
                // Add active class to clicked
                this.classList.add('active');
                
                // Update hidden input value
                const role = this.getAttribute('data-role');
                roleInput.value = role;
            });
        });
    }
});
