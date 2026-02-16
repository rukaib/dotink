<?php
/**
 * Product Model Class
 */

require_once __DIR__ . '/Database.php';

class Product {
    private $db;
    private $table = 'products';
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Search products by keyword (ID or name)
     */
    public function search($keyword, $limit = 10) {
        $searchTerm = '%' . $keyword . '%';
        
        $sql = "SELECT id, product_code, product_name, description, 
                       default_price, default_warranty_value, default_warranty_type 
                FROM {$this->table} 
                WHERE is_active = 1 
                  AND (product_code LIKE ? 
                       OR product_name LIKE ? 
                       OR description LIKE ?)
                ORDER BY product_name ASC 
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(1, $searchTerm, PDO::PARAM_STR);
        $stmt->bindValue(2, $searchTerm, PDO::PARAM_STR);
        $stmt->bindValue(3, $searchTerm, PDO::PARAM_STR);
        $stmt->bindValue(4, (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get product by ID
     */
    public function getById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Get all products
     */
    public function getAll($page = 1, $perPage = ITEMS_PER_PAGE) {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY product_name ASC LIMIT ? OFFSET ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(1, (int)$perPage, PDO::PARAM_INT);
        $stmt->bindValue(2, (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Create new product
     */
    public function create($data) {
        // Generate product code
        $productCode = $this->generateProductCode();
        
        $sql = "INSERT INTO {$this->table} 
                (product_code, product_name, description, default_price, 
                 default_warranty_value, default_warranty_type) 
                VALUES 
                (?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $productCode,
            $this->sanitize($data['product_name']),
            $this->sanitize($data['description'] ?? ''),
            (float)($data['default_price'] ?? 0),
            $data['default_warranty_value'] ?? null,
            $data['default_warranty_type'] ?? null
        ]);
        
        $id = $this->db->lastInsertId();
        
        return [
            'id' => $id,
            'product_code' => $productCode
        ];
    }
    
    /**
     * Update product
     */
    public function update($id, $data) {
        $sql = "UPDATE {$this->table} SET 
                product_name = ?,
                description = ?,
                default_price = ?,
                default_warranty_value = ?,
                default_warranty_type = ?
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $this->sanitize($data['product_name']),
            $this->sanitize($data['description'] ?? ''),
            (float)($data['default_price'] ?? 0),
            $data['default_warranty_value'] ?? null,
            $data['default_warranty_type'] ?? null,
            $id
        ]);
    }
    
    /**
     * Delete product (soft delete)
     */
    public function delete($id) {
        $sql = "UPDATE {$this->table} SET is_active = 0 WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    /**
     * Generate unique product code
     */
    private function generateProductCode() {
        $sql = "SELECT MAX(CAST(SUBSTRING(product_code, 5) AS UNSIGNED)) as max_num 
                FROM {$this->table} WHERE product_code LIKE 'PRD-%'";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        
        $nextNum = ($result['max_num'] ?? 0) + 1;
        return 'PRD-' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
    }
    
    /**
     * Count total active products
     */
    public function count() {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE is_active = 1";
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