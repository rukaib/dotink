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

// Calculate amount in words
$amountInWords = 'Rupees ' . $quotationModel->numberToWords((int)$quotation['grand_total']) . ' Only';

// Pagination for items
$itemsPerFirstPage = 6;
$itemsPerNextPage = 12;
$items = $quotation['items'];
$totalItems = count($items);

// Split items into pages
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

// Format warranty
function formatWarranty($value, $type) {
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
        /* ==========================================
           RESET & BASE
           ========================================== */
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

        /* ==========================================
           PRINT & ACTION BUTTONS
           ========================================== */
        .action-buttons {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            display: flex;
            gap: 10px;
        }

        .action-btn {
            background-color: #0070C0;
            color: white;
            border: none;
            padding: 12px 25px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 5px;
            cursor: pointer;
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
            text-decoration: none;
            transition: background 0.3s;
        }

        .action-btn:hover {
            background-color: #005a9e;
            color: white;
        }

        .action-btn.print-btn {
            background-color: #198754;
        }

        .action-btn.print-btn:hover {
            background-color: #146c43;
        }

        .action-btn.back-btn {
            background-color: #6c757d;
        }

        .action-btn.back-btn:hover {
            background-color: #5a6268;
        }

        /* ==========================================
           A4 PAGE SETUP
           ========================================== */
        @page {
            size: A4 portrait;
            margin: 0;
        }

        /* ==========================================
           PAGE CONTAINER
           ========================================== */
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
            page-break-inside: avoid;
        }

        .page:last-child {
            page-break-after: auto;
        }

        /* ==========================================
           HEADER
           ========================================== */
        .header {
            border-bottom: 2.5px solid var(--cyan);
            padding-bottom: 3mm;
            margin-bottom: 3mm;
            flex-shrink: 0;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-left {
            width: 35%;
            vertical-align: middle;
        }

        .header-right {
            width: 65%;
            text-align: right;
            vertical-align: middle;
        }

        .logo-img {
            max-width: 200px;
            height: auto;
        }

        .logo-text {
            font-size: 18pt;
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
            line-height: 1.3;
        }

        /* ==========================================
           CONTENT AREA
           ========================================== */
        .content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* ==========================================
           TITLE
           ========================================== */
        .title {
            text-align: center;
            margin-bottom: 3mm;
            flex-shrink: 0;
        }

        .title h1 {
            font-size: 16pt;
            font-weight: bold;
            letter-spacing: 2px;
            margin: 0;
        }

        .page-indicator {
            font-size: 9pt;
            color: #666;
            margin-top: 1mm;
        }

        /* ==========================================
           INFO TABLE
           ========================================== */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 3mm;
            font-size: 9pt;
            border: 1px solid #ccc;
            flex-shrink: 0;
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

        .info-table .val {
            color: #000;
        }

        /* ==========================================
           TERMS TABLE
           ========================================== */
        .terms-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 3mm;
            font-size: 8pt;
            flex-shrink: 0;
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

        /* ==========================================
           ITEMS TABLE
           ========================================== */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 3mm;
            font-size: 8pt;
            border: 1px solid #000;
            flex-shrink: 0;
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
            padding: 1.5mm 1.5mm;
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

        /* Continued indicator */
        .continued-note {
            text-align: center;
            font-size: 8pt;
            color: #666;
            font-style: italic;
            padding: 2mm;
            background: #f9f9f9;
            border: 1px dashed #ccc;
            margin-bottom: 3mm;
            flex-shrink: 0;
        }

        /* ==========================================
           TOTALS
           ========================================== */
        .totals-wrapper {
            width: 100%;
            margin-bottom: 3mm;
            flex-shrink: 0;
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

        /* ==========================================
           NOTES
           ========================================== */
        .notes-box {
            border: 1px solid var(--cyan);
            margin-bottom: 3mm;
            flex-shrink: 0;
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

        /* ==========================================
           FOOTER
           ========================================== */
        .footer {
            margin-top: auto;
            flex-shrink: 0;
            padding-top: 2mm;
        }

        .footer-contact {
            padding-bottom: 2mm;
            margin-bottom: 2mm;
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

        .footer-bottom {
            margin-top: auto;
        }

        .footer-line {
            border-top: 1.5px solid var(--cyan);
            margin: 0;
        }

        .footer-copyright {
            text-align: center;
            font-size: 7pt;
            color: #777;
            padding-top: 1.5mm;
        }

        /* ==========================================
           PRINT STYLES
           ========================================== */
        @media print {
            html, body {
                width: 210mm;
                height: 297mm;
                margin: 0 !important;
                padding: 0 !important;
            }

            .page {
                width: 210mm;
                min-height: 297mm;
                padding: 10mm 15mm;
                margin: 0;
                break-after: page;
                page-break-after: always;
            }

            .page:last-child {
                break-after: auto;
                page-break-after: auto;
            }

            .items-table tr {
                page-break-inside: avoid;
            }

            .items-table th {
                background: var(--cyan) !important;
                color: #fff !important;
                -webkit-print-color-adjust: exact;
            }

            .terms-table th {
                background: #f0f0f0 !important;
                -webkit-print-color-adjust: exact;
            }

            .notes-title {
                background: var(--cyan) !important;
                color: #fff !important;
                -webkit-print-color-adjust: exact;
            }

            .words-box {
                background: #f9f9f9 !important;
                -webkit-print-color-adjust: exact;
            }

            .footer-line {
                border-top-color: var(--cyan) !important;
                -webkit-print-color-adjust: exact;
            }

            .no-print {
                display: none !important;
            }
        }

        /* ==========================================
           SCREEN PREVIEW
           ========================================== */
        @media screen {
            body {
                background: #555;
                padding: 20mm 10mm;
            }

            .page {
                box-shadow: 0 0 20px rgba(0,0,0,0.4);
                margin-bottom: 10mm;
            }
        }
    </style>
</head>
<body>

<!-- Action Buttons (No Print) -->
<div class="action-buttons no-print">
    <a href="view.php?id=<?= $id ?>" class="action-btn back-btn">← Back</a>
    <button class="action-btn print-btn" onclick="window.print()">🖨️ Print</button>
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
                    <?php if (!empty($settings['company_logo']) && file_exists($settings['company_logo'])): ?>
                    <img src="<?= $settings['company_logo'] ?>" class="logo-img" alt="Logo">
                    <?php else: ?>
                    <div class="logo-text"><?= htmlspecialchars($settings['company_name'] ?? 'DOT INK (PVT) LTD') ?></div>
                    <?php endif; ?>
                </td>
                <td class="header-right">
                    <div class="company-name"><?= htmlspecialchars($settings['company_name'] ?? 'DOT INK (PVT) LTD') ?></div>
                    <div class="company-details">
                        <?= htmlspecialchars($settings['company_address'] ?? '') ?><br>
                        Hotline: <?= htmlspecialchars($settings['company_hotline'] ?? '') ?> | General: <?= htmlspecialchars($settings['company_general'] ?? '') ?><br>
                        Email: <?= htmlspecialchars($settings['company_email'] ?? '') ?> | <?= htmlspecialchars($settings['company_website'] ?? '') ?> | VAT: <?= htmlspecialchars($settings['company_vat'] ?? '') ?>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- CONTENT AREA -->
    <div class="content">
        <!-- TITLE -->
        <div class="title">
            <h1>QUOTATION</h1>
            <?php if ($totalPages > 1): ?>
            <div class="page-indicator">Page <?= $pageIndex + 1 ?> of <?= $totalPages ?></div>
            <?php endif; ?>
        </div>

        <?php if ($isFirstPage): ?>
        <!-- CUSTOMER INFO (only on first page) -->
        <table class="info-table">
            <tr>
                <td class="lbl">Attention</td>
                <td class="col">:</td>
                <td class="val"><strong><?= htmlspecialchars($quotation['customer_contact'] ?: $quotation['customer_name']) ?></strong></td>
                <td class="lbl">Quotation No</td>
                <td class="col">:</td>
                <td class="val"><strong style="color:var(--blue)"><?= htmlspecialchars($quotation['quotation_no']) ?></strong></td>
            </tr>
            <tr>
                <td class="lbl">Company</td>
                <td class="col">:</td>
                <td class="val"><strong><?= htmlspecialchars($quotation['customer_name']) ?></strong></td>
                <td class="lbl">Date</td>
                <td class="col">:</td>
                <td class="val"><?= date('F d, Y', strtotime($quotation['quotation_date'])) ?></td>
            </tr>
            <tr>
                <td class="lbl">Customer ID</td>
                <td class="col">:</td>
                <td class="val"><?= htmlspecialchars($quotation['customer_code'] ?? 'N/A') ?></td>
                <td class="lbl">Phone</td>
                <td class="col">:</td>
                <td class="val"><?= htmlspecialchars($quotation['customer_phone'] ?: '-') ?></td>
            </tr>
            <tr>
                <td class="lbl">Address</td>
                <td class="col">:</td>
                <td class="val"><?= htmlspecialchars($quotation['customer_address'] ?: '-') ?></td>
                <td class="lbl">Email</td>
                <td class="col">:</td>
                <td class="val"><?= htmlspecialchars($quotation['customer_email'] ?: '-') ?></td>
            </tr>
        </table>

        <!-- TERMS (only on first page) -->
        <table class="terms-table">
            <tr>
                <th>Delivery</th>
                <th>Validity</th>
                <th>Payments</th>
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
        <!-- Continued note for subsequent pages -->
        <div class="continued-note">
            ... continued from previous page
        </div>
        <?php endif; ?>

        <!-- ITEMS TABLE -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width:5%">#</th>
                    <th style="width:10%">Product ID</th>
                    <?php if ($quotation['vat_enabled']): ?>
                    <th style="width:30%">Product / Specification</th>
                    <?php else: ?>
                    <th style="width:37%">Product / Specification</th>
                    <?php endif; ?>
                    <th style="width:9%">Warranty</th>
                    <th style="width:6%">Qty</th>
                    <th style="width:12%">Unit Price</th>
                    <?php if ($quotation['vat_enabled']): ?>
                    <th style="width:8%">VAT</th>
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
                    <td class="txt-c"><?= formatWarranty($item['warranty_value'], $item['warranty_type']) ?></td>
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
        <!-- TOTALS (only on last page) -->
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
                                <td class="txt-r"><strong>-Rs. <?= number_format($quotation['discount'], 2) ?></strong></td>
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

        <!-- NOTES (only on last page) -->
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
        <div class="footer-contact">
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
        </div>

        <div class="footer-bottom">
            <div class="footer-line"></div>
            <div class="footer-copyright">
                © <?= htmlspecialchars($settings['company_name'] ?? 'DOT INK (PVT) LTD') ?> • This Document is Copyright to <?= htmlspecialchars($settings['company_name'] ?? 'DOT INK (PVT) LTD') ?>
            </div>
        </div>
    </div>
</div>

<?php endforeach; ?>

</body>
</html>