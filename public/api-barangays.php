<?php
// Direct barangays endpoint (bypass routing)
require_once __DIR__ . '/../app/Controllers/LocationController.php';

$province = $_GET['province'] ?? '';
$city = $_GET['city'] ?? '';
$controller = new LocationController();
$controller->getBarangays($province, $city);
