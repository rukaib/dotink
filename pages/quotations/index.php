<?php
/**
 * Quotations List Page
 */

require_once __DIR__ . '/../../classes/Quotation.php';

$pageTitle = 'Quotations - ' . APP_NAME;

$quotationModel = new Quotation();

// Pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = ITEMS_PER_PAGE;

// Filters
$filters = [
    'status' => $_GET['status'] ?? '',
    'search' => $_GET['search'] ?? '',
    'date_from' => $_GET['date_from'] ?? '',
    'date_to' => $_GET['date_to'] ?? ''
];

$quotations = $quotationModel->getAll($page, $perPage, $filters);
$totalQuotations = $quotationModel->count($filters);
$totalPages = ceil($totalQuotations / $perPage);

include __DIR__ . '/../../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2><i class="bi bi-file-text me-2"></i>Quotations</h2>
            <p class="text-muted">Manage all quotations</p>
        </div>
        <div class="col-md-6 text-end">
            <a href="create.php" class="btn btn-primary btn-lg">
                <i class="bi bi-plus-circle me-2"></i>Create New Quotation
            </a>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" 
                           placeholder="Quotation No or Customer..." value="<?= htmlspecialchars($filters['search']) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="Draft" <?= $filters['status'] === 'Draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="Final" <?= $filters['status'] === 'Final' ? 'selected' : '' ?>>Final</option>
                        <option value="Approved" <?= $filters['status'] === 'Approved' ? 'selected' : '' ?>>Approved</option>
                        <option value="Rejected" <?= $filters['status'] === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                        <option value="Expired" <?= $filters['status'] === 'Expired' ? 'selected' : '' ?>>Expired</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date From</label>
                    <input type="date" name="date_from" class="form-control" value="<?= $filters['date_from'] ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date To</label>
                    <input type="date" name="date_to" class="form-control" value="<?= $filters['date_to'] ?>">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-search me-1"></i>Filter
                    </button>
                    <a href="index.php" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-1"></i>Clear
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Quotations Table -->
    <div class="card">
        <div class="card-header">
            <strong>Total: <?= number_format($totalQuotations) ?> quotation(s)</strong>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th style="width:12%">Quotation No</th>
                            <th style="width:20%">Customer</th>
                            <th style="width:10%">Date</th>
                            <th style="width:12%" class="text-end">Amount</th>
                            <th style="width:8%" class="text-center">VAT</th>
                            <th style="width:8%" class="text-center">Status</th>
                            <th style="width:10%" class="text-center">Prepared By</th>
                            <th style="width:15%" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($quotations)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox display-1 d-block mb-3"></i>
                                <h5>No quotations found</h5>
                                <p>Create your first quotation to get started</p>
                                <a href="create.php" class="btn btn-primary">
                                    <i class="bi bi-plus-circle me-2"></i>Create Quotation
                                </a>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($quotations as $quotation): ?>
                        <tr>
                            <td>
                                <a href="view.php?id=<?= $quotation['id'] ?>" class="fw-bold text-primary text-decoration-none">
                                    <?= htmlspecialchars($quotation['quotation_no']) ?>
                                </a>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($quotation['customer_name']) ?></strong>
                                <?php if ($quotation['customer_contact']): ?>
                                <br><small class="text-muted"><?= htmlspecialchars($quotation['customer_contact']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?= date('M d, Y', strtotime($quotation['quotation_date'])) ?></td>
                            <td class="text-end">
                                <strong>Rs. <?= number_format($quotation['grand_total'], 2) ?></strong>
                                <?php if ($quotation['discount'] > 0): ?>
                                <br><small class="text-danger">-Rs. <?= number_format($quotation['discount'], 2) ?></small>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($quotation['vat_enabled']): ?>
                                <span class="badge bg-info"><?= $quotation['vat_percentage'] ?>%</span>
                                <?php else: ?>
                                <span class="badge bg-secondary">No</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php
                                $statusClass = [
                                    'Draft' => 'bg-secondary',
                                    'Final' => 'bg-success',
                                    'Approved' => 'bg-primary',
                                    'Rejected' => 'bg-danger',
                                    'Expired' => 'bg-warning text-dark'
                                ];
                                $class = $statusClass[$quotation['status']] ?? 'bg-secondary';
                                ?>
                                <span class="badge <?= $class ?>"><?= $quotation['status'] ?></span>
                            </td>
                            <td class="text-center">
                                <small><?= htmlspecialchars($quotation['prepared_by']) ?></small>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="view.php?id=<?= $quotation['id'] ?>" 
                                       class="btn btn-outline-primary" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="edit.php?id=<?= $quotation['id'] ?>" 
                                       class="btn btn-outline-secondary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="print.php?id=<?= $quotation['id'] ?>" 
                                       class="btn btn-outline-success" title="Print" target="_blank">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger" 
                                            onclick="deleteQuotation(<?= $quotation['id'] ?>, '<?= $quotation['quotation_no'] ?>')" 
                                            title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <?php if ($totalPages > 1): ?>
        <div class="card-footer">
            <nav>
                <ul class="pagination mb-0 justify-content-center">
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page - 1 ?>&<?= http_build_query($filters) ?>">Previous</a>
                    </li>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&<?= http_build_query($filters) ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                    
                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page + 1 ?>&<?= http_build_query($filters) ?>">Next</a>
                    </li>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function deleteQuotation(id, quotationNo) {
    if (confirm('Are you sure you want to delete quotation ' + quotationNo + '?\nThis action cannot be undone.')) {
        fetch('<?= APP_URL ?>ajax/delete_quotation.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({id: id})
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
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