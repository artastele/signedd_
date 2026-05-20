<?php
// Direct cities endpoint (bypass routing)
require_once __DIR__ . '/../app/Controllers/LocationController.php';

$province = $_GET['province'] ?? '';
$controller = new LocationController();
$controller->getCities($province);
