/**
 * DOT INK Quotation System
 * Card-based Product Popup + Edit Item Modal
 */

document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('quotationForm')) {
        initQuotationForm();
    }
});

function initQuotationForm() {
    // Elements
    const form = document.getElementById('quotationForm');
    const customerNameInput = document.getElementById('customer_name');
    const customerDropdown = document.getElementById('customerDropdown');
    const itemsBody = document.getElementById('itemsBody');
    const noItemsRow = document.getElementById('noItemsRow');
    const tableFoot = document.getElementById('tableFoot');
    const vatEnabledCheckbox = document.getElementById('vat_enabled');
    const discountInput = document.getElementById('discount');
    const saveBtn = document.getElementById('saveBtn');
    const savePrintBtn = document.getElementById('savePrintBtn');
    
    const productModal = new bootstrap.Modal(document.getElementById('productModal'));
    const editItemModal = new bootstrap.Modal(document.getElementById('editItemModal'));
    
    let itemCounter = 0;
    let addedItemsCount = 0;
    
    // ==========================================
    // CUSTOMER INLINE SEARCH
    // ==========================================
    
    let customerSearchTimeout;
    
    customerNameInput.addEventListener('input', function() {
        clearTimeout(customerSearchTimeout);
        const query = this.value.trim();
        
        if (query.length < 1) {
            hideCustomerDropdown();
            document.getElementById('is_new_customer').value = '1';
            return;
        }
        
        customerSearchTimeout = setTimeout(() => searchCustomers(query), 250);
    });
    
    customerNameInput.addEventListener('focus', function() {
        if (this.value.trim().length >= 1) {
            searchCustomers(this.value.trim());
        }
    });
    
    document.addEventListener('click', function(e) {
        if (!customerNameInput.contains(e.target) && !customerDropdown.contains(e.target)) {
            hideCustomerDropdown();
        }
    });
    
    function searchCustomers(query) {
        fetch(`${CONFIG.baseUrl}/ajax/search_customers.php?q=${encodeURIComponent(query)}`)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showCustomerDropdown(data.data, query);
                }
            })
            .catch(() => hideCustomerDropdown());
    }
    
    function showCustomerDropdown(customers, query) {
        customerDropdown.innerHTML = '';
        
        customers.forEach(c => {
            const item = document.createElement('div');
            item.className = 'autocomplete-item';
            item.innerHTML = `
                <div class="fw-bold">${escapeHtml(c.company_name)}</div>
                <small class="text-muted">${escapeHtml(c.contact_person || '')} | ${escapeHtml(c.phone || '')}</small>
            `;
            item.addEventListener('click', () => selectCustomer(c));
            customerDropdown.appendChild(item);
        });
        
        const addNew = document.createElement('div');
        addNew.className = 'autocomplete-item add-new';
        addNew.innerHTML = `<i class="bi bi-plus-circle me-2"></i>Add "<strong>${escapeHtml(query)}</strong>" as new`;
        addNew.addEventListener('click', () => {
            document.getElementById('customer_id').value = '';
            document.getElementById('is_new_customer').value = '1';
            hideCustomerDropdown();
        });
        customerDropdown.appendChild(addNew);
        
        customerDropdown.style.display = 'block';
    }
    
    function hideCustomerDropdown() {
        customerDropdown.style.display = 'none';
    }
    
    function selectCustomer(c) {
        document.getElementById('customer_id').value = c.id;
        document.getElementById('customer_name').value = c.company_name;
        document.getElementById('customer_contact').value = c.contact_person || '';
        document.getElementById('customer_phone').value = c.phone || '';
        document.getElementById('customer_email').value = c.email || '';
        document.getElementById('customer_address').value = c.address || '';
        document.getElementById('is_new_customer').value = '0';
        hideCustomerDropdown();
    }
    
    // ==========================================
    // PRODUCT MODAL - CARD FORMAT
    // ==========================================
    
    document.getElementById('openProductModal').addEventListener('click', function() {
        loadProducts('');
        document.getElementById('productSearchInput').value = '';
        addedItemsCount = 0;
        updateAddedCount();
        productModal.show();
        setTimeout(() => document.getElementById('productSearchInput').focus(), 300);
    });
    
    let productSearchTimeout;
    document.getElementById('productSearchInput').addEventListener('input', function() {
        clearTimeout(productSearchTimeout);
        productSearchTimeout = setTimeout(() => loadProducts(this.value.trim()), 250);
    });
    
    document.getElementById('clearProductSearch').addEventListener('click', function() {
        document.getElementById('productSearchInput').value = '';
        loadProducts('');
    });
    
    document.getElementById('showNewProductRow').addEventListener('click', function() {
        addNewProductCard();
    });
    
    function loadProducts(query) {
        const container = document.getElementById('productCardsContainer');
        const loading = document.getElementById('productLoading');
        const noResults = document.getElementById('productNoResults');
        
        loading.style.display = 'block';
        noResults.style.display = 'none';
        container.innerHTML = '';
        
        fetch(`${CONFIG.baseUrl}/ajax/search_products.php?q=${encodeURIComponent(query)}`)
            .then(r => r.json())
            .then(data => {
                loading.style.display = 'none';
                
                if (data.success && data.data.length > 0) {
                    data.data.forEach(product => {
                        container.appendChild(createProductCard(product));
                    });
                } else {
                    noResults.style.display = 'block';
                }
            })
            .catch(() => {
                loading.style.display = 'none';
                noResults.style.display = 'block';
            });
    }
    
    function createProductCard(product) {
        const card = document.createElement('div');
        card.className = 'product-card';
        card.dataset.productId = product.id || '';
        card.dataset.productCode = product.product_code || '';
        
        const warrantyValueOptions = '<option value="">-</option>' + 
            Array.from({length: 60}, (_, i) => 
                `<option value="${i+1}" ${product.warranty_value == i+1 ? 'selected' : ''}>${i+1}</option>`
            ).join('');
        
        const warrantyTypeOptions = `
            <option value="">Type</option>
            <option value="Days" ${product.warranty_type === 'Days' ? 'selected' : ''}>Days</option>
            <option value="Weeks" ${product.warranty_type === 'Weeks' ? 'selected' : ''}>Weeks</option>
            <option value="Months" ${product.warranty_type === 'Months' ? 'selected' : ''}>Months</option>
            <option value="Years" ${product.warranty_type === 'Years' ? 'selected' : ''}>Years</option>
            <option value="Lifetime" ${product.warranty_type === 'Lifetime' ? 'selected' : ''}>Lifetime</option>
        `;
        
        card.innerHTML = `
            <div class="product-card-header">
                <span class="product-code">${escapeHtml(product.product_code || 'NEW')}</span>
                <button type="button" class="btn btn-success btn-sm add-product-btn">
                    <i class="bi bi-plus-lg me-1"></i>Add
                </button>
            </div>
            <div class="product-card-body">
                <div class="row g-2">
                    <div class="col-12">
                        <input type="text" class="form-control product-name-input" 
                               value="${escapeHtml(product.product_name || '')}" 
                               placeholder="Product Name *">
                    </div>
                    <div class="col-12">
                        <input type="text" class="form-control form-control-sm product-desc-input" 
                               value="${escapeHtml(product.description || '')}" 
                               placeholder="Description / Specifications">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label small mb-1">Warranty</label>
                        <div class="input-group input-group-sm">
                            <select class="form-select warranty-value-select">${warrantyValueOptions}</select>
                            <select class="form-select warranty-type-select">${warrantyTypeOptions}</select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Qty</label>
                        <input type="number" class="form-control form-control-sm text-center product-qty-input" 
                               value="1" min="1">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small mb-1">Unit Price</label>
                        <input type="number" class="form-control form-control-sm text-end product-price-input" 
                               value="${parseFloat(product.default_price || 0).toFixed(2)}" min="0" step="0.01">
                    </div>
                </div>
            </div>
            <div class="product-card-footer">
                <span class="text-muted">Total:</span>
                <strong class="product-total text-success">Rs. ${formatNumber(product.default_price || 0)}</strong>
            </div>
        `;
        
        // Event listeners
        const priceInput = card.querySelector('.product-price-input');
        const qtyInput = card.querySelector('.product-qty-input');
        const totalDisplay = card.querySelector('.product-total');
        
        function updateTotal() {
            const price = parseFloat(priceInput.value) || 0;
            const qty = parseInt(qtyInput.value) || 1;
            totalDisplay.textContent = 'Rs. ' + formatNumber(price * qty);
        }
        
        priceInput.addEventListener('input', updateTotal);
        qtyInput.addEventListener('input', updateTotal);
        
        card.querySelector('.add-product-btn').addEventListener('click', function() {
            addProductFromCard(card);
        });
        
        return card;
    }
    
    function addNewProductCard() {
        const container = document.getElementById('productCardsContainer');
        const loading = document.getElementById('productLoading');
        const noResults = document.getElementById('productNoResults');
        
        loading.style.display = 'none';
        noResults.style.display = 'none';
        
        const newCard = createProductCard({
            id: '',
            product_code: '',
            product_name: '',
            description: '',
            default_price: 0,
            warranty_value: '',
            warranty_type: ''
        });
        
        newCard.classList.add('new-product');
        container.insertBefore(newCard, container.firstChild);
        newCard.querySelector('.product-name-input').focus();
    }
    
    function addProductFromCard(card) {
        const name = card.querySelector('.product-name-input').value.trim();
        
        if (!name) {
            alert('Please enter product name');
            card.querySelector('.product-name-input').focus();
            return;
        }
        
        const data = {
            product_id: card.dataset.productId || '',
            product_code: card.dataset.productCode || '',
            product_name: name,
            description: card.querySelector('.product-desc-input').value.trim(),
            warranty_value: card.querySelector('.warranty-value-select').value,
            warranty_type: card.querySelector('.warranty-type-select').value,
            unit_price: parseFloat(card.querySelector('.product-price-input').value) || 0,
            quantity: parseInt(card.querySelector('.product-qty-input').value) || 1,
            is_new_product: !card.dataset.productId
        };
        
        addItemToQuotation(data);
        
        // Visual feedback
        const btn = card.querySelector('.add-product-btn');
        card.classList.add('added');
        btn.innerHTML = '<i class="bi bi-check-lg"></i> Added';
        btn.classList.remove('btn-success');
        btn.classList.add('btn-secondary');
        btn.disabled = true;
        
        addedItemsCount++;
        updateAddedCount();
        
        // Reset
        card.querySelector('.product-qty-input').value = 1;
        
        setTimeout(() => {
            card.classList.remove('added');
            btn.innerHTML = '<i class="bi bi-plus-lg me-1"></i>Add';
            btn.classList.add('btn-success');
            btn.classList.remove('btn-secondary');
            btn.disabled = false;
        }, 2000);
    }
    
    function updateAddedCount() {
        document.getElementById('addedCountBadge').textContent = `${addedItemsCount} added`;
    }
    
    // ==========================================
    // QUOTATION ITEMS TABLE
    // ==========================================
    
    function addItemToQuotation(data) {
        if (noItemsRow) noItemsRow.style.display = 'none';
        if (tableFoot) tableFoot.style.display = 'table-footer-group';
        
        itemCounter++;
        
        const vatEnabled = vatEnabledCheckbox && vatEnabledCheckbox.checked;
        const lineSubtotal = data.unit_price * data.quantity;
        const vatAmount = vatEnabled ? lineSubtotal * (CONFIG.vatRate / 100) : 0;
        const lineTotal = lineSubtotal + vatAmount;
        const warranty = formatWarranty(data.warranty_value, data.warranty_type);
        
        const tr = document.createElement('tr');
        tr.className = 'quotation-item-row';
        tr.dataset.itemId = itemCounter;
        
        tr.innerHTML = `
            <td class="text-center align-middle item-number fw-bold">${itemCounter}</td>
            <td>
                <div class="fw-bold text-primary item-name-display">${escapeHtml(data.product_name)}</div>
                <small class="text-muted">${data.product_code ? '[' + escapeHtml(data.product_code) + ']' : '<em>New</em>'}</small>
                <div class="small text-muted item-desc-display">${escapeHtml(truncate(data.description || '', 50))}</div>
                <input type="hidden" class="item-product-id" value="${data.product_id || ''}">
                <input type="hidden" class="item-product-code" value="${escapeHtml(data.product_code || '')}">
                <input type="hidden" class="item-product-name" value="${escapeHtml(data.product_name)}">
                <input type="hidden" class="item-description" value="${escapeHtml(data.description || '')}">
                <input type="hidden" class="item-is-new" value="${data.is_new_product ? '1' : '0'}">
            </td>
            <td class="text-center align-middle">
                <span class="item-warranty-display">${warranty}</span>
                <input type="hidden" class="item-warranty-value" value="${data.warranty_value || ''}">
                <input type="hidden" class="item-warranty-type" value="${data.warranty_type || ''}">
            </td>
            <td class="text-center align-middle">
                <input type="number" class="form-control form-control-sm text-center item-quantity mx-auto" 
                       value="${data.quantity}" min="1" style="width:60px;">
            </td>
            <td class="text-end align-middle">
                <input type="number" class="form-control form-control-sm text-end item-unit-price" 
                       value="${parseFloat(data.unit_price).toFixed(2)}" min="0" step="0.01" style="width:100px;">
            </td>
            <td class="vat-column text-end align-middle" ${!vatEnabled ? 'style="display:none;"' : ''}>
                <span class="item-vat-display">${formatNumber(vatAmount)}</span>
                <input type="hidden" class="item-vat-amount" value="${vatAmount.toFixed(2)}">
            </td>
            <td class="text-end align-middle">
                <strong class="text-success item-line-total-display">Rs. ${formatNumber(lineTotal)}</strong>
                <input type="hidden" class="item-line-total" value="${lineTotal.toFixed(2)}">
            </td>
            <td class="text-center align-middle">
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-outline-primary edit-item-btn" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button type="button" class="btn btn-outline-danger remove-item-btn" title="Remove">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </td>
        `;
        
        // Event listeners
        const qtyInput = tr.querySelector('.item-quantity');
        const priceInput = tr.querySelector('.item-unit-price');
        
        qtyInput.addEventListener('input', () => { calcRowTotal(tr); calcTotals(); });
        qtyInput.addEventListener('change', function() { if(this.value < 1) this.value = 1; calcRowTotal(tr); calcTotals(); });
        
        priceInput.addEventListener('input', () => { calcRowTotal(tr); calcTotals(); });
        priceInput.addEventListener('change', function() { this.value = parseFloat(this.value || 0).toFixed(2); calcRowTotal(tr); calcTotals(); });
        
        tr.querySelector('.edit-item-btn').addEventListener('click', () => openEditItemModal(tr));
        tr.querySelector('.remove-item-btn').addEventListener('click', () => {
            tr.remove();
            updateItemNumbers();
            calcTotals();
            checkEmpty();
        });
        
        itemsBody.appendChild(tr);
        updateItemNumbers();
        calcTotals();
    }
    
    function calcRowTotal(row) {
        const qty = parseInt(row.querySelector('.item-quantity').value) || 0;
        const price = parseFloat(row.querySelector('.item-unit-price').value) || 0;
        const vatEnabled = vatEnabledCheckbox && vatEnabledCheckbox.checked;
        
        const subtotal = qty * price;
        const vat = vatEnabled ? subtotal * (CONFIG.vatRate / 100) : 0;
        const total = subtotal + vat;
        
        row.querySelector('.item-vat-amount').value = vat.toFixed(2);
        row.querySelector('.item-vat-display').textContent = formatNumber(vat);
        row.querySelector('.item-line-total').value = total.toFixed(2);
        row.querySelector('.item-line-total-display').textContent = 'Rs. ' + formatNumber(total);
    }
    
    function updateItemNumbers() {
        let n = 1;
        itemsBody.querySelectorAll('.quotation-item-row').forEach(row => {
            row.querySelector('.item-number').textContent = n++;
        });
    }
    
    function checkEmpty() {
        const rows = itemsBody.querySelectorAll('.quotation-item-row');
        if (rows.length === 0) {
            if (noItemsRow) noItemsRow.style.display = 'table-row';
            if (tableFoot) tableFoot.style.display = 'none';
        }
    }
    
    // ==========================================
    // EDIT ITEM MODAL
    // ==========================================
    
    function openEditItemModal(row) {
        document.getElementById('editItemRowId').value = row.dataset.itemId;
        document.getElementById('editItemProductId').value = row.querySelector('.item-product-id').value;
        document.getElementById('editItemProductCode').value = row.querySelector('.item-product-code').value;
        document.getElementById('editItemIsNew').value = row.querySelector('.item-is-new').value;
        
        document.getElementById('editItemName').value = row.querySelector('.item-product-name').value;
        document.getElementById('editItemCodeDisplay').value = row.querySelector('.item-product-code').value || 'New Product';
        document.getElementById('editItemDescription').value = row.querySelector('.item-description').value;
        document.getElementById('editItemWarrantyValue').value = row.querySelector('.item-warranty-value').value;
        document.getElementById('editItemWarrantyType').value = row.querySelector('.item-warranty-type').value;
        document.getElementById('editItemQuantity').value = row.querySelector('.item-quantity').value;
        document.getElementById('editItemPrice').value = row.querySelector('.item-unit-price').value;
        
        updateEditPreview();
        editItemModal.show();
    }
    
    // Edit modal preview
    ['editItemQuantity', 'editItemPrice'].forEach(id => {
        document.getElementById(id)?.addEventListener('input', updateEditPreview);
    });
    
    function updateEditPreview() {
        const qty = parseInt(document.getElementById('editItemQuantity').value) || 0;
        const price = parseFloat(document.getElementById('editItemPrice').value) || 0;
        document.getElementById('editItemPreviewTotal').textContent = 'Rs. ' + formatNumber(qty * price);
    }
    
    // Save edit
    document.getElementById('saveEditItem')?.addEventListener('click', function() {
        const rowId = document.getElementById('editItemRowId').value;
        const row = itemsBody.querySelector(`[data-item-id="${rowId}"]`);
        
        if (!row) {
            editItemModal.hide();
            return;
        }
        
        const name = document.getElementById('editItemName').value.trim();
        if (!name) {
            alert('Please enter product name');
            return;
        }
        
        // Update row data
        row.querySelector('.item-product-name').value = name;
        row.querySelector('.item-description').value = document.getElementById('editItemDescription').value;
        row.querySelector('.item-warranty-value').value = document.getElementById('editItemWarrantyValue').value;
        row.querySelector('.item-warranty-type').value = document.getElementById('editItemWarrantyType').value;
        row.querySelector('.item-quantity').value = document.getElementById('editItemQuantity').value;
        row.querySelector('.item-unit-price').value = document.getElementById('editItemPrice').value;
        
        // Update display
        row.querySelector('.item-name-display').textContent = name;
        row.querySelector('.item-desc-display').textContent = truncate(document.getElementById('editItemDescription').value, 50);
        row.querySelector('.item-warranty-display').textContent = formatWarranty(
            document.getElementById('editItemWarrantyValue').value,
            document.getElementById('editItemWarrantyType').value
        );
        
        calcRowTotal(row);
        calcTotals();
        editItemModal.hide();
    });
    
    // ==========================================
    // VAT & TOTALS
    // ==========================================
    
    vatEnabledCheckbox?.addEventListener('change', function() {
        updateVatCols();
        itemsBody.querySelectorAll('.quotation-item-row').forEach(row => calcRowTotal(row));
        calcTotals();
    });
    
    function updateVatCols() {
        const show = vatEnabledCheckbox?.checked;
        document.querySelectorAll('.vat-column').forEach(el => el.style.display = show ? '' : 'none');
        const vatRow = document.getElementById('vatRow');
        const summaryVatRow = document.getElementById('summaryVatRow');
        if (vatRow) vatRow.style.display = show ? '' : 'none';
        if (summaryVatRow) summaryVatRow.style.display = show ? '' : 'none';
    }
    
    discountInput?.addEventListener('input', calcTotals);
    discountInput?.addEventListener('change', function() {
        this.value = parseFloat(this.value || 0).toFixed(2);
        calcTotals();
    });
    
    function calcTotals() {
        let subtotal = 0;
        let totalVat = 0;
        const vatEnabled = vatEnabledCheckbox?.checked;
        
        itemsBody.querySelectorAll('.quotation-item-row').forEach(row => {
            const qty = parseInt(row.querySelector('.item-quantity').value) || 0;
            const price = parseFloat(row.querySelector('.item-unit-price').value) || 0;
            subtotal += qty * price;
            if (vatEnabled) totalVat += (qty * price) * (CONFIG.vatRate / 100);
        });
        
        const discount = parseFloat(discountInput?.value) || 0;
        const grandTotal = subtotal - discount + totalVat;
        
        document.getElementById('subtotalDisplay').textContent = 'Rs. ' + formatNumber(subtotal);
        document.getElementById('discountDisplay').textContent = (discount > 0 ? '-' : '') + 'Rs. ' + formatNumber(discount);
        document.getElementById('vatDisplay').textContent = 'Rs. ' + formatNumber(totalVat);
        document.getElementById('grandTotalDisplay').textContent = 'Rs. ' + formatNumber(grandTotal);
        
        document.getElementById('summarySubtotal').textContent = 'Rs. ' + formatNumber(subtotal);
        document.getElementById('summaryDiscount').textContent = (discount > 0 ? '-' : '') + 'Rs. ' + formatNumber(discount);
        document.getElementById('summaryVat').textContent = 'Rs. ' + formatNumber(totalVat);
        document.getElementById('summaryGrandTotal').textContent = 'Rs. ' + formatNumber(grandTotal);
        
        document.getElementById('subtotal').value = subtotal.toFixed(2);
        document.getElementById('tax_amount').value = totalVat.toFixed(2);
        document.getElementById('grand_total').value = grandTotal.toFixed(2);
    }
    
    // ==========================================
    // FORM SUBMISSION
    // ==========================================
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        saveQuotation(false);
    });
    
    savePrintBtn?.addEventListener('click', () => saveQuotation(true));
    
    function saveQuotation(print) {
        const customerName = document.getElementById('customer_name').value.trim();
        const items = itemsBody.querySelectorAll('.quotation-item-row');
        
        if (!customerName) {
            alert('Please enter customer name');
            document.getElementById('customer_name').focus();
            return;
        }
        
        if (items.length === 0) {
            alert('Please add at least one item');
            document.getElementById('openProductModal').click();
            return;
        }
        
        const data = collectData();
        
        saveBtn.disabled = true;
        const origText = saveBtn.innerHTML;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
        
        fetch(`${CONFIG.baseUrl}/ajax/save_quotation.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(r => r.json())
        .then(result => {
            if (result.success) {
                if (print) window.open('print.php?id=' + result.quotation_id, '_blank');
                window.location.href = 'view.php?id=' + result.quotation_id;
            } else {
                alert('Error: ' + result.error);
                saveBtn.disabled = false;
                saveBtn.innerHTML = origText;
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error saving quotation');
            saveBtn.disabled = false;
            saveBtn.innerHTML = origText;
        });
    }
    
    function collectData() {
        const items = [];
        
        itemsBody.querySelectorAll('.quotation-item-row').forEach(row => {
            items.push({
                product_id: row.querySelector('.item-product-id').value || null,
                product_code: row.querySelector('.item-product-code').value || null,
                product_name: row.querySelector('.item-product-name').value,
                product_description: row.querySelector('.item-description').value,
                warranty_value: row.querySelector('.item-warranty-value').value || null,
                warranty_type: row.querySelector('.item-warranty-type').value || null,
                quantity: parseInt(row.querySelector('.item-quantity').value) || 1,
                unit_price: parseFloat(row.querySelector('.item-unit-price').value) || 0,
                vat_amount: parseFloat(row.querySelector('.item-vat-amount').value) || 0,
                line_total: parseFloat(row.querySelector('.item-line-total').value) || 0,
                is_new_product: row.querySelector('.item-is-new').value === '1'
            });
        });
        
        return {
            id: document.getElementById('quotation_id').value || null,
            quotation_no: document.getElementById('quotation_no').value,
            customer_id: document.getElementById('customer_id').value || null,
            customer_name: document.getElementById('customer_name').value,
            customer_contact: document.getElementById('customer_contact').value,
            customer_phone: document.getElementById('customer_phone').value,
            customer_email: document.getElementById('customer_email').value,
            customer_address: document.getElementById('customer_address').value,
            quotation_date: document.getElementById('quotation_date').value,
            delivery_terms: document.getElementById('delivery_terms').value,
            payment_terms: document.getElementById('payment_terms').value,
            validity: document.getElementById('validity').value,
            stock_availability: document.getElementById('stock_availability').value,
            notes: document.getElementById('notes').value,
            prepared_by: document.getElementById('prepared_by').value,
            status: document.getElementById('status').value,
            vat_enabled: vatEnabledCheckbox?.checked ? 1 : 0,
            vat_percentage: CONFIG.vatRate,
            discount: parseFloat(discountInput?.value) || 0,
            discount_type: 'amount',
            subtotal: parseFloat(document.getElementById('subtotal').value) || 0,
            tax_amount: parseFloat(document.getElementById('tax_amount').value) || 0,
            grand_total: parseFloat(document.getElementById('grand_total').value) || 0,
            items: items
        };
    }
    
    // ==========================================
    // LOAD EXISTING ITEMS
    // ==========================================
    
    function loadExisting() {
        if (typeof existingItems !== 'undefined' && Array.isArray(existingItems) && existingItems.length > 0) {
            existingItems.forEach(item => {
                addItemToQuotation({
                    product_id: item.product_id || '',
                    product_code: item.product_code || '',
                    product_name: item.product_name || '',
                    description: item.product_description || '',
                    warranty_value: item.warranty_value || '',
                    warranty_type: item.warranty_type || '',
                    unit_price: parseFloat(item.unit_price) || 0,
                    quantity: parseInt(item.quantity) || 1,
                    is_new_product: false
                });
            });
        } else {
            if (noItemsRow) noItemsRow.style.display = 'table-row';
            if (tableFoot) tableFoot.style.display = 'none';
        }
    }
    
    // ==========================================
    // INIT
    // ==========================================
    
    loadExisting();
    updateVatCols();
    calcTotals();
}

// ==========================================
// HELPERS
// ==========================================

function formatNumber(n) {
    return parseFloat(n).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function escapeHtml(t) {
    if (!t) return '';
    const d = document.createElement('div');
    d.textContent = t;
    return d.innerHTML;
}

function truncate(s, l) {
    if (!s) return '';
    return s.length > l ? s.substring(0, l) + '...' : s;
}

function formatWarranty(v, t) {
    if (t === 'Lifetime') return 'Lifetime';
    if (v && t) return v + ' ' + t;
    return '-';
}