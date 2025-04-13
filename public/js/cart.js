// Ensure cart script loads only once
if (!window.cartInitialized) {
    window.cartInitialized = true;

    let isSyncing = false; // Prevent multiple syncs
    let addToCartTimeout = null;

    function debounceAddToCart(productId, name, price, image, quantity) {
        // Validate inputs
        if (!productId || !name || !price || !image || isNaN(quantity)) {
            console.error('Invalid cart item data:', { productId, name, price, image, quantity });
            return;
        }
        clearTimeout(addToCartTimeout);
        addToCartTimeout = setTimeout(() => {
            addToCart(productId, name, price, image, quantity);
        }, 300);
    }

    function parsePrice(price) {
        return parseInt(String(price).replace(/\./g, '').replace(' VND', '')) || 0;
    }

    function formatPrice(price) {
        return new Intl.NumberFormat('vi-VN').format(price) + ' VND';
    }

    function initCart() {
        const cartToggleDesktop = document.getElementById('cart-toggle-desktop');
        const cartToggleMobile = document.getElementById('cart-toggle-mobile');
        const sideCart = document.getElementById('side-cart');
        const cartBackdrop = document.getElementById('cart-backdrop');
        const closeCart = document.getElementById('close-cart');
        const clearCart = document.getElementById('clear-cart');

        if (cartToggleDesktop) {
            cartToggleDesktop.addEventListener('click', toggleCart);
        }
        if (cartToggleMobile) {
            cartToggleMobile.addEventListener('click', toggleCart);
        }
        if (closeCart) {
            closeCart.addEventListener('click', toggleCart);
        }
        if (cartBackdrop) {
            cartBackdrop.addEventListener('click', toggleCart);
        }
        if (clearCart) {
            clearCart.addEventListener('click', function() {
                clearCartItems();
                updateCartDisplay();
            });
        }
    }

    function toggleCart() {
        const sideCart = document.getElementById('side-cart');
        const cartBackdrop = document.getElementById('cart-backdrop');
        
        sideCart.classList.toggle('active');
        cartBackdrop.classList.toggle('active');
        document.body.style.overflow = sideCart.classList.contains('active') ? 'hidden' : '';
    }

    function initializeCart() {
        let cart = JSON.parse(localStorage.getItem('cart') || '[]');
        cart = cart.filter(item => 
            item.id && item.name && item.price && item.image && item.quantity > 0
        );
        localStorage.setItem('cart', JSON.stringify(cart));

        fetch('/mywebsite/app/controllers/CartController.php?action=check_login', {
            credentials: 'include'
        })
        .then(response => response.json())
        .then(data => {
            if (data.loggedIn) {
                syncCartWithServer(cart).then(serverCart => {
                    localStorage.setItem('cart', JSON.stringify(serverCart));
                    updateCartDisplay();
                });
            } else {
                updateCartDisplay();
            }
        })
        .catch(error => {
            console.error('Error checking login:', error);
            updateCartDisplay();
        });
    }

    function addToCart(productId, name, price, image, quantity) {
        quantity = Math.max(1, parseInt(quantity) || 1);
        productId = parseInt(productId);
        price = parsePrice(price); // Convert price to integer

        // Validate inputs
        if (!productId || !name || isNaN(price) || !image) {
            console.error('Cannot add to cart: Invalid data', { productId, name, price, image, quantity });
            return;
        }

        let cart = JSON.parse(localStorage.getItem('cart') || '[]');
        
        const existingItemIndex = cart.findIndex(item => item.id === productId);
        
        if (existingItemIndex !== -1) {
            cart[existingItemIndex].quantity = parseInt(cart[existingItemIndex].quantity) + parseInt(quantity); // Fix quantity addition
        } else {
            cart.push({
                id: productId,
                name: name,
                price: price,
                image: image,
                quantity: quantity
            });
        }
        
        localStorage.setItem('cart', JSON.stringify(cart));
        if (!isSyncing) {
            isSyncing = true;
            syncCartWithServer(cart).then(() => {
                isSyncing = false;
                updateCartDisplay();
            });
        } else {
            updateCartDisplay();
        }
    }

    function updateQuantity(productId, change) {
        let cart = JSON.parse(localStorage.getItem('cart') || '[]');
        const itemIndex = cart.findIndex(item => item.id === parseInt(productId));
        
        if (itemIndex !== -1) {
            cart[itemIndex].quantity = Math.max(0, parseInt(cart[itemIndex].quantity) + change);
            
            if (cart[itemIndex].quantity <= 0) {
                cart.splice(itemIndex, 1);
                localStorage.setItem('cart', JSON.stringify(cart));
                // Gửi yêu cầu xóa trực tiếp tới server
                fetch('/mywebsite/app/controllers/CartController.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'removeItem',
                        productId: productId
                    }),
                    credentials: 'include'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateCartDisplay();
                    } else {
                        console.error('Error removing item:', data.message);
                    }
                })
                .catch(error => console.error('Error removing item:', error));
            } else {
                localStorage.setItem('cart', JSON.stringify(cart));
                if (!isSyncing) {
                    isSyncing = true;
                    syncCartWithServer(cart).then(() => {
                        isSyncing = false;
                        updateCartDisplay();
                    });
                } else {
                    updateCartDisplay();
                }
            }
        }
    }

    function removeItem(productId) {
        let cart = JSON.parse(localStorage.getItem('cart') || '[]');
        cart = cart.filter(item => item.id !== parseInt(productId));
        
        localStorage.setItem('cart', JSON.stringify(cart));
        fetch('/mywebsite/app/controllers/CartController.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'removeItem',
                productId: productId
            }),
            credentials: 'include'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateCartDisplay();
            } else {
                console.error('Error removing item:', data.message);
            }
        })
        .catch(error => console.error('Error removing item:', error));
    }

    function clearCartItems() {
        localStorage.removeItem('cart');
        if (!isSyncing) {
            isSyncing = true;
            syncCartWithServer([]).then(() => {
                isSyncing = false;
                updateCartDisplay();
            });
        } else {
            updateCartDisplay();
        }
    }

    function updateCartDisplay() {
        let cart = JSON.parse(localStorage.getItem('cart') || '[]');
        // Filter out invalid items
        cart = cart.filter(item => 
            item.id && item.name && item.price && item.image && item.quantity > 0
        );
        localStorage.setItem('cart', JSON.stringify(cart));

        const cartItems = document.getElementById('cart-items');
        const cartTotal = document.getElementById('cart-total');
        const cartCountDesktop = document.getElementById('cart-count-desktop');
        const cartCountMobile = document.getElementById('cart-count-mobile');
        
        if (cartItems) {
            cartItems.innerHTML = cart.length === 0
                ? '<div class="cart-empty-message">Your cart is empty</div>'
                : '';
        }
        
        let total = 0;
        let count = 0;
        
        cart.forEach(item => {
            if (cartItems) {
                const cartItem = document.createElement('div');
                cartItem.className = 'cart-item';
                cartItem.innerHTML = `
                    <img src="${item.image}" alt="${item.name}" class="cart-item-image">
                    <div class="cart-item-details ms-2">
                        <a>${item.name}</a>
                        <div class="d-flex justify-content-between">
                            <div>
                                <button class="btn btn-sm btn-outline-secondary me-2" onclick="updateQuantity(${item.id}, -1)">-</button>
                                <span class="item-quantity">${item.quantity}</span>
                                <button class="btn btn-sm btn-outline-secondary ms-2" onclick="updateQuantity(${item.id}, 1)">+</button>
                            </div>
                            <button class="btn btn-sm text-danger" onclick="removeItem(${item.id})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="cart-item-price ms-auto">
                        ${formatPrice(item.price * item.quantity)} <!-- Format price -->
                    </div>
                `;
                cartItems.appendChild(cartItem);
            }
            
            total += item.price * item.quantity;
            count += item.quantity;
        });
        
        if (cartTotal) {
            cartTotal.textContent = formatPrice(total); // Format total price
        }
        
        updateCartCountElements(count);
    }

    function updateCartCountElements(count) {
        const cartCountDesktop = document.getElementById('cart-count-desktop');
        const cartCountMobile = document.getElementById('cart-count-mobile');
        
        if (cartCountDesktop) {
            cartCountDesktop.textContent = count;
            cartCountDesktop.classList.toggle('d-none', count === 0);
        }
        
        if (cartCountMobile) {
            cartCountMobile.textContent = count;
            cartCountMobile.classList.toggle('d-none', count === 0);
        }
    }

    function syncCartWithServer(cart) {
        return fetch('/mywebsite/app/controllers/CartController.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'sync',
                cart: cart
            }),
            credentials: 'include'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.cart) {
                return data.cart.filter(item => 
                    item.id && item.name && item.price && item.image && item.quantity > 0
                );
            } else {
                console.error('Invalid server cart response:', data);
                return cart;
            }
        })
        .catch(error => {
            console.error('Error syncing cart:', error);
            return cart;
        });
    }

    function mergeCartsAfterLogin() {
        const cart = JSON.parse(localStorage.getItem('cart') || '[]');
        
        fetch('/mywebsite/app/controllers/CartController.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'mergeCartsAfterLogin',
                cart: cart
            }),
            credentials: 'include'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.cart) {
                localStorage.setItem('cart', JSON.stringify(data.cart));
                updateCartDisplay();
            }
        })
        .catch(error => console.error('Error merging carts:', error));
    }

    function clearCartOnLogout() {
        localStorage.removeItem('cart');
        updateCartDisplay(); // Clear cart display
        fetch('/mywebsite/app/controllers/CartController.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ action: 'logout' }),
            credentials: 'include'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reset cart display to 0
                const cartCountDesktop = document.getElementById('cart-count-desktop');
                const cartCountMobile = document.getElementById('cart-count-mobile');
                const cartTotal = document.getElementById('cart-total');
                const cartItems = document.getElementById('cart-items');

                if (cartCountDesktop) cartCountDesktop.textContent = '0';
                if (cartCountMobile) cartCountMobile.textContent = '0';
                if (cartTotal) cartTotal.textContent = '0 VNĐ';
                if (cartItems) cartItems.innerHTML = '<div class="cart-empty-message">Your cart is empty</div>';
            }
        })
        .catch(error => console.error('Error during logout:', error));
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        initCart();
        initializeCart();

        // Bind add-to-cart buttons
        document.querySelectorAll('.add-to-cart-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const productId = this.dataset.productId;
                const name = this.dataset.name;
                const price = this.dataset.price;
                const image = this.dataset.image;
                this.disabled = true;
                debounceAddToCart(productId, name, price, image, 1);
                setTimeout(() => { this.disabled = false; }, 500);
            });
        });
    });

    window.mergeCartsAfterLogin = mergeCartsAfterLogin;
    window.clearCartOnLogout = clearCartOnLogout;
    window.debounceAddToCart = debounceAddToCart;
}