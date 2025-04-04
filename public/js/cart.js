document.addEventListener('DOMContentLoaded', function() {
    initCart();
    updateCartDisplay();
});

function initCart() {
    const cartToggleDesktop = document.getElementById('cart-toggle-desktop');
    const cartToggleMobile = document.getElementById('cart-toggle-mobile');
    const sideCart = document.getElementById('side-cart');
    const cartBackdrop = document.getElementById('cart-backdrop');
    const closeCart = document.getElementById('close-cart');
    const clearCart = document.getElementById('clear-cart');
    
    // Setup event listeners
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
    
    console.log('Cart initialized with:', {
        cartToggleDesktop: !!cartToggleDesktop,
        cartToggleMobile: !!cartToggleMobile,
        sideCart: !!sideCart,
        cartBackdrop: !!cartBackdrop,
        closeCart: !!closeCart
    });
}

function toggleCart() {
    const sideCart = document.getElementById('side-cart');
    const cartBackdrop = document.getElementById('cart-backdrop');
    
    sideCart.classList.toggle('active');
    cartBackdrop.classList.toggle('active');
    document.body.style.overflow = sideCart.classList.contains('active') ? 'hidden' : '';
}

function addToCart(productId, name, price, image, quantity = 0) {
    let cart = JSON.parse(sessionStorage.getItem('cart')) || [];
    
    const existingItemIndex = cart.findIndex(item => item.id === productId);
    
    if (existingItemIndex !== -1) {
        cart[existingItemIndex].quantity += quantity;
    } else {
        cart.push({
            id: productId,
            name: name,
            price: price,
            image: image,
            quantity: quantity
        });
    }
    
    sessionStorage.setItem('cart', JSON.stringify(cart));
    
    syncCartWithServer(cart);
    
    updateCartDisplay();
}


function updateQuantity(productId, change) {
    let cart = JSON.parse(sessionStorage.getItem('cart')) || [];
    const itemIndex = cart.findIndex(item => item.id === productId);
    
    if (itemIndex !== -1) {
        cart[itemIndex].quantity += change;
        
        if (cart[itemIndex].quantity <= 0) {
            cart.splice(itemIndex, 1);
        }
        
        sessionStorage.setItem('cart', JSON.stringify(cart));
        
        syncCartWithServer(cart);
        
        updateCartDisplay();
    }
}


function removeItem(productId) {
    let cart = JSON.parse(sessionStorage.getItem('cart')) || [];
    const itemIndex = cart.findIndex(item => item.id === productId);
    
    if (itemIndex !== -1) {
        cart.splice(itemIndex, 1);
        
        sessionStorage.setItem('cart', JSON.stringify(cart));
        
        syncCartWithServer(cart);
        
        updateCartDisplay();
    }
}

function clearCartItems() {
    sessionStorage.removeItem('cart');
    syncCartWithServer([]);
}


function updateCartDisplay() {
    const cart = JSON.parse(sessionStorage.getItem('cart')) || [];
    const cartItems = document.getElementById('cart-items');
    const cartTotal = document.getElementById('cart-total');
    const cartCountDesktop = document.getElementById('cart-count-desktop');
    const cartCountMobile = document.getElementById('cart-count-mobile');
    
    if (cartItems) {
        cartItems.innerHTML = '';
    }
    
    if (cart.length === 0) {
        if (cartItems) {
            cartItems.innerHTML = '<div class="cart-empty-message">Your cart is empty</div>';
        }
        if (cartTotal) {
            cartTotal.textContent = '0';
        }
        
        updateCartCountElements(0);
        return;
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
                    ${item.price} VND
                </div>
            `;
            cartItems.appendChild(cartItem);
        }
        
        total += item.price * item.quantity;
        count += item.quantity;
    });
    
    if (cartTotal) {
        cartTotal.textContent = total;
    }
    
    updateCartCountElements(count);
}

function updateCartCountElements(count) {
    const cartCountDesktop = document.getElementById('cart-count-desktop');
    const cartCountMobile = document.getElementById('cart-count-mobile');
    
    if (cartCountDesktop) {
        cartCountDesktop.textContent = count;
        if (count > 0) {
            cartCountDesktop.classList.remove('d-none');
        } else {
            cartCountDesktop.classList.add('d-none');
        }
    }
    
    if (cartCountMobile) {
        cartCountMobile.textContent = count;
        if (count > 0) {
            cartCountMobile.classList.remove('d-none');
        } else {
            cartCountMobile.classList.add('d-none');
        }
    }
}

function syncCartWithServer(cart) {
    fetch('/mywebsite/app/controllers/CartController.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ action: 'updateCart', cart: cart })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Cart synced with server');
        } else {
            console.error('Error syncing cart with server');
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}