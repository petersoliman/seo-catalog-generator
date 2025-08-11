<?php
// Database setup script for products API
// This script creates the database table and populates it with data from products.json

// Include configuration
require_once 'config.php';

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database '" . DB_NAME . "' created or already exists.\n";
    
    // Select the database
    $pdo->exec("USE `" . DB_NAME . "`");
    
    // Create products table
    $createTableSQL = "
    CREATE TABLE IF NOT EXISTS `products` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(255) NOT NULL,
        `sku` varchar(100) NOT NULL,
        `model_number` varchar(100) NOT NULL,
        `brand` varchar(100) NOT NULL,
        `category` varchar(255) NOT NULL,
        `url` text NOT NULL,
        `image` text,
        `price` decimal(10,2) DEFAULT NULL,
        `currency` varchar(10) DEFAULT NULL,
        `availability` varchar(100) DEFAULT NULL,
        `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `sku` (`sku`),
        KEY `brand` (`brand`),
        KEY `category` (`category`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $pdo->exec($createTableSQL);
    echo "Products table created successfully.\n";
    
    // Check if table is empty
    $stmt = $pdo->query("SELECT COUNT(*) FROM products");
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        echo "Table is empty. Populating with data from products.json...\n";
        
        // Read products.json
        if (file_exists('products.json')) {
            $jsonData = file_get_contents('products.json');
            $products = json_decode($jsonData, true);
            
            if (isset($products['itemListElement']) && is_array($products['itemListElement'])) {
                $insertStmt = $pdo->prepare("
                    INSERT INTO products (name, sku, model_number, brand, category, url, image, price, currency, availability)
                    VALUES (:name, :sku, :model_number, :brand, :category, :url, :image, :price, :currency, :availability)
                    ON DUPLICATE KEY UPDATE
                    name = VALUES(name),
                    model_number = VALUES(model_number),
                    brand = VALUES(brand),
                    category = VALUES(category),
                    url = VALUES(url),
                    image = VALUES(image),
                    price = VALUES(price),
                    currency = VALUES(currency),
                    availability = VALUES(availability)
                ");
                
                $inserted = 0;
                $errors = 0;
                
                foreach ($products['itemListElement'] as $item) {
                    if (isset($item['item'])) {
                        $product = $item['item'];
                        
                        try {
                            // Extract model number from name (usually the first part)
                            $modelNumber = $product['name'];
                            if (strpos($product['name'], ' ') !== false) {
                                $parts = explode(' ', $product['name']);
                                $modelNumber = $parts[0]; // First part is usually the model
                            }
                            
                            // Extract brand name
                            $brand = 'Unknown';
                            if (isset($product['brand']['name'])) {
                                $brand = $product['brand']['name'];
                            }
                            
                            // Extract price and currency
                            $price = null;
                            $currency = null;
                            if (isset($product['offers']['price'])) {
                                $price = $product['offers']['price'];
                            }
                            if (isset($product['offers']['priceCurrency'])) {
                                $currency = $product['offers']['priceCurrency'];
                            }
                            
                            $insertStmt->execute([
                                'name' => $product['name'],
                                'sku' => $product['sku'],
                                'model_number' => $modelNumber,
                                'brand' => $brand,
                                'category' => $product['category'],
                                'url' => $product['url'],
                                'image' => $product['image'] ?? '',
                                'price' => $price,
                                'currency' => $currency,
                                'availability' => $product['offers']['availability'] ?? ''
                            ]);
                            
                            $inserted++;
                        } catch (Exception $e) {
                            $errors++;
                            if (DEBUG_MODE) {
                                echo "Error inserting product {$product['sku']}: " . $e->getMessage() . "\n";
                            }
                        }
                    }
                }
                
                echo "Successfully inserted/updated $inserted products.\n";
                if ($errors > 0) {
                    echo "Encountered $errors errors during insertion.\n";
                }
            } else {
                echo "No products found in JSON data.\n";
            }
        } else {
            echo "products.json file not found.\n";
        }
    } else {
        echo "Table already contains $count products. Skipping data population.\n";
    }
    
    // Show sample data
    echo "\nSample data from database:\n";
    $stmt = $pdo->query("SELECT brand, COUNT(*) as count FROM products GROUP BY brand ORDER BY count DESC");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "Brand: {$row['brand']} - Count: {$row['count']}\n";
    }
    
    // Show total count
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM products");
    $total = $stmt->fetchColumn();
    echo "\nTotal products in database: $total\n";
    
    echo "\nDatabase setup completed successfully!\n";
    echo "You can now use the API at: /api.php?brand=BRAND_NAME\n";
    
} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
    if (DEBUG_MODE) {
        echo "Connection details: " . DB_HOST . " / " . DB_NAME . " / " . DB_USER . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
