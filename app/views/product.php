<?php
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../controllers/ProductController.php";

$productController = new ProductController($conn);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$products = $productController->index();

$sort_by = $_GET['sort_by'] ?? 'name_asc';
?>

<!DOCTYPE html>
<html lang="en">
<head>
 <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <title>Product Page</title>
 <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/cart.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/responsive.css">
 <script src="<?= BASE_URL ?>/js/web.js"></script>
<script src="<?= BASE_URL ?>/js/cart.js"></script>
 <link href="<?= BASE_URL ?>/css/node_modules/bootstrap/dist/css/bootstrap.css" rel="stylesheet">
 <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
 <style>
     #main-header .navbar-nav .nav-link, #main-header .navbar-brand {
        background-color: transparent;
        color: black;
        letter-spacing: 1px;
        transition: background-color 0.3s ease, color 0.3s ease;
     }

    #main-header .navbar-nav .nav-link:hover {
        text-underline-offset: 5px;
        text-decoration: underline;
    }

     #main-header:hover .navbar,
    #main-header.scrolled .navbar{
        background-color: white; 
        
     }
    #main-header:hover .navbar-nav .nav-link, #main-header:hover .navbar-brand,
    #main-header.scrolled .navbar-nav .nav-link, #main-header.scrolled .navbar-brand {
    color: black;
    }
    

    
    .search-bar {
        display: flex;
        position: absolute;
        
        top: 100%;
        opacity: 0;
        left: 0;
        width: 100%;
        background-color: white;
        z-index: 1050;
        padding: 0px 20px 40px 20px;
        justify-content: flex-end;
        pointer-events: none;
        height: auto;
        transform: translate(0, -7px);
        transition: transform 0.3s ease;
    }
    .search-bar.active {
        opacity: 1;
        pointer-events: auto;
    }

   

    .search-bar input { 
        width: 300px;
        border: 0px;
        border-bottom: 1px black solid;
        border-radius: 0px;
        text-transform: uppercase;
        letter-spacing: 1px;
        outline: none;
        padding: 8px 35px 8px 0;
    }

    .search-bar input:focus {
        outline: none;
        box-shadow: none;
        border-color: black;
    }

    .search-bar input::placeholder {
        text-transform: uppercase;
    }
    .search-bar button {
        position: absolute;
        right: 0; 
        top: 50%; 
        transform: translateY(-130%); 
        background: none; 
        border: none; 
        padding: 0 30px; 
        cursor: pointer;
        color: #333; 
        font-size: 20px;
        line-height: 1; 

    }
    
    #sortSelect:focus {
        outline: none;
        box-shadow: none;
        border-color: black;
    }

    .search-result-container {
    position: absolute;
    top: 100%;
    width: 400px;
    right: 0;
    background: white;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    z-index: 1060;
    max-height: 400px;
    overflow-y: auto;
    display: none;
    border: 1px solid #ddd;
    border-top: none;
    margin-top: 0;
}

.search-result-container .list-group-item {
    border-left: none;
    border-right: none;
}

.search-result-container .list-group-item:first-child {
    border-top: none;
}   

@media (max-width: 991.98px) {
    .search-result-container {
        margin-top: 20px;
    }
}
    
@media (max-width: 991.98px) {
    

    select.custom-select {
        width: 100%;

        font-size: 0.75rem;
        border-radius: 5px;
    }

    select.custom-select option {
        padding: 10px;
        font-size: 0.5rem;
    }
    
}

footer {
    text-align: center;
    margin-top: auto;
    padding: 10px 0;
    background-color: #f8f9fa;
}

 </style>
</head>
<body>
<header id="main-header">
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container-fluid px-2 px-sm-3 px-md-5">

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" 
                data-bs-target="#navbarContent" aria-controls="navbarContent" 
                aria-expanded="false" aria-label="Toggle navigation">
                ☰
            </button>

            <a class="navbar-brand d-lg-none d-block " href="/mywebsite/public/index.php?page=home">PVI</a>
            
            <a class="navbar-item ">
                <a class="nav-link d-lg-none d-block" href="#" id="search-toggle-mobile">SEARCH</a>
                <div class="search-bar">
                    <form id="search-form-mobile" class="form" action="#" method="get">
                        <input type="text" id="search-input-mobile" class="form-control" autocomplete="off" placeholder="Enter keyword" />
                        <input type="hidden" name="type" value="product" />
                        <button type="submit" class="search-btn">→</button>
                    </form>
                    <div id="search-results-mobile" class="search-result-container"></div>
                </div>
            </a>
            
            <a class="navbar-item ">
                <a class="nav-link d-lg-none d-block">
                    <div id="cart-toggle-mobile" class="bag cursor-pointer" role="button">
                        <p class="mb-0">BAG<span id="cart-count-mobile" class="position-absolute translate-middle badge bg-dark rounded-circle"></span></p>
                    </div>
                </a>
                <div id="side-cart" class="side-cart-panel">
                    <div class="side-cart-header">
                        <h5>Your Shopping Bag</h5>
                        <button id="close-cart" class="btn-close" aria-label="Close"></button>
                    </div>
                    <div class="side-cart-body">
                        <div id="cart-items" class="cart-items">
                            <div class="cart-empty-message">Your cart is empty</div>
                        </div>
                    </div>
                    <div class="side-cart-footer">
                        <div class="d-flex justify-content-between mb-3">
                            <strong>Total:</strong>
                            <span id="cart-total">0 VND</span>
                        </div>
                        <a href="/mywebsite/public/index.php?page=payment" class="btn btn-outline-dark w-100 mb-2">Checkout</a>
                        <button id="clear-cart" class="btn btn-outline-dark w-100">Clear Cart</button>
                    </div>
                </div>
                <div id="cart-backdrop" class="cart-backdrop"></div>
            </a>

            

            <!-- Collapsible content -->
            <div class="collapse navbar-collapse" id="navbarContent">
                <!-- Left menu items -->
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="navbar-item">
                        <a class="nav-link" href="/mywebsite/public/index.php?page=home">HOME</a>
                    </li>
                    <li class="navbar-item">
                        <a class="nav-link" href="#">PRODUCT</a>
                    </li>
                    <li class="navbar-item">
                        <a class="nav-link" href="/mywebsite/public/index.php?page=contact">CONTACT</a>
                    </li>
                </ul>
                
                <a class="navbar-brand d-none d-lg-block position-absolute start-50 translate-middle-x" 
                   href="/mywebsite/public/index.php" style="font-size: clamp(28px, 3vw, 35px);">PVI</a>
                
                <!-- Right menu items -->
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="navbar-item d-none d-lg-block">
                        <a class="nav-link" href="#" id="search-toggle-desktop">SEARCH</a>
                        <div class="search-bar ">
                            <form id="search-form-desktop" class="form" action="#" method="get">
                                <input type="text" id="search-input-desktop" class="form-control" autocomplete="off" placeholder="Enter keyword" />
                                <input type="hidden" name="type" value="product" />
                                <button type="submit" class="search-btn">→</button>
                            </form>
                            <div id="search-results-desktop" class="search-result-container"></div>
                        </div>
                    </li>
                    <li class="navbar-item">
                        <a class="nav-link" href="/mywebsite/public/index.php?page=authentication">ACCOUNT</a>
                    </li>
                    <li class="navbar-item d-none d-lg-block">
                        <a class="nav-link">
                            <div id="cart-toggle-desktop" class="bag cursor-pointer" role="button">
                                <p class="mb-0">BAG<span id="cart-count-desktop" class="position-absolute translate-middle badge bg-dark rounded-circle"></span></p>
                            </div>
                        </a>
                        <div id="side-cart" class="side-cart-panel">
                            <div class="side-cart-header">
                                <h5>Your Shopping Bag</h5>
                                <button id="close-cart" class="btn-close" aria-label="Close"></button>
                            </div>
                            <div class="side-cart-body">
                                <div id="cart-items" class="cart-items">
                                    <div class="cart-empty-message">Your cart is empty</div>
                                </div>
                            </div>
                            <div class="side-cart-footer">
                                <div class="d-flex justify-content-between mb-3">
                                    <strong>Total:</strong>
                                    <span id="cart-total">0 VND</span>
                                </div>
                                <a href="/mywebsite/public/index.php?page=payment" class="btn btn-outline-dark w-100 mb-2">Checkout</a>
                                <button id="clear-cart" class="btn btn-outline-dark w-100">Clear Cart</button>
                            </div>
                        </div>
                        <div id="cart-backdrop" class="cart-backdrop"></div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>


<main>
    <section>
        <div class="container text-center" style="margin-top: 100px; margin-bottom: 100px;">
            <div class="d-flex justify-content-between sort-container ">
                <div class="d-flex ">
                <div class="me-2">
                    <p class="text-center">FILTER</p>
                </div>
                <select class="d-inline form-select form-select-sm w-auto custom-select mb-2" id="sortSelect" onchange="window.location.href='?sort_by=' + this.value">
                    <option value="name_asc" <?= ($sort_by === 'name_asc' ? 'selected' : '') ?>>A to Z</option>
                    <option value="name_desc" <?= ($sort_by === 'name_desc' ? 'selected' : '') ?>>Z to A</option>
                    <option value="price_asc" <?= ($sort_by === 'price_asc' ? 'selected' : '') ?>>Low to High</option>
                    <option value="price_desc" <?= ($sort_by === 'price_desc' ? 'selected' : '') ?>>High to Low</option>
                </select>
                </div>
                

            </div>


            <div class="row row-cols-3 gy-5">
                <?php foreach ($products as $product): ?>
                    <div class="col">
                        <div class="product-card position-relative">
                            <a href="<?php echo BASE_URL; ?>/index.php?page=productDetail&id=<?php echo $product['id']; ?>" class="text-decoration-none text-dark">
                                <div class="product-image-container">
                                    <img src="<?= htmlspecialchars($product["image"]) ?>" alt="<?= htmlspecialchars($product["name"]) ?>" class="img-fluid product-image" loading="lazy">
                                </div>
                                <h5 class="mt-2"><?= htmlspecialchars($product["name"]) ?></h5>
                                <h6><?= htmlspecialchars($product["price"]) ?></h6>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</main>

<footer class="text-center">
    <p>© 2025 My Website. All Rights Reserved.</p>
</footer>
<script>
    const header = document.getElementById("main-header");

    window.addEventListener("scroll", ()  => {
        if (window.scrollY > 50) {
            header.classList.add("scrolled");
        } else {
            header.classList.remove("scrolled");
        }
    });

    document.addEventListener("DOMContentLoaded", function() {

const searchForms = document.querySelectorAll('.form');
const searchInputs = document.querySelectorAll('.form-control');
const searchResultsMobile = document.getElementById('search-results-mobile');
const searchResultsDesktop = document.getElementById('search-results-desktop');
const searchToggles = [document.getElementById('search-toggle-mobile'), document.getElementById('search-toggle-desktop')];
const searchBars = document.querySelectorAll('.search-bar');

console.log('Number of search inputs:', searchInputs.length);
console.log('Number of search bars:', searchBars.length);

searchInputs.forEach((searchInput, index) => {
    const searchResults = index === 0 ? searchResultsMobile : searchResultsDesktop;
    const searchBar = searchBars[index];
    console.log(`Index ${index}: searchBar exists:`, !!searchBar);
    let searchTimeout;

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        if (searchBar) {
            searchBar.classList.add('active');
        }
        const searchTerm = this.value ? this.value.trim() : '';
        if (searchTerm.length > 0) {
            searchTimeout = setTimeout(() => performSearch(searchTerm, searchResults, searchBar), 300);
        } else {
            searchResults.innerHTML = '';
            searchResults.style.display = 'none';
        }
    });

    searchForms[index].addEventListener('submit', function(e) {
        e.preventDefault();
    });

    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target) && !searchToggles[index].contains(e.target)) {
            if (searchBar) {
                searchBar.classList.remove('active');
            }
            searchResults.style.display = 'none';
        }
    });

    searchInput.addEventListener('focus', function() {
        if (searchBar) {
            searchBar.classList.add('active');
        }
    });
});

searchToggles.forEach((toggle, index) => {
if (toggle) {
    const searchBar = searchBars[index];
    const searchInput = searchInputs[index];
    const searchResults = index === 0 ? searchResultsMobile : searchResultsDesktop;

    toggle.addEventListener('click', function (e) {
        e.preventDefault();
        searchBar.classList.toggle('active');
        searchInput.focus();
    });

    const handleMouseLeave = (e) => {
        if (!searchBar.contains(e.relatedTarget) && !toggle.contains(e.relatedTarget)) {
            searchBar.classList.remove('active');
            searchResults.style.display = 'none';
        }
    };

    toggle.addEventListener('mouseleave', handleMouseLeave);
    searchBar.addEventListener('mouseleave', handleMouseLeave);
}
});

function performSearch(searchTerm, searchResults, searchBar) {
    if (!searchTerm || searchTerm.length < 1) {
        searchResults.innerHTML = '';
        searchResults.style.display = 'none';
        return;
    }

    searchResults.innerHTML = '<div class="p-2 text-center"><div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div></div>';
    searchResults.style.display = 'block';
    if (searchBar) {
        searchBar.classList.add('active');
    }

    console.log('Fetching data for:', searchTerm);
    fetch(`/mywebsite/app/views/search.php?query=${encodeURIComponent(searchTerm)}`)
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            console.log('Received data:', data);
            displayResults(data, searchResults, searchBar, searchTerm);
        })
        .catch(error => {
            console.error('Search error:', error);
            searchResults.innerHTML = '<div class="p-2 text-center text-danger">Error loading results</div>';
            searchResults.style.display = 'block';
        });
}

function displayResults(data, searchResults, searchBar, searchTerm) {
    console.log('Displaying results:', data);
    searchResults.innerHTML = '';

    if (!data || data.length === 0) {
        searchResults.innerHTML = `<div class="p-2 text-center">No products found for "${searchTerm}"</div>`;
        searchResults.style.display = 'block';
        console.log('No data to display');
        return;
    }

    const resultsList = document.createElement('div');
    resultsList.className = 'list-group';

    data.forEach(product => {
        const resultItem = document.createElement('a');
        resultItem.className = 'list-group-item list-group-item-action';
        resultItem.href = `${BASE_URL}/index.php?page=productDetail&id=${product.id}`;
        resultItem.innerHTML = `
            <div>${product.name}</div>
        `;
        resultItem.addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = this.href;
            if (searchBar) {
                searchBar.classList.remove('active');
            }
            searchResults.style.display = 'none';
        });

        resultsList.appendChild(resultItem);
    });

    searchResults.appendChild(resultsList);
    searchResults.style.display = 'block';
    console.log('Dropdown should be visible now');
}



});

    

</script>
</body>
</html>