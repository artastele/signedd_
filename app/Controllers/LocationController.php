<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 1
// Last modified: 2026-05-01
// Part of: SPED LMS — Location Controller (Philippine Locations)

class LocationController {
    private $locations;

    public function __construct() {
        // Load Philippine location data
        $jsonPath = __DIR__ . '/../../public/data/philippines.json';
        if (file_exists($jsonPath)) {
            $this->locations = json_decode(file_get_contents($jsonPath), true);
        } else {
            $this->locations = $this->getDefaultLocations();
        }
    }

    /**
     * Get all provinces
     */
    public function getProvinces() {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'provinces' => array_keys($this->locations)
        ]);
    }

    /**
     * Get cities by province
     */
    public function getCities($province = null) {
        header('Content-Type: application/json');
        
        // Get province from parameter or URL
        if (!$province && isset($_GET['province'])) {
            $province = $_GET['province'];
        }
        
        $province = urldecode($province);
        
        if (isset($this->locations[$province])) {
            echo json_encode([
                'success' => true,
                'cities' => array_keys($this->locations[$province])
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Province not found',
                'province' => $province,
                'available' => array_keys($this->locations)
            ]);
        }
    }

    /**
     * Get barangays by city
     */
    public function getBarangays($province = null, $city = null) {
        header('Content-Type: application/json');
        
        // Get parameters from URL if not provided
        if (!$province && isset($_GET['province'])) {
            $province = $_GET['province'];
        }
        if (!$city && isset($_GET['city'])) {
            $city = $_GET['city'];
        }
        
        $province = urldecode($province);
        $city = urldecode($city);
        
        if (isset($this->locations[$province][$city])) {
            echo json_encode([
                'success' => true,
                'barangays' => $this->locations[$province][$city]
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'City not found',
                'province' => $province,
                'city' => $city
            ]);
        }
    }

    /**
     * Default location data (sample - can be expanded)
     */
    private function getDefaultLocations() {
        return [
            'Cebu' => [
                'Cebu City' => [
                    'Apas', 'Banilad', 'Basak', 'Busay', 'Guadalupe', 'Lahug', 
                    'Mabolo', 'Talamban', 'Tisa', 'Zapatera'
                ],
                'Mandaue City' => [
                    'Alang-alang', 'Bakilid', 'Banilad', 'Basak', 'Cabancalan',
                    'Centro', 'Guizo', 'Ibabao-Estancia', 'Looc', 'Mantuyong'
                ],
                'Lapu-Lapu City' => [
                    'Agus', 'Babag', 'Bankal', 'Basak', 'Buaya', 'Canjulao',
                    'Caubian', 'Gun-ob', 'Ibo', 'Looc', 'Mactan', 'Maribago'
                ],
                'Talisay City' => [
                    'Biasong', 'Bulacao', 'Cadulawan', 'Camp IV', 'Cansojong',
                    'Dumlog', 'Jaclupan', 'Lagtang', 'Lawaan', 'Linao'
                ]
            ],
            'Metro Manila' => [
                'Manila' => [
                    'Ermita', 'Intramuros', 'Malate', 'Paco', 'Pandacan',
                    'Port Area', 'Quiapo', 'Sampaloc', 'San Miguel', 'Tondo'
                ],
                'Quezon City' => [
                    'Bagong Pag-asa', 'Batasan Hills', 'Commonwealth', 'Cubao',
                    'Diliman', 'Fairview', 'Kamuning', 'Novaliches', 'Project 4', 'Tandang Sora'
                ],
                'Makati City' => [
                    'Bel-Air', 'Carmona', 'Dasmariñas', 'Forbes Park', 'Guadalupe Nuevo',
                    'Magallanes', 'Poblacion', 'Rockwell', 'San Lorenzo', 'Urdaneta'
                ],
                'Pasig City' => [
                    'Bagong Ilog', 'Kapitolyo', 'Manggahan', 'Maybunga', 'Oranbo',
                    'Pinagbuhatan', 'Rosario', 'San Joaquin', 'Santolan', 'Ugong'
                ]
            ],
            'Davao del Sur' => [
                'Davao City' => [
                    'Agdao', 'Buhangin', 'Bunawan', 'Calinan', 'Matina',
                    'Paquibato', 'Poblacion', 'Talomo', 'Toril', 'Tugbok'
                ],
                'Digos City' => [
                    'Aplaya', 'Balabag', 'Cogon', 'Dawis', 'Goma',
                    'Kiagot', 'Lungag', 'Rizal', 'San Jose', 'Zone I'
                ]
            ]
        ];
    }
}
