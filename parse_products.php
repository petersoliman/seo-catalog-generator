<?php
// Parse Bosch Professional Products from Database
// This script displays all products from the MySQL database in a beautiful HTML format

require_once 'config.php';

// Check if this is an AJAX request for search/filtering
$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';

if (!$isAjax) {
    // Display the full HTML page
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bosch Professional Products Database</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            font-weight: 300;
        }
        
        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .stats-bar {
            background: #f8f9fa;
            padding: 20px 30px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .controls {
            padding: 20px 30px;
            background: white;
            border-bottom: 1px solid #e9ecef;
        }
        
        .search-filter {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .search-box {
            flex: 1;
            min-width: 300px;
            position: relative;
        }
        
        .search-box input {
            width: 100%;
            padding: 12px 20px 12px 45px;
            border: 2px solid #e9ecef;
            border-radius: 25px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .search-box input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }
        
        .filter-select {
            padding: 12px 20px;
            border: 2px solid #e9ecef;
            border-radius: 25px;
            font-size: 1rem;
            background: white;
            cursor: pointer;
            min-width: 150px;
        }
        
        .filter-select:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .products-grid {
            padding: 30px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
            min-height: 500px;
        }
        
        .product-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            overflow: hidden;
            transition: all 0.3s ease;
            border: 1px solid #e9ecef;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }
        
        .product-image {
            height: 200px;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        .product-image img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            transition: transform 0.3s ease;
        }
        
        .product-card:hover .product-image img {
            transform: scale(1.05);
        }
        
        .product-image .no-image {
            color: #6c757d;
            font-size: 3rem;
            opacity: 0.5;
        }
        
        .product-info {
            padding: 20px;
        }
        
        .product-name {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 10px;
            line-height: 1.4;
        }
        
        .product-category {
            background: #667eea;
            color: white;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.8rem;
            display: inline-block;
            margin-bottom: 15px;
        }
        
        .product-details {
            margin-bottom: 15px;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }
        
        .detail-label {
            color: #6c757d;
            font-weight: 500;
        }
        
        .detail-value {
            color: #2c3e50;
            font-weight: 600;
        }
        
        .detail-row .detail-value.price {
            color: #e74c3c;
            font-weight: bold;
            font-size: 1.1em;
        }
        
        .product-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5a6fd8;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .loading {
            text-align: center;
            padding: 50px;
            color: #6c757d;
        }
        
        .no-results {
            text-align: center;
            padding: 50px;
            color: #6c757d;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            padding: 30px;
            border-top: 1px solid #e9ecef;
        }
        
        .page-btn {
            padding: 10px 15px;
            border: 1px solid #e9ecef;
            background: white;
            color: #6c757d;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .page-btn:hover,
        .page-btn.active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        
        .page-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        @media (max-width: 768px) {
            .container {
                margin: 10px;
                border-radius: 15px;
            }
            
            .header h1 {
                font-size: 2rem;
            }
            
            .stats-bar {
                flex-direction: column;
                text-align: center;
            }
            
            .search-filter {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-box {
                min-width: auto;
            }
            
            .products-grid {
                grid-template-columns: 1fr;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-tools"></i> Bosch Professional Products</h1>
            <p>Complete database of professional power tools and equipment</p>
        </div>
        
        <div class="stats-bar" id="statsBar">
            <div class="stat-item">
                <div class="stat-number" id="totalProducts">-</div>
                <div class="stat-label">Total Products</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" id="totalCategories">-</div>
                <div class="stat-label">Categories</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" id="totalBrands">-</div>
                <div class="stat-label">Brands</div>
            </div>
        </div>
        
        <div class="controls">
            <div class="search-filter">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Search products by name, SKU, or category...">
                </div>
                <select class="filter-select" id="categoryFilter">
                    <option value="">All Categories</option>
                </select>
                <select class="filter-select" id="brandFilter">
                    <option value="">All Brands</option>
                </select>
            </div>
        </div>
        
        <div class="products-grid" id="productsGrid">
            <div class="loading">
                <i class="fas fa-spinner fa-spin fa-2x"></i>
                <p>Loading products...</p>
            </div>
        </div>
        
        <div class="pagination" id="pagination"></div>
    </div>

    <script>
        let allProducts = [];
        let filteredProducts = [];
        let currentPage = 1;
        const productsPerPage = 12;
        
        // Load products on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadProducts();
            setupEventListeners();
        });
        
        function setupEventListeners() {
            document.getElementById('searchInput').addEventListener('input', debounce(filterProducts, 300));
            document.getElementById('categoryFilter').addEventListener('change', filterProducts);
            document.getElementById('brandFilter').addEventListener('change', filterProducts);
        }
        
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }
        
        function loadProducts() {
            fetch('parse_products.php?ajax=1')
                .then(response => response.json())
                .then(data => {
                    allProducts = data.products;
                    filteredProducts = [...allProducts];
                    console.log('Loaded products:', allProducts.length);
                    console.log('Sample product:', allProducts[0]);
                    updateStats(data.stats);
                    populateFilters(data.stats);
                    displayProducts();
                })
                .catch(error => {
                    console.error('Error loading products:', error);
                    document.getElementById('productsGrid').innerHTML = 
                        '<div class="no-results">Error loading products. Please try again.</div>';
                });
        }
        
        function updateStats(stats) {
            document.getElementById('totalProducts').textContent = stats.totalProducts;
            document.getElementById('totalCategories').textContent = stats.totalCategories;
            document.getElementById('totalBrands').textContent = stats.totalBrands;
        }
        
        function populateFilters(stats) {
            const categoryFilter = document.getElementById('categoryFilter');
            const brandFilter = document.getElementById('brandFilter');
            
            // Populate category filter
            stats.categories.forEach(category => {
                const option = document.createElement('option');
                option.value = category.name;
                option.textContent = `${category.name} (${category.count})`;
                categoryFilter.appendChild(option);
            });
            
            // Populate brand filter
            stats.brands.forEach(brand => {
                const option = document.createElement('option');
                option.value = brand.name;
                option.textContent = `${brand.name} (${brand.count})`;
                brandFilter.appendChild(option);
            });
        }
        
        function filterProducts() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const categoryFilter = document.getElementById('categoryFilter').value;
            const brandFilter = document.getElementById('brandFilter').value;
            
            // Special search for SKU
            if (searchTerm && searchTerm.length >= 3) {
                const skuMatch = allProducts.find(product => 
                    product.sku.toLowerCase() === searchTerm.toLowerCase()
                );
                if (skuMatch) {
                    console.log('Found SKU match:', skuMatch);
                }
            }
            
            filteredProducts = allProducts.filter(product => {
                const matchesSearch = !searchTerm || 
                    product.name.toLowerCase().includes(searchTerm) ||
                    product.sku.toLowerCase().includes(searchTerm) ||
                    product.category.toLowerCase().includes(searchTerm);
                
                const matchesCategory = !categoryFilter || product.category === categoryFilter;
                const matchesBrand = !brandFilter || product.brand === brandFilter;
                
                return matchesSearch && matchesCategory && matchesBrand;
            });
            
            currentPage = 1;
            displayProducts();
        }
        
        function displayProducts() {
            const startIndex = (currentPage - 1) * productsPerPage;
            const endIndex = startIndex + productsPerPage;
            const productsToShow = filteredProducts.slice(startIndex, endIndex);
            
            const productsGrid = document.getElementById('productsGrid');
            
            if (productsToShow.length === 0) {
                productsGrid.innerHTML = '<div class="no-results">No products found matching your criteria.</div>';
                document.getElementById('pagination').innerHTML = '';
                return;
            }
            
            productsGrid.innerHTML = productsToShow.map(product => `
                <div class="product-card">
                    <div class="product-image">
                        ${product.main_image_url ? 
                            `<img src="${product.main_image_url}" alt="${product.name}" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">` : 
                            ''
                        }
                        <div class="no-image" style="${product.main_image_url ? 'display: none;' : ''}">
                            <i class="fas fa-tools"></i>
                        </div>
                    </div>
                    <div class="product-info">
                        <div class="product-name">${product.name}</div>
                        <div class="product-category">${product.category}</div>
                        <div class="product-details">
                            <div class="detail-row">
                                <span class="detail-label">SKU:</span>
                                <span class="detail-value">${product.sku}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Model:</span>
                                <span class="detail-value">${product.model_number}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Brand:</span>
                                <span class="detail-value">${product.brand}</span>
                            </div>
                            ${product.more_details && product.more_details !== 'null' ? `
                            <div class="detail-row">
                                <span class="detail-label">Price:</span>
                                <span class="detail-value price">${getPriceDisplay(product.more_details)}</span>
                            </div>
                            ` : ''}
                        </div>
                        <div class="product-actions">
                            <a href="${product.url}" target="_blank" class="btn btn-primary">
                                <i class="fas fa-external-link-alt"></i> View Product
                            </a>
                            <button class="btn btn-secondary" onclick="showProductDetails('${product.id}')">
                                <i class="fas fa-info-circle"></i> Details
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');
            
            displayPagination();
        }
        
        function displayPagination() {
            const totalPages = Math.ceil(filteredProducts.length / productsPerPage);
            const pagination = document.getElementById('pagination');
            
            if (totalPages <= 1) {
                pagination.innerHTML = '';
                return;
            }
            
            let paginationHTML = '';
            
            // Previous button
            paginationHTML += `
                <button class="page-btn" onclick="changePage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>
                    <i class="fas fa-chevron-left"></i> Previous
                </button>
            `;
            
            // Page numbers
            for (let i = 1; i <= totalPages; i++) {
                if (i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)) {
                    paginationHTML += `
                        <button class="page-btn ${i === currentPage ? 'active' : ''}" onclick="changePage(${i})">
                            ${i}
                        </button>
                    `;
                } else if (i === currentPage - 3 || i === currentPage + 3) {
                    paginationHTML += '<span class="page-btn" style="border: none; background: none;">...</span>';
                }
            }
            
            // Next button
            paginationHTML += `
                <button class="page-btn" onclick="changePage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}>
                    Next <i class="fas fa-chevron-right"></i>
                </button>
            `;
            
            pagination.innerHTML = paginationHTML;
        }
        
        function changePage(page) {
            currentPage = page;
            displayProducts();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
        
        function getPriceDisplay(moreDetails) {
            if (!moreDetails || moreDetails === 'null' || moreDetails === null) {
                return 'Price not available';
            }
            try {
                const details = JSON.parse(moreDetails);
                if (details.price !== undefined) {
                    if (details.price === 0) {
                        return 'Contact for pricing';
                    }
                    return `${details.price}${details.priceCurrency ? ' ' + details.priceCurrency : ''}`;
                }
                return 'Price not available';
            } catch (e) {
                return 'Price not available';
            }
        }
        
        function showProductDetails(productId) {
            const product = allProducts.find(p => p.id == productId);
            if (product) {
                let details = `Product Details:\n\nName: ${product.name}\nSKU: ${product.sku}\nCategory: ${product.category}\nBrand: ${product.brand}\nDescription: ${product.description}\nArabic Name: ${product.name_ar}\nArabic Description: ${product.description_ar}`;
                
                // Add more_details if available
                if (product.more_details && product.more_details !== 'null' && product.more_details !== null) {
                    try {
                        const moreDetails = JSON.parse(product.more_details);
                        details += '\n\n📋 Additional Details:';
                        
                        if (moreDetails.price !== undefined) {
                            details += `\nPrice: ${moreDetails.price === 0 ? 'Contact for pricing' : moreDetails.price}`;
                        }
                        
                        if (moreDetails.priceCurrency) {
                            details += `\nCurrency: ${moreDetails.priceCurrency}`;
                        }
                        
                        if (moreDetails.availability) {
                            details += `\nAvailability: ${moreDetails.availability || 'Contact for availability'}`;
                        }
                        
                        if (moreDetails.itemCondition) {
                            details += `\nCondition: ${moreDetails.itemCondition.replace('https://schema.org/', '')}`;
                        }
                        
                        // Add raw JSON for developers
                        details += `\n\n🔧 Raw Data:\n${JSON.stringify(moreDetails, null, 2)}`;
                        
                    } catch (e) {
                        details += `\n\n⚠️ Error parsing additional details: ${e.message}`;
                    }
                } else {
                    details += '\n\n📋 Additional Details: Not available for this product';
                }
                
                alert(details);
            }
        }
    </script>
</body>
</html>
    <?php
} else {
    // AJAX request - return JSON data
    try {
        $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Get all products
        $stmt = $pdo->query("SELECT id, name, name_ar, description, description_ar, main_image_url, sku, model_number, brand, category, url, more_details FROM products ORDER BY name ASC");
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get statistics
        $totalProducts = count($products);
        
        $categoryStmt = $pdo->query("SELECT category, COUNT(*) as count FROM products GROUP BY category ORDER BY count DESC");
        $categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);
        
        $brandStmt = $pdo->query("SELECT brand, COUNT(*) as count FROM products GROUP BY brand ORDER BY count DESC");
        $brands = $brandStmt->fetchAll(PDO::FETCH_ASSOC);
        
        $stats = [
            'totalProducts' => $totalProducts,
            'totalCategories' => count($categories),
            'totalBrands' => count($brands),
            'categories' => $categories,
            'brands' => $brands
        ];
        
        header('Content-Type: application/json');
        echo json_encode([
            'products' => $products,
            'stats' => $stats
        ]);
        
    } catch (Exception $e) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>
