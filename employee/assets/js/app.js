// assets/js/app.js
const App = {
    init: function() {
        console.log("App initialized");
        this.bindEvents();
    },

    bindEvents: function() {
        // Go back functionality
        const backBtn = document.getElementById('back-btn');
        if (backBtn) {
            backBtn.addEventListener('click', () => {
                window.history.back();
            });
        }
    },

    showToast: function(message, type = 'success') {
        // We will implement Bootstrap toast here
        alert(message); // fallback for now
    },

    formatCurrency: function(amount) {
        return '₹' + parseFloat(amount).toFixed(2);
    }
};

document.addEventListener('DOMContentLoaded', () => {
    App.init();
});
