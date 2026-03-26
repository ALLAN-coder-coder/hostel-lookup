<?php
/**
 * Hostel Data Handler
 * Reads and filters hostel data from JSON file
 */

class Hostel {
    private $dataFile;
    private $hostels = [];

    public function __construct($dataFile = 'data/hostels.json') {
        $this->dataFile = $dataFile;
        $this->loadData();
    }

    /**
     * Load hostel data from JSON file
     */
    private function loadData() {
        $filePath = __DIR__ . '/../' . $this->dataFile;
        
        if (file_exists($filePath)) {
            $json = file_get_contents($filePath);
            $this->hostels = json_decode($json, true) ?? [];
        }
    }

    /**
     * Get all hostels
     */
    public function getAll() {
        return $this->hostels;
    }

    /**
     * Get hostel by ID
     */
    public function getById($id) {
        foreach ($this->hostels as $hostel) {
            if ($hostel['id'] == $id) {
                return $hostel;
            }
        }
        return null;
    }

    /**
     * Search and filter hostels
     */
    public function search($query = '', $filter = '') {
        $results = $this->hostels;

        // Search by name, location, or custodian
        if (!empty($query)) {
            $query = strtolower(trim($query));
            $results = array_filter($results, function($hostel) use ($query) {
                return strpos(strtolower($hostel['name']), $query) !== false ||
                       strpos(strtolower($hostel['location']), $query) !== false ||
                       strpos(strtolower($hostel['custodian']), $query) !== false;
            });
        }

        // Apply filters
        switch ($filter) {
            case 'vacant':
                // Only show hostels with vacant rooms
                $results = array_filter($results, function($hostel) {
                    return $hostel['vacant'] > 0;
                });
                break;
            case 'distance':
                // Sort by distance (closest first)
                usort($results, function($a, $b) {
                    return $a['distance'] - $b['distance'];
                });
                break;
            case 'nearby':
                // Filter hostels within 1km
                $results = array_filter($results, function($hostel) {
                    return $hostel['distance'] <= 1.0;
                });
                break;
            case 'cheap':
                // Sort by price (cheapest first)
                usort($results, function($a, $b) {
                    return $a['price_min'] - $b['price_min'];
                });
                break;
            case 'self-contained':
                // Only self-contained
                $results = array_filter($results, function($hostel) {
                    return $hostel['self_contained'] == true;
                });
                break;
        }

        return array_values($results);
    }

    /**
     * Format price to UGX
     */
    public function formatPrice($amount) {
        if ($amount >= 1000000) {
            return number_format($amount / 1000000, 1) . 'M';
        } elseif ($amount >= 1000) {
            return number_format($amount / 1000) . 'K';
        }
        return number_format($amount);
    }

    /**
     * Get vacancy status text
     */
    public function getVacancyText($vacant) {
        if ($vacant == 0) {
            return 'fully booked';
        } elseif ($vacant == 1) {
            return '1 vacant';
        } else {
            return $vacant . ' vacant';
        }
    }

    /**
     * Get vacancy class
     */
    public function getVacancyClass($vacant) {
        return $vacant > 0 ? 'avail' : 'avail full';
    }

    /**
     * Get self-contained text
     */
    public function getSelfContainedText($selfContained) {
        return $selfContained ? 'self-contained' : 'Not self-contained';
    }
}

