<?php
require_once __DIR__ . '/../config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? APP_NAME ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="<?= APP_URL ?>assets/css/style.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #f5f7fa;
        }
        .dropdown-menu.show {
            display: block;
        }
        .dropdown-item {
            cursor: pointer;
            padding: 10px 15px;
        }
        .dropdown-item:hover {
            background-color: #e9ecef;
        }
        .product-dropdown.show,
        #customerDropdown.show {
            display: block;
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            z-index: 1050;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 0 0 6px 6px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="<?= APP_URL ?>">
                <i class="bi bi-file-earmark-text me-2"></i>DOT INK
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= APP_URL ?>"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= APP_URL ?>pages/quotations/"><i class="bi bi-file-text me-1"></i>Quotations</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= APP_URL ?>pages/quotations/create.php"><i class="bi bi-plus-circle me-1"></i>New Quotation</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center text-white">
                    <small><i class="bi bi-calendar me-1"></i><?= date('F d, Y') ?></small>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <main class="main-content">