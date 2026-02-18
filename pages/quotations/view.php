<?php
/**
 * View Quotation
 */

require_once __DIR__ . '/../../classes/Quotation.php';

$quotationModel = new Quotation();
$settings = $quotationModel->getCompanySettings();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    header('Location: index.php');
    exit;
}

$quotation = $quotationModel->getById($id);

if (!$quotation) {
    header('Location: index.php');
    exit;
}

$pageTitle = 'View ' . $quotation['quotation_no'] . ' - ' . APP_NAME;

// Format warranty
function formatWarrantyView($value, $type) {
    if ($type === 'Lifetime') return 'Lifetime';
    if ($value && $type) return $value . ' ' . $type;
    return '-';
}

// Status badge class
function getStatusClass($status) {
    $classes = [
        'Draft' => 'bg-secondary',
        'Final' => 'bg-success',
        'Approved' => 'bg-primary',
        'Rejected' => 'bg-danger',
        'Expired' => 'bg-warning text-dark'
    ];
    return $classes[$status] ?? 'bg-secondary';
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-6">
            <h2>
                <i class="bi bi-file-text me-2"></i><?= htmlspecialchars($quotation['quotation_no']) ?>
                <span class="badge <?= getStatusClass($quotation['status']) ?> fs-6 ms-2"><?= $quotation['status'] ?></span>
            </h2>
            <p class="text-muted mb-0">
                Created: <?= date('F d, Y \a\t h:i A', strtotime($quotation['created_at'])) ?>
                <?php if ($quotation['updated_at'] && $quotation['updated_at'] != $quotation['created_at']): ?>
                | Updated: <?= date('F d, Y \a\t h:i A', strtotime($quotation['updated_at'])) ?>
                <?php endif; ?>
            </p>
        </div>
        <div class="col-md-6 text-end">
            <a href="edit.php?id=<?= $id ?>" class="btn btn-primary">
                <i class="bi bi-pencil me-1"></i>Edit
            </a>
            <a href="print.php?id=<?= $id ?>" class="btn btn-success" target="_blank">
                <i class="bi bi-printer me-1"></i>Print
            </a>
            <button type="button" class="btn btn-outline-danger" onclick="deleteQuotation(<?= $id ?>, '<?= $quotation['quotation_no'] ?>')">
                <i class="bi bi-trash me-1"></i>Delete
            </button>
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>
    
    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Customer Info Card -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-person me-2"></i>Customer Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="text-muted" style="width:120px;">Company:</td>
                                    <td><strong class="text-primary fs-5"><?= htmlspecialchars($quotation['customer_name']) ?></strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Contact Person:</td>
                                    <td><?= htmlspecialchars($quotation['customer_contact'] ?: '-') ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Phone:</td>
                                    <td><?= htmlspecialchars($quotation['customer_phone'] ?: '-') ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="text-muted" style="width:80px;">Email:</td>
                                    <td><?= htmlspecialchars($quotation['customer_email'] ?: '-') ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Address:</td>
                                    <td><?= htmlspecialchars($quotation['customer_address'] ?: '-') ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Items Card -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-cart me-2"></i>Quotation Items (<?= count($quotation['items']) ?>)</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:5%">#</th>
                                    <th style="width:12%">Code</th>
                                    <th style="width:28%">Product</th>
                                    <th style="width:12%">Warranty</th>
                                    <th style="width:8%" class="text-center">Qty</th>
                                    <th style="width:13%" class="text-end">Unit Price</th>
                                    <?php if ($quotation['vat_enabled']): ?>
                                    <th style="width:10%" class="text-end">VAT</th>
                                    <?php endif; ?>
                                    <th style="width:14%" class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($quotation['items'])): ?>
                                <tr>
                                    <td colspan="<?= $quotation['vat_enabled'] ? 8 : 7 ?>" class="text-center py-4 text-muted">
                                        No items in this quotation
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($quotation['items'] as $index => $item): ?>
                                <tr>
                                    <td class="text-center"><?= $index + 1 ?></td>
                                    <td>
                                        <strong class="text-success"><?= htmlspecialchars($item['product_code'] ?: 'N/A') ?></strong>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($item['product_name']) ?></strong>
                                        <?php if (!empty($item['product_description'])): ?>
                                        <br><small class="text-muted"><?= nl2br(htmlspecialchars($item['product_description'])) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center"><?= formatWarrantyView($item['warranty_value'], $item['warranty_type']) ?></td>
                                    <td class="text-center"><strong><?= $item['quantity'] ?></strong></td>
                                    <td class="text-end">Rs. <?= number_format($item['unit_price'], 2) ?></td>
                                    <?php if ($quotation['vat_enabled']): ?>
                                    <td class="text-end text-info">Rs. <?= number_format($item['vat_amount'], 2) ?></td>
                                    <?php endif; ?>
                                    <td class="text-end"><strong class="text-success">Rs. <?= number_format($item['line_total'], 2) ?></strong></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="<?= $quotation['vat_enabled'] ? 6 : 5 ?>" class="text-end fw-bold">Subtotal:</td>
                                    <td colspan="2" class="text-end fw-bold">Rs. <?= number_format($quotation['subtotal'], 2) ?></td>
                                </tr>
                                <?php if ($quotation['discount'] > 0): ?>
                                <tr class="text-danger">
                                    <td colspan="<?= $quotation['vat_enabled'] ? 6 : 5 ?>" class="text-end fw-bold">Discount:</td>
                                    <td colspan="2" class="text-end fw-bold">- Rs. <?= number_format($quotation['discount'], 2) ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if ($quotation['vat_enabled']): ?>
                                <tr class="text-info">
                                    <td colspan="6" class="text-end fw-bold">VAT (<?= $quotation['vat_percentage'] ?>%):</td>
                                    <td colspan="2" class="text-end fw-bold">Rs. <?= number_format($quotation['tax_amount'], 2) ?></td>
                                </tr>
                                <?php endif; ?>
                                <tr class="table-dark">
                                    <td colspan="<?= $quotation['vat_enabled'] ? 6 : 5 ?>" class="text-end fw-bold fs-5">GRAND TOTAL:</td>
                                    <td colspan="2" class="text-end fw-bold fs-5">Rs. <?= number_format($quotation['grand_total'], 2) ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Notes Card -->
            <?php if (!empty($quotation['notes'])): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-sticky me-2"></i>Notes & Remarks</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0" style="white-space: pre-line;"><?= htmlspecialchars($quotation['notes']) ?></p>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quotation Details -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Quotation Details</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted">Quotation No:</td>
                            <td><strong class="text-primary"><?= htmlspecialchars($quotation['quotation_no']) ?></strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Date:</td>
                            <td><strong><?= date('F d, Y', strtotime($quotation['quotation_date'])) ?></strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status:</td>
                            <td><span class="badge <?= getStatusClass($quotation['status']) ?>"><?= $quotation['status'] ?></span></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Prepared By:</td>
                            <td><?= htmlspecialchars($quotation['prepared_by'] ?: '-') ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">VAT:</td>
                            <td>
                                <?php if ($quotation['vat_enabled']): ?>
                                <span class="badge bg-info"><?= $quotation['vat_percentage'] ?>% Enabled</span>
                                <?php else: ?>
                                <span class="badge bg-secondary">Not Applied</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <!-- Terms -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-file-text me-2"></i>Terms & Conditions</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted">Delivery:</td>
                            <td><?= htmlspecialchars($quotation['delivery_terms'] ?: '-') ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Payment:</td>
                            <td><?= htmlspecialchars($quotation['payment_terms'] ?: '-') ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Validity:</td>
                            <td><strong><?= htmlspecialchars($quotation['validity'] ?: '-') ?></strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Stock:</td>
                            <td><?= htmlspecialchars($quotation['stock_availability'] ?: '-') ?></td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <!-- Summary Card -->
            <div class="card mb-4 border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>Amount Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <strong>Rs. <?= number_format($quotation['subtotal'], 2) ?></strong>
                    </div>
                    <?php if ($quotation['discount'] > 0): ?>
                    <div class="d-flex justify-content-between mb-2 text-danger">
                        <span>Discount:</span>
                        <strong>- Rs. <?= number_format($quotation['discount'], 2) ?></strong>
                    </div>
                    <?php endif; ?>
                    <?php if ($quotation['vat_enabled']): ?>
                    <div class="d-flex justify-content-between mb-2 text-info">
                        <span>VAT (<?= $quotation['vat_percentage'] ?>%):</span>
                        <strong>Rs. <?= number_format($quotation['tax_amount'], 2) ?></strong>
                    </div>
                    <?php endif; ?>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span class="fs-5 fw-bold">Grand Total:</span>
                        <strong class="fs-4 text-primary">Rs. <?= number_format($quotation['grand_total'], 2) ?></strong>
                    </div>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="d-grid gap-2">
                <a href="edit.php?id=<?= $id ?>" class="btn btn-primary btn-lg">
                    <i class="bi bi-pencil me-2"></i>Edit Quotation
                </a>
                <a href="print.php?id=<?= $id ?>" class="btn btn-success btn-lg" target="_blank">
                    <i class="bi bi-printer me-2"></i>Print / Download
                </a>
                <a href="create.php" class="btn btn-outline-primary">
                    <i class="bi bi-plus-circle me-2"></i>Create New Quotation
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function deleteQuotation(id, quotationNo) {
    if (confirm('Are you sure you want to delete quotation ' + quotationNo + '?\n\nThis action cannot be undone!')) {
        fetch('<?= APP_URL ?>ajax/delete_quotation.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({id: id})
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = 'index.php';
            } else {
                alert('Error: ' + data.error);
            }
        })
        .catch(error => {
            alert('Error deleting quotation');
            console.error(error);
        });
    }
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>