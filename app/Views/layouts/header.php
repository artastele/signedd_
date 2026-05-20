<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'SPED LMS'; ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo defined('BASE_PATH') ? BASE_PATH : ''; ?>/images/favicon.ico">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="<?php echo defined('BASE_PATH') ? BASE_PATH : ''; ?>/css/custom.css" rel="stylesheet">

    <!-- Print CSS (Process 5 — IEP print layout) -->
    <link href="<?php echo defined('BASE_PATH') ? BASE_PATH : ''; ?>/css/print.css" rel="stylesheet" media="print">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Tabler Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons@latest/tabler-icons.min.css">
</head>
<body class="bg-light-surface">

<!-- Sidebar Overlay (Mobile) -->
<div class="sidebar-overlay"></div>
