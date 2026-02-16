<?php
/**
 * AJAX Product Search
 */

header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

error_reporting(E_ALL);
ini_set('display_errors', 0);

try {
    require_once __DIR__ . '/../classes/Product.php';
    
    $keyword = isset($_GET['q']) ? trim($_GET['q']) : '';
    
    if (strlen($keyword) < 1) {
        echo json_encode(['success' => true, 'data' => []]);
        exit;
    }
    
    $productModel = new Product();
    $products = $productModel->search($keyword, 10);
    
    $results = [];
    foreach ($products as $product) {
        $results[] = [
            'id' => $product['id'],
            'product_code' => $product['product_code'],
            'product_name' => $product['product_name'],
            'description' => $product['description'] ?? '',
            'default_price' => $product['default_price'],
            'warranty_value' => $product['default_warranty_value'],
            'warranty_type' => $product['default_warranty_type']
        ];
    }
    
    echo json_encode(['success' => true, 'data' => $results]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}