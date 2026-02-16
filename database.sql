-- ============================================
-- DOTINK QUOTATION MANAGEMENT SYSTEM
-- Database: dotink
-- Port: 3307
-- ============================================

CREATE DATABASE IF NOT EXISTS `dotink` 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `dotink`;

-- ============================================
-- CUSTOMERS TABLE
-- ============================================
DROP TABLE IF EXISTS `customers`;
CREATE TABLE `customers` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `customer_code` VARCHAR(50) NOT NULL,
    `company_name` VARCHAR(255) NOT NULL,
    `contact_person` VARCHAR(150) DEFAULT NULL,
    `phone` VARCHAR(50) DEFAULT NULL,
    `email` VARCHAR(150) DEFAULT NULL,
    `address` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_customer_code` (`customer_code`),
    KEY `idx_company_name` (`company_name`),
    KEY `idx_phone` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PRODUCTS TABLE
-- ============================================
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `product_code` VARCHAR(50) NOT NULL,
    `product_name` VARCHAR(255) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `image_path` VARCHAR(500) DEFAULT NULL,
    `default_price` DECIMAL(15,2) DEFAULT 0.00,
    `default_warranty_value` INT(11) DEFAULT NULL,
    `default_warranty_type` ENUM('Days','Weeks','Months','Years','Lifetime') DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_product_code` (`product_code`),
    KEY `idx_product_name` (`product_name`),
    KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- QUOTATIONS TABLE
-- ============================================
DROP TABLE IF EXISTS `quotations`;
CREATE TABLE `quotations` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `quotation_no` VARCHAR(50) NOT NULL,
    `customer_id` INT(11) DEFAULT NULL,
    `customer_name` VARCHAR(255) DEFAULT NULL,
    `customer_contact` VARCHAR(150) DEFAULT NULL,
    `customer_phone` VARCHAR(50) DEFAULT NULL,
    `customer_email` VARCHAR(150) DEFAULT NULL,
    `customer_address` TEXT DEFAULT NULL,
    `quotation_date` DATE NOT NULL,
    `subtotal` DECIMAL(15,2) DEFAULT 0.00,
    `discount` DECIMAL(15,2) DEFAULT 0.00,
    `discount_type` ENUM('amount','percentage') DEFAULT 'amount',
    `vat_enabled` TINYINT(1) DEFAULT 0,
    `vat_percentage` DECIMAL(5,2) DEFAULT 18.00,
    `tax_amount` DECIMAL(15,2) DEFAULT 0.00,
    `grand_total` DECIMAL(15,2) DEFAULT 0.00,
    `delivery_terms` VARCHAR(255) DEFAULT NULL,
    `payment_terms` VARCHAR(255) DEFAULT NULL,
    `validity` VARCHAR(100) DEFAULT NULL,
    `stock_availability` VARCHAR(100) DEFAULT NULL,
    `notes` TEXT DEFAULT NULL,
    `prepared_by` VARCHAR(150) DEFAULT NULL,
    `status` ENUM('Draft','Final','Approved','Rejected','Expired') DEFAULT 'Draft',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_quotation_no` (`quotation_no`),
    KEY `idx_customer_id` (`customer_id`),
    KEY `idx_quotation_date` (`quotation_date`),
    KEY `idx_status` (`status`),
    CONSTRAINT `fk_quotations_customer` FOREIGN KEY (`customer_id`) 
        REFERENCES `customers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- QUOTATION ITEMS TABLE
-- ============================================
DROP TABLE IF EXISTS `quotation_items`;
CREATE TABLE `quotation_items` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `quotation_id` INT(11) NOT NULL,
    `product_id` INT(11) DEFAULT NULL,
    `product_code` VARCHAR(50) DEFAULT NULL,
    `product_name` VARCHAR(255) NOT NULL,
    `product_description` TEXT DEFAULT NULL,
    `product_image` VARCHAR(500) DEFAULT NULL,
    `warranty_value` INT(11) DEFAULT NULL,
    `warranty_type` ENUM('Days','Weeks','Months','Years','Lifetime') DEFAULT NULL,
    `quantity` INT(11) NOT NULL DEFAULT 1,
    `unit_price` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `vat_amount` DECIMAL(15,2) DEFAULT 0.00,
    `line_total` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `item_order` INT(11) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_quotation_id` (`quotation_id`),
    KEY `idx_product_id` (`product_id`),
    KEY `idx_item_order` (`item_order`),
    CONSTRAINT `fk_items_quotation` FOREIGN KEY (`quotation_id`) 
        REFERENCES `quotations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_items_product` FOREIGN KEY (`product_id`) 
        REFERENCES `products` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- COMPANY SETTINGS TABLE
-- ============================================
DROP TABLE IF EXISTS `company_settings`;
CREATE TABLE `company_settings` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `setting_key` VARCHAR(100) NOT NULL,
    `setting_value` TEXT DEFAULT NULL,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- INSERT DEFAULT COMPANY SETTINGS
-- ============================================
INSERT INTO `company_settings` (`setting_key`, `setting_value`) VALUES
('company_name', 'DOT INK (PVT) LTD'),
('company_address', '218/13A, Ranimadama, Enderamulla, Wattala.'),
('company_hotline', '075 966 166 8'),
('company_general', '011 368 780'),
('company_email', 'info@dotink.lk'),
('company_website', 'www.dotink.lk'),
('company_vat', '178769677-7000'),
('company_logo', 'assets/images/logo.png'),
('default_vat_percentage', '18'),
('default_validity', '14 Days'),
('default_delivery', 'Within 2 Days after Confirmation'),
('default_payment', '30 Days Credit / COD'),
('default_stock', 'Available'),
('prepared_by_name', 'M.ASHAN'),
('prepared_by_title', 'Executive - Operation Management'),
('prepared_by_mobile', '075 966 166 8');

-- ============================================
-- INSERT SAMPLE CUSTOMERS
-- ============================================
INSERT INTO `customers` (`customer_code`, `company_name`, `contact_person`, `phone`, `email`, `address`) VALUES
('CUST-001', 'XYZ CORPORATION', 'Mr. John Silva', '0771234567', 'john@xyzcorp.lk', '123, Main Street, Colombo 01.'),
('CUST-002', 'ABC TRADING COMPANY', 'Mrs. Sarah Fernando', '0779876543', 'sarah@abctrading.lk', '45, Galle Road, Colombo 03.'),
('CUST-003', 'MEGA ENTERPRISES', 'Mr. Ranil Perera', '0765432189', 'ranil@megaent.lk', '78, Kandy Road, Kadawatha.'),
('CUST-004', 'GLOBAL SOLUTIONS LTD', 'Ms. Nisha Kumar', '0712345678', 'nisha@globalsol.lk', '90, Duplication Road, Colombo 04.'),
('CUST-005', 'TECH INNOVATIONS', 'Mr. Amal Jayawardena', '0723456789', 'amal@techinno.lk', '56, High Level Road, Nugegoda.');

-- ============================================
-- INSERT SAMPLE PRODUCTS
-- ============================================
INSERT INTO `products` (`product_code`, `product_name`, `description`, `default_price`, `default_warranty_value`, `default_warranty_type`, `image_path`) VALUES
('PRD-001', 'HP LaserJet Pro M404dn', 'Business Laser Printer\nPrint Speed: 40ppm\nDuplex Printing\nNetwork Ready', 125000.00, 12, 'Months', NULL),
('PRD-002', 'Canon PIXMA G2020', 'All-in-One Ink Tank Printer\nPrint, Scan, Copy\nHigh Volume Printing\nUSB Connectivity', 45000.00, 24, 'Months', NULL),
('PRD-003', 'Epson L3250 Wi-Fi', 'EcoTank Multi-Function Printer\nWireless Printing\nPrint, Scan, Copy\nMobile Printing Support', 52000.00, 12, 'Months', NULL),
('PRD-004', 'HP 107a Laser Printer', 'Compact Mono Laser\nPrint Speed: 20ppm\nUSB 2.0\nEnergy Efficient', 35000.00, 12, 'Months', NULL),
('PRD-005', 'Brother TN-2365 Toner', 'Original Brother Toner\nPage Yield: 2,600 pages\nHigh Quality Output', 7500.00, NULL, NULL, NULL),
('PRD-006', 'HP 83A Toner Cartridge', 'Original HP Toner\nPage Yield: 1,500 pages\nCompatible with HP LaserJet Pro', 8500.00, NULL, NULL, NULL),
('PRD-007', 'Canon PG-745 Black Ink', 'Original Canon Ink\nPage Yield: 180 pages\nFine Cartridge', 2800.00, NULL, NULL, NULL),
('PRD-008', 'Epson 003 Ink Bottle Set', '4 Color Ink Set (CMYK)\n65ml Each Bottle\nHigh Volume Yield', 4200.00, NULL, NULL, NULL),
('PRD-009', 'A4 Paper Ream (500 Sheets)', '80GSM Premium Quality\n500 Sheets per Ream\nFor All Printers', 1250.00, NULL, NULL, NULL),
('PRD-010', 'USB Printer Cable 3M', 'USB 2.0 A to B Cable\n3 Meter Length\nHigh Speed Transfer', 650.00, 6, 'Months', NULL);

-- ============================================
-- INSERT SAMPLE QUOTATION
-- ============================================
INSERT INTO `quotations` (
    `quotation_no`, `customer_id`, `customer_name`, `customer_contact`, 
    `customer_phone`, `customer_email`, `customer_address`, `quotation_date`,
    `subtotal`, `discount`, `vat_enabled`, `tax_amount`, `grand_total`,
    `delivery_terms`, `payment_terms`, `validity`, `stock_availability`,
    `notes`, `prepared_by`, `status`
) VALUES (
    'QT-2025-0001', 1, 'XYZ CORPORATION', 'Mr. John Silva',
    '0771234567', 'john@xyzcorp.lk', '123, Main Street, Colombo 01.', CURDATE(),
    170000.00, 0.00, 0, 0.00, 170000.00,
    'Within 2 Days after Confirmation', '30 Days Credit / COD', '14 Days', 'Available',
    'Price is negotiable after discussions.\nOn-site installation included.',
    'M.ASHAN', 'Final'
);

INSERT INTO `quotation_items` (
    `quotation_id`, `product_id`, `product_code`, `product_name`, `product_description`,
    `warranty_value`, `warranty_type`, `quantity`, `unit_price`, `line_total`, `item_order`
) VALUES
(1, 1, 'PRD-001', 'HP LaserJet Pro M404dn', 'Business Laser Printer\nPrint Speed: 40ppm\nDuplex Printing\nNetwork Ready', 12, 'Months', 1, 125000.00, 125000.00, 1),
(1, 2, 'PRD-002', 'Canon PIXMA G2020', 'All-in-One Ink Tank Printer\nPrint, Scan, Copy\nHigh Volume Printing\nUSB Connectivity', 24, 'Months', 1, 45000.00, 45000.00, 2);

-- ============================================
-- CREATE AUTO-INCREMENT PROCEDURE FOR QUOTATION NO
-- ============================================
DELIMITER //
CREATE PROCEDURE `GetNextQuotationNo`()
BEGIN
    DECLARE next_no INT;
    DECLARE current_year INT;
    DECLARE prefix VARCHAR(10);
    
    SET current_year = YEAR(CURDATE());
    SET prefix = CONCAT('QT-', current_year, '-');
    
    SELECT COALESCE(MAX(CAST(SUBSTRING(quotation_no, 9) AS UNSIGNED)), 0) + 1
    INTO next_no
    FROM quotations
    WHERE quotation_no LIKE CONCAT(prefix, '%');
    
    SELECT CONCAT(prefix, LPAD(next_no, 4, '0')) AS next_quotation_no;
END //
DELIMITER ;

-- ============================================
-- INDEXES FOR BETTER PERFORMANCE
-- ============================================
CREATE INDEX idx_customers_search ON customers(company_name, contact_person, phone);
CREATE INDEX idx_products_search ON products(product_code, product_name);
CREATE INDEX idx_quotations_search ON quotations(quotation_no, customer_name, quotation_date);