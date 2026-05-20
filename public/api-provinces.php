<?php
// Direct provinces endpoint (bypass routing)
require_once __DIR__ . '/../app/Controllers/LocationController.php';

$controller = new LocationController();
$controller->getProvinces();
