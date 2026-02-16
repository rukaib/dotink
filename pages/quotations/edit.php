<?php
/**
 * Edit Quotation
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

$pageTitle = 'Edit ' . $quotation['quotation_no'] . ' - ' . APP_NAME;

$baseUrl = '';
$scriptDir = dirname($_SERVER['SCRIPT_NAME']);
if (strpos($scriptDir, '/dotink') !== false) {
    $baseUrl = substr($scriptDir, 0, strpos($scriptDir, '/dotink') + 7);
} else {
    $baseUrl = '/dotink';
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-6">
            <h2><i class="bi bi-pencil me-2"></i>Edit Quotation</h2>
            <p class="text-muted">Editing: <strong class="text-primary"><?= htmlspecialchars($quotation['quotation_no']) ?></strong></p>
        </div>
        <div class="col-md-6 text-end">
            <a href="view.php?id=<?= $id ?>" class="btn btn-outline-primary me-1">
                <i class="bi bi-eye"></i> View
            </a>
            <a href="print.php?id=<?= $id ?>" class="btn btn-outline-success me-1" target="_blank">
                <i class="bi bi-printer"></i> Print
            </a>
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </div>
    
    <!-- Form -->
    <form id="quotationForm" novalidate>
        <input type="hidden" id="quotation_id" name="id" value="<?= $quotation['id'] ?>">
        
        <div class="row">
            <!-- Left Column -->
            <div class="col-lg-8">
                <!-- Customer Section -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-person me-2"></i>Customer Information</h5>
                    </div>
                    <div class="card-body">
                        <input type="hidden" id="customer_id" name="customer_id" value="<?= htmlspecialchars($quotation['customer_id'] ?? '') ?>">
                        <input type="hidden" id="is_new_customer" name="is_new_customer" value="0">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Company Name <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <input type="text" id="customer_name" name="customer_name" 
                                           class="form-control form-control-lg" 
                                           value="<?= htmlspecialchars($quotation['customer_name'] ?? '') ?>"
                                           placeholder="Type to search..." autocomplete="off" required>
                                    <div id="customerDropdown" class="autocomplete-dropdown"></div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Contact Person</label>
                                <input type="text" id="customer_contact" name="customer_contact" class="form-control"
                                       value="<?= htmlspecialchars($quotation['customer_contact'] ?? '') ?>">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Phone</label>
                                <input type="text" id="customer_phone" name="customer_phone" class="form-control"
                                       value="<?= htmlspecialchars($quotation['customer_phone'] ?? '') ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Email</label>
                                <input type="email" id="customer_email" name="customer_email" class="form-control"
                                       value="<?= htmlspecialchars($quotation['customer_email'] ?? '') ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Address</label>
                                <input type="text" id="customer_address" name="customer_address" class="form-control"
                                       value="<?= htmlspecialchars($quotation['customer_address'] ?? '') ?>">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Items Section -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-cart me-2"></i>Quotation Items</h5>
                        <button type="button" id="openProductModal" class="btn btn-light">
                            <i class="bi bi-plus-circle me-1"></i>Add Products
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0" id="itemsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:5%">#</th>
                                        <th style="width:28%">Product</th>
                                        <th style="width:15%">Warranty</th>
                                        <th style="width:10%" class="text-center">Qty</th>
                                        <th style="width:14%" class="text-end">Unit Price</th>
                                        <th style="width:8%" class="text-end vat-column" <?= !$quotation['vat_enabled'] ? 'style="display:none;"' : '' ?>>VAT</th>
                                        <th style="width:14%" class="text-end">Total</th>
                                        <th style="width:80px" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsBody">
                                    <tr id="noItemsRow" style="display:none;">
                                        <td colspan="8" class="text-center py-5 text-muted">
                                            <i class="bi bi-box-seam display-4 d-block mb-3"></i>
                                            <h5>No items added</h5>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot class="table-light" id="tableFoot">
                                    <tr>
                                        <td colspan="4" class="text-end fw-bold">Subtotal:</td>
                                        <td class="d-none vat-column"></td>
                                        <td colspan="2" class="text-end fw-bold" id="subtotalDisplay">Rs. 0.00</td>
                                        <td></td>
                                    </tr>
                                    <tr id="discountRow">
                                        <td colspan="4" class="text-end fw-bold text-danger">Discount:</td>
                                        <td class="d-none vat-column"></td>
                                        <td colspan="2" class="text-end text-danger" id="discountDisplay">Rs. 0.00</td>
                                        <td></td>
                                    </tr>
                                    <tr id="vatRow" <?= !$quotation['vat_enabled'] ? 'style="display:none;"' : '' ?>>
                                        <td colspan="4" class="text-end fw-bold text-info">VAT (<?= $quotation['vat_percentage'] ?? 18 ?>%):</td>
                                        <td class="d-none vat-column"></td>
                                        <td colspan="2" class="text-end text-info" id="vatDisplay">Rs. 0.00</td>
                                        <td></td>
                                    </tr>
                                    <tr class="table-dark">
                                        <td colspan="4" class="text-end fw-bold fs-5">GRAND TOTAL:</td>
                                        <td class="d-none vat-column"></td>
                                        <td colspan="2" class="text-end fw-bold fs-5" id="grandTotalDisplay">Rs. 0.00</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Notes -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-sticky me-2"></i>Notes</h5>
                    </div>
                    <div class="card-body">
                        <textarea id="notes" name="notes" class="form-control" rows="3"><?= htmlspecialchars($quotation['notes'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
            
            <!-- Right Column -->
            <div class="col-lg-4">
                <!-- Details -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Quotation No</label>
                            <input type="text" id="quotation_no" name="quotation_no" 
                                   class="form-control bg-light fw-bold text-primary" 
                                   value="<?= htmlspecialchars($quotation['quotation_no']) ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Date</label>
                            <input type="date" id="quotation_date" name="quotation_date" 
                                   class="form-control" value="<?= $quotation['quotation_date'] ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Status</label>
                            <select id="status" name="status" class="form-select">
                                <option value="Draft" <?= $quotation['status'] === 'Draft' ? 'selected' : '' ?>>Draft</option>
                                <option value="Final" <?= $quotation['status'] === 'Final' ? 'selected' : '' ?>>Final</option>
                                <option value="Approved" <?= $quotation['status'] === 'Approved' ? 'selected' : '' ?>>Approved</option>
                                <option value="Rejected" <?= $quotation['status'] === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                                <option value="Expired" <?= $quotation['status'] === 'Expired' ? 'selected' : '' ?>>Expired</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Prepared By</label>
                            <input type="text" id="prepared_by" name="prepared_by" class="form-control" 
                                   value="<?= htmlspecialchars($quotation['prepared_by'] ?? '') ?>">
                        </div>
                    </div>
                </div>
                
                <!-- Terms -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-file-text me-2"></i>Terms</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <label class="form-label small fw-bold">Delivery</label>
                            <input type="text" id="delivery_terms" name="delivery_terms" class="form-control form-control-sm" 
                                   value="<?= htmlspecialchars($quotation['delivery_terms'] ?? '') ?>">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-bold">Payment</label>
                            <input type="text" id="payment_terms" name="payment_terms" class="form-control form-control-sm" 
                                   value="<?= htmlspecialchars($quotation['payment_terms'] ?? '') ?>">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-bold">Validity</label>
                            <input type="text" id="validity" name="validity" class="form-control form-control-sm" 
                                   value="<?= htmlspecialchars($quotation['validity'] ?? '') ?>">
                        </div>
                        <div class="mb-0">
                            <label class="form-label small fw-bold">Stock</label>
                            <input type="text" id="stock_availability" name="stock_availability" class="form-control form-control-sm" 
                                   value="<?= htmlspecialchars($quotation['stock_availability'] ?? '') ?>">
                        </div>
                    </div>
                </div>
                
                <!-- Tax & Discount -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-calculator me-2"></i>Tax & Discount</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="vat_enabled" name="vat_enabled"
                                   <?= $quotation['vat_enabled'] ? 'checked' : '' ?>>
                            <label class="form-check-label fw-bold" for="vat_enabled">Apply VAT (18%)</label>
                        </div>
                        <div>
                            <label class="form-label fw-bold">Discount</label>
                            <div class="input-group">
                                <span class="input-group-text">Rs.</span>
                                <input type="number" id="discount" name="discount" class="form-control" 
                                       value="<?= $quotation['discount'] ?? 0 ?>" min="0" step="0.01">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Summary -->
                <div class="card mb-4 border-primary">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <strong id="summarySubtotal">Rs. 0.00</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2 text-danger">
                            <span>Discount:</span>
                            <strong id="summaryDiscount">Rs. 0.00</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2 text-info" id="summaryVatRow" <?= !$quotation['vat_enabled'] ? 'style="display:none;"' : '' ?>>
                            <span>VAT (18%):</span>
                            <strong id="summaryVat">Rs. 0.00</strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span class="fs-5 fw-bold">Grand Total:</span>
                            <strong class="fs-4 text-primary" id="summaryGrandTotal">Rs. 0.00</strong>
                        </div>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg" id="saveBtn">
                        <i class="bi bi-check-circle me-2"></i>Update Quotation
                    </button>
                    <button type="button" class="btn btn-success btn-lg" id="savePrintBtn">
                        <i class="bi bi-printer me-2"></i>Update & Print
                    </button>
                    <a href="view.php?id=<?= $id ?>" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </div>
        </div>
        
        <input type="hidden" id="subtotal" name="subtotal" value="<?= $quotation['subtotal'] ?? 0 ?>">
        <input type="hidden" id="tax_amount" name="tax_amount" value="<?= $quotation['tax_amount'] ?? 0 ?>">
        <input type="hidden" id="grand_total" name="grand_total" value="<?= $quotation['grand_total'] ?? 0 ?>">
    </form>
</div>

<!-- Product Modal (Same as create.php) -->
<div class="modal fade" id="productModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-fullscreen-md-down modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-success text-white py-2">
                <h5 class="modal-title"><i class="bi bi-box-seam me-2"></i>Add Products to Quotation</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" style="background:#f5f5f5;">
                <div class="bg-white border-bottom p-3 sticky-top">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-7">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input type="text" id="productSearchInput" class="form-control form-control-lg border-start-0" 
                                       placeholder="Search products...">
                                <button type="button" class="btn btn-outline-secondary" id="clearProductSearch">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn btn-primary btn-lg w-100" id="showNewProductRow">
                                <i class="bi bi-plus-circle me-1"></i>New Product
                            </button>
                        </div>
                        <div class="col-md-2 text-end">
                            <span class="badge bg-success fs-6 py-2 px-3" id="addedCountBadge">0 added</span>
                        </div>
                    </div>
                </div>
                
                <div class="p-3" style="max-height: calc(100vh - 200px); overflow-y: auto;" id="productListContainer">
                    <div id="productLoading" class="text-center py-5">
                        <div class="spinner-border text-success" style="width:3rem;height:3rem;"></div>
                        <p class="mt-3 text-muted">Loading products...</p>
                    </div>
                    <div id="productNoResults" class="text-center py-5" style="display:none;">
                        <i class="bi bi-search display-3 text-muted"></i>
                        <h5 class="mt-3">No products found</h5>
                    </div>
                    <div id="productCardsContainer"></div>
                </div>
            </div>
            <div class="modal-footer bg-white border-top py-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Item Modal -->
<div class="modal fade" id="editItemModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white py-2">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Edit Item</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editItemRowId" value="">
                <input type="hidden" id="editItemProductId" value="">
                <input type="hidden" id="editItemProductCode" value="">
                <input type="hidden" id="editItemIsNew" value="0">
                
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-bold">Product Name <span class="text-danger">*</span></label>
                        <input type="text" id="editItemName" class="form-control form-control-lg">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Product Code</label>
                        <input type="text" id="editItemCodeDisplay" class="form-control bg-light" readonly>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold">Description</label>
                        <textarea id="editItemDescription" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Warranty</label>
                        <div class="input-group">
                            <select id="editItemWarrantyValue" class="form-select">
                                <option value="">-</option>
                                <?php for($i=1; $i<=60; $i++): ?>
                                <option value="<?= $i ?>"><?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                            <select id="editItemWarrantyType" class="form-select">
                                <option value="">Select</option>
                                <option value="Days">Days</option>
                                <option value="Weeks">Weeks</option>
                                <option value="Months">Months</option>
                                <option value="Years">Years</option>
                                <option value="Lifetime">Lifetime</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Quantity</label>
                        <input type="number" id="editItemQuantity" class="form-control" min="1" value="1">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Unit Price</label>
                        <div class="input-group">
                            <span class="input-group-text">Rs.</span>
                            <input type="number" id="editItemPrice" class="form-control" min="0" step="0.01">
                        </div>
                    </div>
                </div>
                
                <div class="card bg-light mt-4">
                    <div class="card-body py-2">
                        <div class="row align-items-center">
                            <div class="col"><small class="text-muted">Line Total:</small></div>
                            <div class="col text-end">
                                <strong class="fs-4 text-success" id="editItemPreviewTotal">Rs. 0.00</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-lg" id="saveEditItem">
                    <i class="bi bi-check-circle me-1"></i>Update Item
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const CONFIG = {
    baseUrl: '<?= $baseUrl ?>',
    vatRate: <?= $quotation['vat_percentage'] ?? 18 ?>
};
const existingItems = <?= json_encode($quotation['items'] ?? []) ?>;
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>