<?php
/**
 * Database Configuration
 * DOT INK Quotation Management System
 */

define('DB_HOST', 'localhost');
define('DB_PORT', '3307');
define('DB_NAME', 'dotink');
define('DB_USER', 'root');
define('DB_PASS', '31052006');
define('DB_CHARSET', 'utf8mb4');

// Application Settings
define('APP_NAME', 'DOT INK Quotation System');
define('APP_VERSION', '1.0.0');

// Get the base URL dynamically
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptPath = dirname($_SERVER['SCRIPT_NAME']);
$basePath = '';

// Find the base path to dotink folder
if (strpos($scriptPath, '/dotink') !== false) {
    $basePath = substr($scriptPath, 0, strpos($scriptPath, '/dotink') + 7);
} else {
    $basePath = '/dotink';
}

define('APP_URL', $basePath . '/');
define('BASE_URL', $protocol . '://' . $host . $basePath . '/');

// VAT Settings
define('DEFAULT_VAT_RATE', 18);

// Pagination
define('ITEMS_PER_PAGE', 15);

// Date Format
define('DATE_FORMAT', 'Y-m-d');
define('DISPLAY_DATE_FORMAT', 'F d, Y');

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Timezone
date_default_timezone_set('Asia/Colombo');