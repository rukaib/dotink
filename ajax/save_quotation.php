<?php
/**
 * AJAX Save Quotation
 */

header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

error_reporting(E_ALL);
ini_set('display_errors', 0);

try {
    require_once __DIR__ . '/../classes/Quotation.php';
    
    // Get JSON input
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
    
    if (!$input) {
        throw new Exception('Invalid request data');
    }
    
    // Validate required fields
    if (empty($input['customer_name'])) {
        throw new Exception('Customer name is required');
    }
    
    if (empty($input['items']) || !is_array($input['items']) || count($input['items']) === 0) {
        throw new Exception('At least one item is required');
    }
    
    $quotationModel = new Quotation();
    
    // Check if update or create
    if (!empty($input['id'])) {
        $quotationModel->update($input['id'], $input);
        $quotationId = $input['id'];
        $message = 'Quotation updated successfully';
    } else {
        if (empty($input['quotation_no'])) {
            $input['quotation_no'] = $quotationModel->getNextQuotationNo();
        }
        $quotationId = $quotationModel->create($input);
        $message = 'Quotation created successfully';
    }
    
    echo json_encode([
        'success' => true,
        'message' => $message,
        'quotation_id' => $quotationId,
        'quotation_no' => $input['quotation_no'] ?? ''
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}