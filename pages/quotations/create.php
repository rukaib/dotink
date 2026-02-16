<?php
/**
 * Create New Quotation
 */

require_once __DIR__ . '/../../classes/Quotation.php';

$pageTitle = 'Create Quotation - ' . APP_NAME;

$quotationModel = new Quotation();
$settings = $quotationModel->getCompanySettings();
$nextQuotationNo = $quotationModel->getNextQuotationNo();

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
            <h2><i class="bi bi-plus-circle me-2"></i>Create New Quotation</h2>
            <p class="text-muted">Fill in the details to create a new quotation</p>
        </div>
        <div class="col-md-6 text-end">
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to List
            </a>
        </div>
    </div>
    
    <!-- Quotation Form -->
    <form id="quotationForm" novalidate>
        <input type="hidden" id="quotation_id" name="id" value="">
        
        <div class="row">
            <!-- Left Column -->
            <div class="col-lg-8">
                <!-- Customer Section -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-person me-2"></i>Customer Information</h5>
                    </div>
                    <div class="card-body">
                        <input type="hidden" id="customer_id" name="customer_id" value="">
                        <input type="hidden" id="is_new_customer" name="is_new_customer" value="0">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Company Name <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <input type="text" id="customer_name" name="customer_name" 
                                           class="form-control form-control-lg" 
                                           placeholder="Type to search or enter new..."
                                           autocomplete="off" required>
                                    <div id="customerDropdown" class="autocomplete-dropdown"></div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Contact Person</label>
                                <input type="text" id="customer_contact" name="customer_contact" class="form-control">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Phone</label>
                                <input type="text" id="customer_phone" name="customer_phone" class="form-control">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Email</label>
                                <input type="email" id="customer_email" name="customer_email" class="form-control">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Address</label>
                                <input type="text" id="customer_address" name="customer_address" class="form-control">
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
                                        <th style="width:8%" class="text-end vat-column" style="display:none;">VAT</th>
                                        <th style="width:14%" class="text-end">Total</th>
                                        <th style="width:80px" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsBody">
                                    <tr id="noItemsRow">
                                        <td colspan="8" class="text-center py-5 text-muted">
                                            <i class="bi bi-box-seam display-4 d-block mb-3"></i>
                                            <h5>No items added yet</h5>
                                            <p class="mb-3">Click "Add Products" to start adding items</p>
                                            <button type="button" class="btn btn-success" onclick="document.getElementById('openProductModal').click()">
                                                <i class="bi bi-plus-circle me-1"></i>Add Products
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot class="table-light" id="tableFoot" style="display:none;">
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
                                    <tr id="vatRow" style="display:none;">
                                        <td colspan="4" class="text-end fw-bold text-info">VAT (18%):</td>
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
                        <textarea id="notes" name="notes" class="form-control" rows="3">Price is negotiable after discussions.
One to one replacement warranty for manufacture faults only.
Cheque should be drawn in favor of DOT INK (PVT) LTD.</textarea>
                    </div>
                </div>
            </div>
            
            <!-- Right Column -->
            <div class="col-lg-4">
                <!-- Quotation Details -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Quotation No</label>
                            <input type="text" id="quotation_no" name="quotation_no" 
                                   class="form-control bg-light fw-bold text-primary" value="<?= $nextQuotationNo ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Date</label>
                            <input type="date" id="quotation_date" name="quotation_date" 
                                   class="form-control" value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Status</label>
                            <select id="status" name="status" class="form-select">
                                <option value="Draft">Draft</option>
                                <option value="Final">Final</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Prepared By</label>
                            <input type="text" id="prepared_by" name="prepared_by" class="form-control" 
                                   value="<?= htmlspecialchars($settings['prepared_by_name'] ?? 'M.ASHAN') ?>">
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
                                   value="Within 2 Days after Confirmation">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-bold">Payment</label>
                            <input type="text" id="payment_terms" name="payment_terms" class="form-control form-control-sm" 
                                   value="30 Days Credit / COD">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-bold">Validity</label>
                            <input type="text" id="validity" name="validity" class="form-control form-control-sm" 
                                   value="14 Days">
                        </div>
                        <div class="mb-0">
                            <label class="form-label small fw-bold">Stock</label>
                            <input type="text" id="stock_availability" name="stock_availability" class="form-control form-control-sm" 
                                   value="Available">
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
                            <input class="form-check-input" type="checkbox" id="vat_enabled" name="vat_enabled">
                            <label class="form-check-label fw-bold" for="vat_enabled">Apply VAT (18%)</label>
                        </div>
                        <div>
                            <label class="form-label fw-bold">Discount</label>
                            <div class="input-group">
                                <span class="input-group-text">Rs.</span>
                                <input type="number" id="discount" name="discount" class="form-control" value="0" min="0" step="0.01">
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
                        <div class="d-flex justify-content-between mb-2 text-info" id="summaryVatRow" style="display:none;">
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
                        <i class="bi bi-check-circle me-2"></i>Save Quotation
                    </button>
                    <button type="button" class="btn btn-success btn-lg" id="savePrintBtn">
                        <i class="bi bi-printer me-2"></i>Save & Print
                    </button>
                    <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </div>
        </div>
        
        <input type="hidden" id="subtotal" name="subtotal" value="0">
        <input type="hidden" id="tax_amount" name="tax_amount" value="0">
        <input type="hidden" id="grand_total" name="grand_total" value="0">
    </form>
</div>

<!-- ============================================
     PRODUCT SELECTION POPUP - LINE/CARD FORMAT
     ============================================ -->
<div class="modal fade" id="productModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-fullscreen-md-down modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-success text-white py-2">
                <h5 class="modal-title"><i class="bi bi-box-seam me-2"></i>Add Products to Quotation</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" style="background:#f5f5f5;">
                <!-- Search Bar - Fixed at top -->
                <div class="bg-white border-bottom p-3 sticky-top">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-7">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input type="text" id="productSearchInput" class="form-control form-control-lg border-start-0" 
                                       placeholder="Search products by code or name...">
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
                
                <!-- Products List -->
                <div class="p-3" style="max-height: calc(100vh - 200px); overflow-y: auto;" id="productListContainer">
                    <!-- Loading -->
                    <div id="productLoading" class="text-center py-5">
                        <div class="spinner-border text-success" style="width:3rem;height:3rem;"></div>
                        <p class="mt-3 text-muted">Loading products...</p>
                    </div>
                    
                    <!-- No Results -->
                    <div id="productNoResults" class="text-center py-5" style="display:none;">
                        <i class="bi bi-search display-3 text-muted"></i>
                        <h5 class="mt-3">No products found</h5>
                        <p class="text-muted">Try different search or add a new product</p>
                        <button type="button" class="btn btn-primary" onclick="document.getElementById('showNewProductRow').click()">
                            <i class="bi bi-plus-circle me-1"></i>Add New Product
                        </button>
                    </div>
                    
                    <!-- Product Cards Container -->
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

<!-- ============================================
     EDIT ITEM POPUP
     ============================================ -->
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
                
                <!-- Preview -->
                <div class="card bg-light mt-4">
                    <div class="card-body py-2">
                        <div class="row align-items-center">
                            <div class="col">
                                <small class="text-muted">Line Total:</small>
                            </div>
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
    vatRate: 18
};
const existingItems = [];
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>