<?php
/**
 * Customer Model Class
 */

require_once __DIR__ . '/Database.php';

class Customer {
    private $db;
    private $table = 'customers';
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Search customers by keyword
     */
    public function search($keyword, $limit = 10) {
        $searchTerm = '%' . $keyword . '%';
        
        $sql = "SELECT id, customer_code, company_name, contact_person, phone, email, address 
                FROM {$this->table} 
                WHERE company_name LIKE ? 
                   OR contact_person LIKE ? 
                   OR phone LIKE ? 
                   OR customer_code LIKE ? 
                ORDER BY company_name ASC 
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(1, $searchTerm, PDO::PARAM_STR);
        $stmt->bindValue(2, $searchTerm, PDO::PARAM_STR);
        $stmt->bindValue(3, $searchTerm, PDO::PARAM_STR);
        $stmt->bindValue(4, $searchTerm, PDO::PARAM_STR);
        $stmt->bindValue(5, (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get customer by ID
     */
    public function getById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Get all customers
     */
    public function getAll($page = 1, $perPage = ITEMS_PER_PAGE) {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT * FROM {$this->table} ORDER BY company_name ASC LIMIT ? OFFSET ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(1, (int)$perPage, PDO::PARAM_INT);
        $stmt->bindValue(2, (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Create new customer
     */
    public function create($data) {
        // Generate customer code
        $customerCode = $this->generateCustomerCode();
        
        $sql = "INSERT INTO {$this->table} 
                (customer_code, company_name, contact_person, phone, email, address) 
                VALUES 
                (?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $customerCode,
            $this->sanitize($data['company_name']),
            $this->sanitize($data['contact_person'] ?? ''),
            $this->sanitize($data['phone'] ?? ''),
            $this->sanitize($data['email'] ?? ''),
            $this->sanitize($data['address'] ?? '')
        ]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Update customer
     */
    public function update($id, $data) {
        $sql = "UPDATE {$this->table} SET 
                company_name = ?,
                contact_person = ?,
                phone = ?,
                email = ?,
                address = ?
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $this->sanitize($data['company_name']),
            $this->sanitize($data['contact_person'] ?? ''),
            $this->sanitize($data['phone'] ?? ''),
            $this->sanitize($data['email'] ?? ''),
            $this->sanitize($data['address'] ?? ''),
            $id
        ]);
    }
    
    /**
     * Delete customer
     */
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    /**
     * Generate unique customer code
     */
    private function generateCustomerCode() {
        $sql = "SELECT MAX(CAST(SUBSTRING(customer_code, 6) AS UNSIGNED)) as max_num 
                FROM {$this->table} WHERE customer_code LIKE 'CUST-%'";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        
        $nextNum = ($result['max_num'] ?? 0) + 1;
        return 'CUST-' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
    }
    
    /**
     * Count total customers
     */
    public function count() {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return $result['total'];
    }
    
    /**
     * Sanitize input
     */
    private function sanitize($input) {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}