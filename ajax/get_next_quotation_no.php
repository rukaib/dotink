<?php
/**
 * AJAX Get Next Quotation Number
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../classes/Quotation.php';

try {
    $quotationModel = new Quotation();
    $nextNo = $quotationModel->getNextQuotationNo();
    
    echo json_encode([
        'success' => true,
        'quotation_no' => $nextNo
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}