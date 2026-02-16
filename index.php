<?php
/**
 * Dashboard - Main Index
 */

require_once __DIR__ . '/classes/Quotation.php';
require_once __DIR__ . '/classes/Customer.php';
require_once __DIR__ . '/classes/Product.php';

$pageTitle = 'Dashboard - ' . APP_NAME;

$quotationModel = new Quotation();
$customerModel = new Customer();
$productModel = new Product();

// Get statistics
$totalQuotations = $quotationModel->count();
$totalCustomers = $customerModel->count();
$totalProducts = $productModel->count();

// Recent quotations
$recentQuotations = $quotationModel->getAll(1, 5);

include 'includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="bi bi-speedometer2 me-2"></i>Dashboard</h2>
            <p class="text-muted">Welcome to DOT INK Quotation Management System</p>
        </div>
    </div>
    
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-2">Total Quotations</h6>
                            <h2 class="card-title mb-0"><?= number_format($totalQuotations) ?></h2>
                        </div>
                        <i class="bi bi-file-text display-4 opacity-50"></i>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <a href="pages/quotations/" class="text-white text-decoration-none small">
                        View All <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-2">Total Customers</h6>
                            <h2 class="card-title mb-0"><?= number_format($totalCustomers) ?></h2>
                        </div>
                        <i class="bi bi-people display-4 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-2">Total Products</h6>
                            <h2 class="card-title mb-0"><?= number_format($totalProducts) ?></h2>
                        </div>
                        <i class="bi bi-box-seam display-4 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-lightning me-2"></i>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <a href="pages/quotations/create.php" class="btn btn-primary btn-lg me-2">
                        <i class="bi bi-plus-circle me-2"></i>Create New Quotation
                    </a>
                    <a href="pages/quotations/" class="btn btn-outline-primary btn-lg">
                        <i class="bi bi-list-ul me-2"></i>View All Quotations
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Quotations -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Recent Quotations</h5>
                    <a href="pages/quotations/" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Quotation No</th>
                                    <th>Customer</th>
                                    <th>Date</th>
                                    <th class="text-end">Amount</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recentQuotations)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                        No quotations found
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($recentQuotations as $quotation): ?>
                                <tr>
                                    <td>
                                        <strong class="text-primary"><?= htmlspecialchars($quotation['quotation_no']) ?></strong>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($quotation['customer_name']) ?></strong>
                                        <?php if ($quotation['customer_contact']): ?>
                                        <br><small class="text-muted"><?= htmlspecialchars($quotation['customer_contact']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date(DISPLAY_DATE_FORMAT, strtotime($quotation['quotation_date'])) ?></td>
                                    <td class="text-end">
                                        <strong>Rs. <?= number_format($quotation['grand_total'], 2) ?></strong>
                                    </td>
                                    <td class="text-center">
                                        <?php
                                        $statusClass = [
                                            'Draft' => 'bg-secondary',
                                            'Final' => 'bg-success',
                                            'Approved' => 'bg-primary',
                                            'Rejected' => 'bg-danger',
                                            'Expired' => 'bg-warning'
                                        ];
                                        $class = $statusClass[$quotation['status']] ?? 'bg-secondary';
                                        ?>
                                        <span class="badge <?= $class ?>"><?= $quotation['status'] ?></span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="pages/quotations/view.php?id=<?= $quotation['id'] ?>" 
                                               class="btn btn-outline-primary" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="pages/quotations/edit.php?id=<?= $quotation['id'] ?>" 
                                               class="btn btn-outline-secondary" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="pages/quotations/print.php?id=<?= $quotation['id'] ?>" 
                                               class="btn btn-outline-success" title="Print" target="_blank">
                                                <i class="bi bi-printer"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>