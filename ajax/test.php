<?php
/**
 * Test AJAX Connection
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Test customers
    $stmt = $db->query("SELECT COUNT(*) as count FROM customers");
    $customers = $stmt->fetch();
    
    // Test products
    $stmt = $db->query("SELECT COUNT(*) as count FROM products");
    $products = $stmt->fetch();
    
    echo json_encode([
        'success' => true,
        'message' => 'Database connection successful',
        'customers_count' => $customers['count'],
        'products_count' => $products['count'],
        'app_url' => APP_URL,
        'base_url' => BASE_URL
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}