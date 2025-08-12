<?php
// Simple product display from database
require_once 'config.php';

try {
    $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get all products from database
    $stmt = $pdo->query("SELECT id, name, sku, model_number, brand, category, url, description FROM products ORDER BY name ASC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get total count
    $totalProducts = count($products);
    
} catch (Exception $e) {
    $error = $e->getMessage();
    $products = [];
    $totalProducts = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products Database</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        .stats {
            background: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 18px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #495057;
        }
        tr:hover {
            background-color: #f8f9fa;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .no-data {
            text-align: center;
            padding: 40px;
            color: #6c757d;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Products Database</h1>
        
        <?php if (isset($error)): ?>
            <div class="error">
                <strong>Database Error:</strong> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <div class="stats">
            Total Products: <strong><?php echo $totalProducts; ?></strong>
        </div>
        
        <?php if (empty($products)): ?>
            <div class="no-data">
                <?php if (isset($error)): ?>
                    No products found due to database error.
                <?php else: ?>
                    No products found in the database.
                <?php endif; ?>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>SKU</th>
                        <th>Model</th>
                        <th>Brand</th>
                        <th>Category</th>
                        <th>URL</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($product['id']); ?></td>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo htmlspecialchars($product['sku']); ?></td>
                            <td><?php echo htmlspecialchars($product['model_number']); ?></td>
                            <td><?php echo htmlspecialchars($product['brand']); ?></td>
                            <td><?php echo htmlspecialchars($product['category']); ?></td>
                            <td>
                                <?php if (!empty($product['url'])): ?>
                                    <a href="<?php echo htmlspecialchars($product['url']); ?>" target="_blank">View</a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                $description = $product['description'];
                                if (strlen($description) > 100) {
                                    echo htmlspecialchars(substr($description, 0, 100)) . '...';
                                } else {
                                    echo htmlspecialchars($description);
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
