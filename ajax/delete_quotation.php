<?php
/**
 * AJAX Delete Quotation
 */

header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

error_reporting(E_ALL);
ini_set('display_errors', 0);

try {
    require_once __DIR__ . '/../classes/Quotation.php';
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['id'])) {
        throw new Exception('Quotation ID is required');
    }
    
    $quotationModel = new Quotation();
    $quotationModel->delete($input['id']);
    
    echo json_encode([
        'success' => true,
        'message' => 'Quotation deleted successfully'
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}