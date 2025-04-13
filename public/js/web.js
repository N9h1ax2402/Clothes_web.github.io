function handleLogin(userData) {
    // ... (existing login logic)
    fetch('/mywebsite/app/controllers/AuthController.php', {
        method: 'POST',
        body: JSON.stringify({ action: 'login', ...userData }),
        credentials: 'include'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Trigger cart merge
            window.mergeCartsAfterLogin();
            // Dispatch custom event for product.php
            document.dispatchEvent(new Event('userLoggedIn'));
        }
    });
}

function logout() {
    fetch('/mywebsite/app/controllers/AuthController.php?action=logout', {
        method: 'POST',
        credentials: 'include'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.clearCartOnLogout(); // Clear cart display
            const cartCountDesktop = document.getElementById('cart-count-desktop');
            const cartCountMobile = document.getElementById('cart-count-mobile');
            const cartTotal = document.getElementById('cart-total');
            const cartItems = document.getElementById('cart-items');

            if (cartCountDesktop) cartCountDesktop.textContent = '0';
            if (cartCountMobile) cartCountMobile.textContent = '0';
            if (cartTotal) cartTotal.textContent = '0 VNĐ';
            if (cartItems) cartItems.innerHTML = '<div class="cart-empty-message">Your cart is empty</div>';

            window.location.href = '/mywebsite/public/index.php?page=home';
        }
    })
    .catch(error => console.error('Error during logout:', error));
}
