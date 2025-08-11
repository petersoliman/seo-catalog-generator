<?php
// Front controller for Symfony server
// This file allows the Symfony development server to properly serve your application

// Set error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get the request URI
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$path = parse_url($requestUri, PHP_URL_PATH);

// Handle API routes
if (strpos($path, '/api') === 0) {
    // Include configuration
    require_once 'config.php';
    
    // Set headers for API
    header('Content-Type: application/json; charset=UTF-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    
    // Handle preflight OPTIONS request
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }
    
    // Function to send JSON response
    function sendJsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit();
    }
    
    // Function to send error response
    function sendErrorResponse($message, $statusCode = 400) {
        sendJsonResponse([
            'error' => true,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ], $statusCode);
    }
    
    try {
        // Check if brand parameter is provided
        if (!isset($_GET['brand']) || empty(trim($_GET['brand']))) {
            sendErrorResponse('Brand parameter is required. Usage: ?brand=BRAND_NAME', 400);
        }
        
        $brand = trim($_GET['brand']);
        
        // Validate brand parameter length
        if (strlen($brand) > 100) {
            sendErrorResponse('Brand name too long. Maximum 100 characters allowed.', 400);
        }
        
        // Create PDO connection
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Prepare and execute query with limit
        $stmt = $pdo->prepare("
            SELECT 
                model_number,
                name,
                sku,
                category,
                price,
                currency,
                url
            FROM products 
            WHERE brand = :brand 
            ORDER BY model_number, name
            LIMIT :limit
        ");
        
        $stmt->bindValue(':brand', $brand, PDO::PARAM_STR);
        $stmt->bindValue(':limit', MAX_RESULTS, PDO::PARAM_INT);
        $stmt->execute();
        
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($products)) {
            sendJsonResponse([
                'success' => true,
                'brand' => $brand,
                'message' => 'No products found for this brand',
                'count' => 0,
                'products' => [],
                'timestamp' => date('Y-m-d H:i:s'),
                'api_version' => API_VERSION
            ]);
        }
        
        // Format response
        $response = [
            'success' => true,
            'brand' => $brand,
            'count' => count($products),
            'products' => array_map(function($product) {
                return [
                    'model_number' => $product['model_number'],
                    'name' => $product['name'],
                    'sku' => $product['sku'],
                    'category' => $product['category'],
                    'price' => $product['price'] ? (float)$product['price'] : null,
                    'currency' => $product['currency'],
                    'url' => $product['url']
                ];
            }, $products),
            'timestamp' => date('Y-m-d H:i:s'),
            'api_version' => API_VERSION
        ];
        
        sendJsonResponse($response);
        
    } catch (PDOException $e) {
        if (DEBUG_MODE) {
            sendErrorResponse('Database connection error: ' . $e->getMessage(), 500);
        } else {
            sendErrorResponse('Database connection error', 500);
        }
    } catch (Exception $e) {
        if (DEBUG_MODE) {
            sendErrorResponse('Server error: ' . $e->getMessage(), 500);
        } else {
            sendErrorResponse('Server error', 500);
        }
    }
}

// Handle demo page
if ($path === '/demo' || $path === '/demo.html') {
    include 'demo.html';
    exit();
}

// Default: Include your main scraping script
header('Content-Type: text/html; charset=UTF-8');
require_once 'boschScrpr.php';
?>
