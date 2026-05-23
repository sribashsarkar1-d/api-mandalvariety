// assets/js/admin-login.js - Premium UX
document.addEventListener('DOMContentLoaded', function() {
    
    // Enhanced password toggle with smooth animation
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const icon = this.querySelector('i');
            
            input.type = input.type === 'password' ? 'text' : 'password';
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
            
            // Animate icon
            icon.style.transform = 'scale(0.9)';
            setTimeout(() => icon.style.transform = '', 150);
        });
    });

    // Form validation & submission
    const loginForm = document.getElementById('loginForm');
    const signupForm = document.getElementById('signupForm');
    
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            handleFormSubmit(this);
        });
    }
    
    if (signupForm) {
        const password = signupForm.querySelector('input[name="password"]');
        const confirmPassword = signupForm.querySelector('input[name="confirm_password"]');
        
        // Real-time validation
        [password, confirmPassword].forEach(input => {
            input.addEventListener('input', validatePasswordMatch);
        });
        
        signupForm.addEventListener('submit', function(e) {
            handleFormSubmit(this);
        });
    }
    
    function validatePasswordMatch() {
        const password = signupForm.querySelector('input[name="password"]').value;
        const confirmPassword = signupForm.querySelector('input[name="confirm_password"]').value;
        
        if (confirmPassword && password !== confirmPassword) {
            confirmPassword.setCustomValidity('Passwords do not match');
            confirmPassword.style.borderColor = '#ef4444';
        } else {
            confirmPassword.setCustomValidity('');
            confirmPassword.style.borderColor = '';
        }
    }
    
    function handleFormSubmit(form) {
        const submitBtn = form.querySelector('.btn-login, .btn-create');
        const spinner = submitBtn.querySelector('.spinner-border');
        
        // Disable button and show loading
        submitBtn.disabled = true;
        submitBtn.classList.add('disabled');
        if (spinner) spinner.classList.remove('d-none');
        
        // Update button text
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Processing...';
        
        // Re-enable after 3 seconds (form will redirect anyway)
        setTimeout(() => {
            submitBtn.disabled = false;
            submitBtn.classList.remove('disabled');
            submitBtn.innerHTML = originalText;
        }, 3000);
    }
    
    // Add floating animation to card on load
    const loginCard = document.querySelector('.login-card');
    setTimeout(() => {
        loginCard.style.animation = 'float 6s ease-in-out infinite';
    }, 1000);
    
    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && document.activeElement.tagName !== 'BUTTON') {
            document.querySelector('button[type="submit"]')?.click();
        }
    });
});
