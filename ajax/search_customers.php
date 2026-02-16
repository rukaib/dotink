<?php
/**
 * AJAX Customer Search
 */

header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 0);

try {
    require_once __DIR__ . '/../classes/Customer.php';
    
    $keyword = isset($_GET['q']) ? trim($_GET['q']) : '';
    
    if (strlen($keyword) < 1) {
        echo json_encode(['success' => true, 'data' => []]);
        exit;
    }
    
    $customerModel = new Customer();
    $customers = $customerModel->search($keyword, 10);
    
    $results = [];
    foreach ($customers as $customer) {
        $results[] = [
            'id' => $customer['id'],
            'customer_code' => $customer['customer_code'],
            'company_name' => $customer['company_name'],
            'contact_person' => $customer['contact_person'] ?? '',
            'phone' => $customer['phone'] ?? '',
            'email' => $customer['email'] ?? '',
            'address' => $customer['address'] ?? ''
        ];
    }
    
    echo json_encode(['success' => true, 'data' => $results]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}