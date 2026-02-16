<?php
/**
 * Quotation Model Class
 */

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Customer.php';
require_once __DIR__ . '/Product.php';

class Quotation {
    private $db;
    private $table = 'quotations';
    private $itemsTable = 'quotation_items';
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Get next quotation number
     */
    public function getNextQuotationNo() {
        $currentYear = date('Y');
        $prefix = "QT-{$currentYear}-";
        
        $sql = "SELECT MAX(CAST(SUBSTRING(quotation_no, 9) AS UNSIGNED)) as max_num 
                FROM {$this->table} WHERE quotation_no LIKE :prefix";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':prefix' => $prefix . '%']);
        $result = $stmt->fetch();
        
        $nextNum = ($result['max_num'] ?? 0) + 1;
        return $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
    }
    
    /**
     * Get quotation by ID with items
     */
    /**
 * Get quotation by ID with items
 */
public function getById($id) {
    $sql = "SELECT q.*, c.customer_code 
            FROM {$this->table} q
            LEFT JOIN customers c ON q.customer_id = c.id
            WHERE q.id = ?";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([$id]);
    $quotation = $stmt->fetch();
    
    if ($quotation) {
        $quotation['items'] = $this->getItems($id);
    }
    
    return $quotation;
}

/**
 * Get quotation items
 */
public function getItems($quotationId) {
    $sql = "SELECT * FROM {$this->itemsTable} 
            WHERE quotation_id = ? 
            ORDER BY item_order ASC";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([$quotationId]);
    return $stmt->fetchAll();
}
    
    /**
     * Get all quotations with pagination
     */
    public function getAll($page = 1, $perPage = ITEMS_PER_PAGE, $filters = []) {
        $offset = ($page - 1) * $perPage;
        $where = "1=1";
        $params = [];
        
        if (!empty($filters['status'])) {
            $where .= " AND q.status = :status";
            $params[':status'] = $filters['status'];
        }
        
        if (!empty($filters['search'])) {
            $where .= " AND (q.quotation_no LIKE :search OR q.customer_name LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        
        if (!empty($filters['date_from'])) {
            $where .= " AND q.quotation_date >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $where .= " AND q.quotation_date <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }
        
        $sql = "SELECT q.*, c.customer_code 
                FROM {$this->table} q
                LEFT JOIN customers c ON q.customer_id = c.id
                WHERE {$where}
                ORDER BY q.created_at DESC 
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', (int)$perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Count quotations with filters
     */
    public function count($filters = []) {
        $where = "1=1";
        $params = [];
        
        if (!empty($filters['status'])) {
            $where .= " AND status = :status";
            $params[':status'] = $filters['status'];
        }
        
        if (!empty($filters['search'])) {
            $where .= " AND (quotation_no LIKE :search OR customer_name LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE {$where}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['total'];
    }
    
    /**
     * Create new quotation
     */
    public function create($data) {
        $this->db->beginTransaction();
        
        try {
            $customerId = null;
            
            // Handle new customer creation
            if (empty($data['customer_id']) && !empty($data['customer_name'])) {
                $customerModel = new Customer();
                $customerId = $customerModel->create([
                    'company_name' => $data['customer_name'],
                    'contact_person' => $data['customer_contact'] ?? '',
                    'phone' => $data['customer_phone'] ?? '',
                    'email' => $data['customer_email'] ?? '',
                    'address' => $data['customer_address'] ?? ''
                ]);
            } else {
                $customerId = $data['customer_id'] ?? null;
            }
            
            // Insert quotation
            $sql = "INSERT INTO {$this->table} 
                    (quotation_no, customer_id, customer_name, customer_contact, 
                     customer_phone, customer_email, customer_address, quotation_date,
                     subtotal, discount, discount_type, vat_enabled, vat_percentage, 
                     tax_amount, grand_total, delivery_terms, payment_terms, validity,
                     stock_availability, notes, prepared_by, status) 
                    VALUES 
                    (:quotation_no, :customer_id, :customer_name, :customer_contact,
                     :customer_phone, :customer_email, :customer_address, :quotation_date,
                     :subtotal, :discount, :discount_type, :vat_enabled, :vat_percentage,
                     :tax_amount, :grand_total, :delivery_terms, :payment_terms, :validity,
                     :stock_availability, :notes, :prepared_by, :status)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':quotation_no' => $data['quotation_no'],
                ':customer_id' => $customerId,
                ':customer_name' => $this->sanitize($data['customer_name']),
                ':customer_contact' => $this->sanitize($data['customer_contact'] ?? ''),
                ':customer_phone' => $this->sanitize($data['customer_phone'] ?? ''),
                ':customer_email' => $this->sanitize($data['customer_email'] ?? ''),
                ':customer_address' => $this->sanitize($data['customer_address'] ?? ''),
                ':quotation_date' => $data['quotation_date'],
                ':subtotal' => (float)$data['subtotal'],
                ':discount' => (float)($data['discount'] ?? 0),
                ':discount_type' => $data['discount_type'] ?? 'amount',
                ':vat_enabled' => (int)($data['vat_enabled'] ?? 0),
                ':vat_percentage' => (float)($data['vat_percentage'] ?? DEFAULT_VAT_RATE),
                ':tax_amount' => (float)($data['tax_amount'] ?? 0),
                ':grand_total' => (float)$data['grand_total'],
                ':delivery_terms' => $this->sanitize($data['delivery_terms'] ?? ''),
                ':payment_terms' => $this->sanitize($data['payment_terms'] ?? ''),
                ':validity' => $this->sanitize($data['validity'] ?? ''),
                ':stock_availability' => $this->sanitize($data['stock_availability'] ?? ''),
                ':notes' => $this->sanitize($data['notes'] ?? ''),
                ':prepared_by' => $this->sanitize($data['prepared_by'] ?? ''),
                ':status' => $data['status'] ?? 'Draft'
            ]);
            
            $quotationId = $this->db->lastInsertId();
            
            // Insert quotation items
            if (!empty($data['items'])) {
                $this->saveItems($quotationId, $data['items']);
            }
            
            $this->db->commit();
            return $quotationId;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    /**
     * Update quotation
     */
    public function update($id, $data) {
        $this->db->beginTransaction();
        
        try {
            $customerId = $data['customer_id'] ?? null;
            
            // Handle new customer creation
            if (empty($data['customer_id']) && !empty($data['customer_name'])) {
                $customerModel = new Customer();
                $customerId = $customerModel->create([
                    'company_name' => $data['customer_name'],
                    'contact_person' => $data['customer_contact'] ?? '',
                    'phone' => $data['customer_phone'] ?? '',
                    'email' => $data['customer_email'] ?? '',
                    'address' => $data['customer_address'] ?? ''
                ]);
            }
            
            // Update quotation
            $sql = "UPDATE {$this->table} SET 
                    customer_id = :customer_id,
                    customer_name = :customer_name,
                    customer_contact = :customer_contact,
                    customer_phone = :customer_phone,
                    customer_email = :customer_email,
                    customer_address = :customer_address,
                    quotation_date = :quotation_date,
                    subtotal = :subtotal,
                    discount = :discount,
                    discount_type = :discount_type,
                    vat_enabled = :vat_enabled,
                    vat_percentage = :vat_percentage,
                    tax_amount = :tax_amount,
                    grand_total = :grand_total,
                    delivery_terms = :delivery_terms,
                    payment_terms = :payment_terms,
                    validity = :validity,
                    stock_availability = :stock_availability,
                    notes = :notes,
                    prepared_by = :prepared_by,
                    status = :status
                    WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':id' => $id,
                ':customer_id' => $customerId,
                ':customer_name' => $this->sanitize($data['customer_name']),
                ':customer_contact' => $this->sanitize($data['customer_contact'] ?? ''),
                ':customer_phone' => $this->sanitize($data['customer_phone'] ?? ''),
                ':customer_email' => $this->sanitize($data['customer_email'] ?? ''),
                ':customer_address' => $this->sanitize($data['customer_address'] ?? ''),
                ':quotation_date' => $data['quotation_date'],
                ':subtotal' => (float)$data['subtotal'],
                ':discount' => (float)($data['discount'] ?? 0),
                ':discount_type' => $data['discount_type'] ?? 'amount',
                ':vat_enabled' => (int)($data['vat_enabled'] ?? 0),
                ':vat_percentage' => (float)($data['vat_percentage'] ?? DEFAULT_VAT_RATE),
                ':tax_amount' => (float)($data['tax_amount'] ?? 0),
                ':grand_total' => (float)$data['grand_total'],
                ':delivery_terms' => $this->sanitize($data['delivery_terms'] ?? ''),
                ':payment_terms' => $this->sanitize($data['payment_terms'] ?? ''),
                ':validity' => $this->sanitize($data['validity'] ?? ''),
                ':stock_availability' => $this->sanitize($data['stock_availability'] ?? ''),
                ':notes' => $this->sanitize($data['notes'] ?? ''),
                ':prepared_by' => $this->sanitize($data['prepared_by'] ?? ''),
                ':status' => $data['status'] ?? 'Draft'
            ]);
            
            // Delete existing items and re-insert
            $this->deleteItems($id);
            if (!empty($data['items'])) {
                $this->saveItems($id, $data['items']);
            }
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    /**
     * Save quotation items
     */
    private function saveItems($quotationId, $items) {
        $productModel = new Product();
        
        $sql = "INSERT INTO {$this->itemsTable} 
                (quotation_id, product_id, product_code, product_name, product_description,
                 product_image, warranty_value, warranty_type, quantity, unit_price, 
                 vat_amount, line_total, item_order) 
                VALUES 
                (:quotation_id, :product_id, :product_code, :product_name, :product_description,
                 :product_image, :warranty_value, :warranty_type, :quantity, :unit_price,
                 :vat_amount, :line_total, :item_order)";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($items as $index => $item) {
            $productId = $item['product_id'] ?? null;
            $productCode = $item['product_code'] ?? null;
            $productImage = $item['product_image'] ?? null;
            
            // If new product, create it
            if (empty($productId) && !empty($item['is_new_product'])) {
                $result = $productModel->create([
                    'product_name' => $item['product_name'],
                    'description' => $item['product_description'] ?? '',
                    'default_price' => $item['unit_price'],
                    'default_warranty_value' => $item['warranty_value'] ?? null,
                    'default_warranty_type' => $item['warranty_type'] ?? null
                ]);
                $productId = $result['id'];
                $productCode = $result['product_code'];
            }
            
            $stmt->execute([
                ':quotation_id' => $quotationId,
                ':product_id' => $productId,
                ':product_code' => $productCode,
                ':product_name' => $this->sanitize($item['product_name']),
                ':product_description' => $this->sanitize($item['product_description'] ?? ''),
                ':product_image' => $productImage,
                ':warranty_value' => $item['warranty_value'] ?? null,
                ':warranty_type' => $item['warranty_type'] ?? null,
                ':quantity' => (int)$item['quantity'],
                ':unit_price' => (float)$item['unit_price'],
                ':vat_amount' => (float)($item['vat_amount'] ?? 0),
                ':line_total' => (float)$item['line_total'],
                ':item_order' => $index + 1
            ]);
        }
    }
    
    /**
     * Delete quotation items
     */
    private function deleteItems($quotationId) {
        $sql = "DELETE FROM {$this->itemsTable} WHERE quotation_id = :quotation_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':quotation_id' => $quotationId]);
    }
    
    /**
     * Delete quotation
     */
    public function delete($id) {
        $this->db->beginTransaction();
        
        try {
            // Items will be deleted by CASCADE
            $sql = "DELETE FROM {$this->table} WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    /**
     * Get company settings
     */
    public function getCompanySettings() {
        $sql = "SELECT setting_key, setting_value FROM company_settings";
        $stmt = $this->db->query($sql);
        $results = $stmt->fetchAll();
        
        $settings = [];
        foreach ($results as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        
        return $settings;
    }
    
    /**
     * Sanitize input
     */
    private function sanitize($input) {
        if (is_null($input)) return '';
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Convert number to words (for amount)
     */
    public function numberToWords($number) {
        $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 
                 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 
                 'Seventeen', 'Eighteen', 'Nineteen'];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
        
        $number = (int)$number;
        
        if ($number == 0) return 'Zero';
        
        $words = '';
        
        if (($number / 10000000) >= 1) {
            $words .= $this->numberToWords((int)($number / 10000000)) . ' Crore ';
            $number %= 10000000;
        }
        
        if (($number / 100000) >= 1) {
            $words .= $this->numberToWords((int)($number / 100000)) . ' Lakh ';
            $number %= 100000;
        }
        
        if (($number / 1000) >= 1) {
            $words .= $this->numberToWords((int)($number / 1000)) . ' Thousand ';
            $number %= 1000;
        }
        
        if (($number / 100) >= 1) {
            $words .= $this->numberToWords((int)($number / 100)) . ' Hundred ';
            $number %= 100;
        }
        
        if ($number > 0) {
            if ($words != '') $words .= 'and ';
            
            if ($number < 20) {
                $words .= $ones[$number];
            } else {
                $words .= $tens[(int)($number / 10)];
                if ($number % 10 > 0) {
                    $words .= ' ' . $ones[$number % 10];
                }
            }
        }
        
        return trim($words);
    }
}