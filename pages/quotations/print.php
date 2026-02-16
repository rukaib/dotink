<?php
/**
 * Print Quotation - A4 Format
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

// Calculate base URL for images (absolute path)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$basePath = '';
$scriptDir = dirname($_SERVER['SCRIPT_NAME']);
if (strpos($scriptDir, '/dotink') !== false) {
    $basePath = substr($scriptDir, 0, strpos($scriptDir, '/dotink') + 7);
} else {
    $basePath = '/dotink';
}
$baseUrl = $protocol . '://' . $host . $basePath;

// Logo path - absolute URL
$logoPath = $baseUrl . '/assets/images/logo.png';

// Check if logo exists
$logoFullPath = __DIR__ . '/../../assets/images/logo.png';
$logoExists = file_exists($logoFullPath);

// Amount in words
$amountInWords = 'Rupees ' . $quotationModel->numberToWords((int)$quotation['grand_total']) . ' Only';

// Pagination
$itemsPerFirstPage = 6;
$itemsPerNextPage = 12;
$items = $quotation['items'];
$totalItems = count($items);

$pages = [];
if ($totalItems <= $itemsPerFirstPage) {
    $pages[] = $items;
} else {
    $pages[] = array_slice($items, 0, $itemsPerFirstPage);
    $remaining = array_slice($items, $itemsPerFirstPage);
    while (count($remaining) > 0) {
        $pages[] = array_slice($remaining, 0, $itemsPerNextPage);
        $remaining = array_slice($remaining, $itemsPerNextPage);
    }
}
$totalPages = count($pages);

function formatWarrantyPrint($value, $type) {
    if ($type === 'Lifetime') return 'Lifetime';
    if ($value && $type) return $value . ' ' . $type;
    return '-';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quotation - <?= htmlspecialchars($quotation['quotation_no']) ?></title>
    <style>
        /* Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --cyan: #00B0F0;
            --blue: #0070C0;
            --dark: #333;
        }

        html, body {
            font-family: Cambria, Georgia, serif;
            font-size: 10pt;
            line-height: 1.4;
            color: var(--dark);
            background: #fff;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* Action Buttons */
        .action-buttons {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            display: flex;
            gap: 10px;
        }

        .action-btn {
            padding: 12px 25px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 5px;
            cursor: pointer;
            border: none;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
            transition: all 0.3s;
        }

        .btn-back {
            background: #6c757d;
            color: white;
        }

        .btn-back:hover {
            background: #5a6268;
        }

        .btn-print {
            background: #28a745;
            color: white;
        }

        .btn-print:hover {
            background: #218838;
        }

        /* A4 Page */
        @page {
            size: A4 portrait;
            margin: 0;
        }

        .page {
            width: 210mm;
            height: 297mm;
            padding: 10mm 15mm;
            margin: 0 auto;
            background: #fff;
            position: relative;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            page-break-after: always;
        }

        .page:last-child {
            page-break-after: auto;
        }

        /* Header */
        .header {
            border-bottom: 2.5px solid var(--cyan);
            padding-bottom: 3mm;
            margin-bottom: 3mm;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-left {
            width: 40%;
            vertical-align: middle;
        }

        .header-right {
            width: 60%;
            text-align: right;
            vertical-align: middle;
        }

        .logo-img {
            max-width: 180px;
            max-height: 60px;
            height: auto;
        }

        .logo-text {
            font-size: 20pt;
            font-weight: bold;
            color: var(--blue);
        }

        .company-name {
            font-size: 14pt;
            font-weight: bold;
            color: var(--blue);
            margin-bottom: 1mm;
        }

        .company-details {
            font-size: 8pt;
            color: #555;
            line-height: 1.4;
        }

        /* Content */
        .content {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        /* Title */
        .title {
            text-align: center;
            margin-bottom: 3mm;
        }

        .title h1 {
            font-size: 16pt;
            font-weight: bold;
            letter-spacing: 3px;
            margin: 0;
            color: var(--dark);
        }

        .page-indicator {
            font-size: 9pt;
            color: #666;
            margin-top: 1mm;
        }

        /* Info Table */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 3mm;
            font-size: 9pt;
            border: 1px solid #ccc;
        }

        .info-table td {
            padding: 1.5mm 2mm;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }

        .info-table tr:last-child td {
            border-bottom: none;
        }

        .info-table .lbl {
            font-weight: 600;
            color: #555;
            width: 70px;
            white-space: nowrap;
        }

        .info-table .col {
            width: 5px;
        }

        /* Terms Table */
        .terms-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 3mm;
            font-size: 8pt;
        }

        .terms-table th,
        .terms-table td {
            border: 1px solid #999;
            padding: 1.5mm;
            text-align: center;
        }

        .terms-table th {
            background: #f0f0f0;
            font-weight: 600;
            text-transform: uppercase;
        }

        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 3mm;
            font-size: 8pt;
            border: 1px solid #000;
        }

        .items-table th {
            background: var(--cyan);
            color: #fff;
            padding: 2mm 1.5mm;
            text-align: center;
            font-weight: 600;
            font-size: 8pt;
            text-transform: uppercase;
            border: 1px solid #000;
        }

        .items-table td {
            padding: 1.5mm;
            border: 1px solid #000;
            vertical-align: top;
        }

        .items-table .txt-c { text-align: center; }
        .items-table .txt-r { text-align: right; }

        .prd-name {
            font-weight: 600;
            font-size: 8pt;
        }

        .prd-desc {
            font-size: 7pt;
            color: #555;
            margin-top: 0.5mm;
            line-height: 1.2;
            white-space: pre-line;
        }

        .continued-note {
            text-align: center;
            font-size: 8pt;
            color: #666;
            font-style: italic;
            padding: 2mm;
            background: #f9f9f9;
            border: 1px dashed #ccc;
            margin-bottom: 3mm;
        }

        /* Totals */
        .totals-wrapper {
            width: 100%;
            margin-bottom: 3mm;
        }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }

        .totals-left {
            width: 55%;
            vertical-align: top;
            padding-right: 3mm;
        }

        .totals-right {
            width: 45%;
            vertical-align: top;
        }

        .words-box {
            background: #f9f9f9;
            border: 1px solid #ddd;
            padding: 2mm;
            font-size: 8pt;
            font-style: italic;
            line-height: 1.3;
        }

        .amounts-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
        }

        .amounts-table td {
            padding: 1mm 2mm;
        }

        .amounts-table .txt-r {
            text-align: right;
        }

        .amounts-table .total-row td {
            border-top: 1.5px solid #000;
            border-bottom: 1.5px solid #000;
            font-weight: bold;
            font-size: 10pt;
            padding: 1.5mm 2mm;
        }

        .amounts-table .discount-row td {
            color: #dc3545;
        }

        .amounts-table .vat-row td {
            color: #0dcaf0;
        }

        /* Notes */
        .notes-box {
            border: 1px solid var(--cyan);
            margin-bottom: 3mm;
        }

        .notes-title {
            background: var(--cyan);
            color: #fff;
            padding: 1.5mm;
            text-align: center;
            font-weight: bold;
            font-size: 8pt;
            text-transform: uppercase;
        }

        .notes-content {
            padding: 2mm;
            font-size: 8pt;
            line-height: 1.3;
        }

        /* Footer */
        .footer {
            margin-top: auto;
            padding-top: 2mm;
        }

        .footer-contact-table {
            width: 100%;
            border-collapse: collapse;
        }

        .footer-contact-left {
            width: 70%;
            vertical-align: top;
            font-size: 8pt;
            line-height: 1.3;
        }

        .footer-contact-right {
            width: 30%;
            text-align: right;
            vertical-align: top;
        }

        .footer-contact-name {
            color: var(--blue);
            font-weight: bold;
            font-size: 9pt;
        }

        .footer-line {
            border-top: 1.5px solid var(--cyan);
            margin: 2mm 0 0 0;
        }

        .footer-copyright {
            text-align: center;
            font-size: 7pt;
            color: #777;
            padding-top: 1.5mm;
        }

        /* Print Styles */
        @media print {
            html, body {
                width: 210mm;
                height: 297mm;
                margin: 0 !important;
                padding: 0 !important;
            }

            .page {
                margin: 0;
                page-break-after: always;
            }

            .page:last-child {
                page-break-after: auto;
            }

            .items-table th {
                background: var(--cyan) !important;
                -webkit-print-color-adjust: exact;
            }

            .terms-table th {
                background: #f0f0f0 !important;
                -webkit-print-color-adjust: exact;
            }

            .notes-title {
                background: var(--cyan) !important;
                -webkit-print-color-adjust: exact;
            }

            .no-print {
                display: none !important;
            }
        }

        /* Screen Preview */
        @media screen {
            body {
                background: #444;
                padding: 20mm 10mm;
            }

            .page {
                box-shadow: 0 0 20px rgba(0,0,0,0.5);
                margin-bottom: 15mm;
            }
        }
    </style>
</head>
<body>

<!-- Action Buttons -->
<div class="action-buttons no-print">
    <a href="view.php?id=<?= $id ?>" class="action-btn btn-back">
        ← Back
    </a>
    <button class="action-btn btn-print" onclick="window.print()">
        🖨️ Print
    </button>
</div>

<?php foreach ($pages as $pageIndex => $pageItems): ?>
<?php 
$isFirstPage = ($pageIndex === 0);
$isLastPage = ($pageIndex === $totalPages - 1);
$startItemNum = ($pageIndex === 0) ? 1 : $itemsPerFirstPage + ($pageIndex - 1) * $itemsPerNextPage + 1;
?>

<div class="page">
    <!-- HEADER -->
    <div class="header">
        <table class="header-table">
            <tr>
                <td class="header-left">
                    <?php if ($logoExists): ?>
                        <img src="<?= $logoPath ?>" class="logo-img" alt="Company Logo">
                    <?php else: ?>
                        <div class="logo-text"><?= htmlspecialchars($settings['company_name'] ?? 'DOT INK (PVT) LTD') ?></div>
                    <?php endif; ?>
                </td>
                <td class="header-right">
                    <div class="company-name"><?= htmlspecialchars($settings['company_name'] ?? 'DOT INK (PVT) LTD') ?></div>
                    <div class="company-details">
                        <?= htmlspecialchars($settings['company_address'] ?? '218/13A, Ranimadama, Enderamulla, Wattala.') ?><br>
                        Hotline: <?= htmlspecialchars($settings['company_hotline'] ?? '075 966 166 8') ?> | General: <?= htmlspecialchars($settings['company_general'] ?? '011 368 780') ?><br>
                        Email: <?= htmlspecialchars($settings['company_email'] ?? 'info@dotink.lk') ?> | <?= htmlspecialchars($settings['company_website'] ?? 'www.dotink.lk') ?><br>
                        VAT Reg: <?= htmlspecialchars($settings['company_vat'] ?? '178769677-7000') ?>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- CONTENT -->
    <div class="content">
        <!-- Title -->
        <div class="title">
            <h1>QUOTATION</h1>
            <?php if ($totalPages > 1): ?>
            <div class="page-indicator">Page <?= $pageIndex + 1 ?> of <?= $totalPages ?></div>
            <?php endif; ?>
        </div>

        <?php if ($isFirstPage): ?>
        <!-- Customer Info -->
        <table class="info-table">
            <tr>
                <td class="lbl">Attention</td>
                <td class="col">:</td>
                <td><strong><?= htmlspecialchars($quotation['customer_contact'] ?: $quotation['customer_name']) ?></strong></td>
                <td class="lbl">Quotation No</td>
                <td class="col">:</td>
                <td><strong style="color:var(--blue)"><?= htmlspecialchars($quotation['quotation_no']) ?></strong></td>
            </tr>
            <tr>
                <td class="lbl">Company</td>
                <td class="col">:</td>
                <td><strong><?= htmlspecialchars($quotation['customer_name']) ?></strong></td>
                <td class="lbl">Date</td>
                <td class="col">:</td>
                <td><?= date('F d, Y', strtotime($quotation['quotation_date'])) ?></td>
            </tr>
            <tr>
                <td class="lbl">Phone</td>
                <td class="col">:</td>
                <td><?= htmlspecialchars($quotation['customer_phone'] ?: '-') ?></td>
                <td class="lbl">Email</td>
                <td class="col">:</td>
                <td><?= htmlspecialchars($quotation['customer_email'] ?: '-') ?></td>
            </tr>
            <tr>
                <td class="lbl">Address</td>
                <td class="col">:</td>
                <td colspan="4"><?= htmlspecialchars($quotation['customer_address'] ?: '-') ?></td>
            </tr>
        </table>

        <!-- Terms -->
        <table class="terms-table">
            <tr>
                <th>Delivery</th>
                <th>Validity</th>
                <th>Payment</th>
                <th>Stock</th>
            </tr>
            <tr>
                <td><?= htmlspecialchars($quotation['delivery_terms'] ?: '-') ?></td>
                <td><?= htmlspecialchars($quotation['validity'] ?: '-') ?></td>
                <td><?= htmlspecialchars($quotation['payment_terms'] ?: '-') ?></td>
                <td><?= htmlspecialchars($quotation['stock_availability'] ?: '-') ?></td>
            </tr>
        </table>
        <?php else: ?>
        <div class="continued-note">... continued from previous page</div>
        <?php endif; ?>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width:5%">#</th>
                    <th style="width:12%">Code</th>
                    <?php if ($quotation['vat_enabled']): ?>
                    <th style="width:28%">Product / Specification</th>
                    <?php else: ?>
                    <th style="width:35%">Product / Specification</th>
                    <?php endif; ?>
                    <th style="width:10%">Warranty</th>
                    <th style="width:6%">Qty</th>
                    <th style="width:12%">Unit Price</th>
                    <?php if ($quotation['vat_enabled']): ?>
                    <th style="width:10%">VAT</th>
                    <?php endif; ?>
                    <th style="width:13%">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pageItems as $index => $item): ?>
                <tr>
                    <td class="txt-c"><?= $startItemNum + $index ?></td>
                    <td class="txt-c"><strong><?= htmlspecialchars($item['product_code'] ?: 'N/A') ?></strong></td>
                    <td>
                        <div class="prd-name"><?= htmlspecialchars($item['product_name']) ?></div>
                        <?php if ($item['product_description']): ?>
                        <div class="prd-desc"><?= htmlspecialchars($item['product_description']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="txt-c"><?= formatWarrantyPrint($item['warranty_value'], $item['warranty_type']) ?></td>
                    <td class="txt-c"><?= $item['quantity'] ?></td>
                    <td class="txt-r">Rs. <?= number_format($item['unit_price'], 2) ?></td>
                    <?php if ($quotation['vat_enabled']): ?>
                    <td class="txt-r">Rs. <?= number_format($item['vat_amount'], 2) ?></td>
                    <?php endif; ?>
                    <td class="txt-r"><strong>Rs. <?= number_format($item['line_total'], 2) ?></strong></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($isLastPage): ?>
        <!-- Totals -->
        <div class="totals-wrapper">
            <table class="totals-table">
                <tr>
                    <td class="totals-left">
                        <div class="words-box">
                            <strong>Amount in Words:</strong><br>
                            <?= $amountInWords ?>
                        </div>
                    </td>
                    <td class="totals-right">
                        <table class="amounts-table">
                            <tr>
                                <td><strong>Sub Total</strong></td>
                                <td class="txt-r"><strong>Rs. <?= number_format($quotation['subtotal'], 2) ?></strong></td>
                            </tr>
                            <?php if ($quotation['discount'] > 0): ?>
                            <tr class="discount-row">
                                <td><strong>Discount</strong></td>
                                <td class="txt-r"><strong>- Rs. <?= number_format($quotation['discount'], 2) ?></strong></td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($quotation['vat_enabled']): ?>
                            <tr class="vat-row">
                                <td><strong>VAT (<?= $quotation['vat_percentage'] ?>%)</strong></td>
                                <td class="txt-r"><strong>Rs. <?= number_format($quotation['tax_amount'], 2) ?></strong></td>
                            </tr>
                            <?php endif; ?>
                            <tr class="total-row">
                                <td><strong>GRAND TOTAL</strong></td>
                                <td class="txt-r"><strong>Rs. <?= number_format($quotation['grand_total'], 2) ?></strong></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Notes -->
        <?php if ($quotation['notes']): ?>
        <div class="notes-box">
            <div class="notes-title">Special Notes</div>
            <div class="notes-content">
                <?= nl2br(htmlspecialchars($quotation['notes'])) ?>
            </div>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- FOOTER -->
    <div class="footer">
        <table class="footer-contact-table">
            <tr>
                <td class="footer-contact-left">
                    <strong>For Sales and Technical Inquiries:</strong><br>
                    <span class="footer-contact-name"><?= htmlspecialchars($settings['prepared_by_name'] ?? 'M.ASHAN') ?></span><br>
                    <?= htmlspecialchars($settings['prepared_by_title'] ?? 'Executive - Operation Management') ?> | Mobile: <?= htmlspecialchars($settings['prepared_by_mobile'] ?? '075 966 166 8') ?>
                </td>
                <td class="footer-contact-right">
                    <strong style="color:var(--blue);"><?= htmlspecialchars($quotation['quotation_no']) ?></strong>
                    <?php if ($totalPages > 1): ?>
                    <br><small>Page <?= $pageIndex + 1 ?>/<?= $totalPages ?></small>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
        <div class="footer-line"></div>
        <div class="footer-copyright">
            © <?= date('Y') ?> <?= htmlspecialchars($settings['company_name'] ?? 'DOT INK (PVT) LTD') ?> • This Document is Copyright Protected
        </div>
    </div>
</div>

<?php endforeach; ?>

</body>
</html>