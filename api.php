<?php
// Products API - Returns product model numbers by brand
// Usage: GET /api.php?brand=BRAND_NAME

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
    // Create PDO connection
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // If model_number is provided, return description_ar and description_en
    if (isset($_GET['model_number']) && !empty(trim($_GET['model_number']))) {
        $model_number = trim($_GET['model_number']);
        if (strlen($model_number) > 100) {
            sendErrorResponse('Model number too long. Maximum 100 characters allowed.', 400);
        }
        $stmt = $pdo->prepare("
            SELECT model_number, description_ar, description_en
            FROM products
            WHERE model_number = :model_number
            LIMIT 1
        ");
        $stmt->bindValue(':model_number', $model_number, PDO::PARAM_STR);
        $stmt->execute();
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$product) {
            sendJsonResponse([
                'success' => false,
                'message' => 'No product found for this model number',
                'model_number' => $model_number,
                'timestamp' => date('Y-m-d H:i:s'),
                'api_version' => API_VERSION
            ]);
        }
        sendJsonResponse([
            'success' => true,
            'model_number' => $product['model_number'],
            'description_ar' => $product['description_ar'],
            'description_en' => $product['description_en'],
            'timestamp' => date('Y-m-d H:i:s'),
            'api_version' => API_VERSION
        ]);
    }

    // Otherwise, require brand parameter as before
    if (!isset($_GET['brand']) || empty(trim($_GET['brand']))) {
        sendErrorResponse('Brand parameter is required. Usage: ?brand=BRAND_NAME', 400);
    }
    $brand = trim($_GET['brand']);
    if (strlen($brand) > 100) {
        sendErrorResponse('Brand name too long. Maximum 100 characters allowed.', 400);
    }
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
?>
