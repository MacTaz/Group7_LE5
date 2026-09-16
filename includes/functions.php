<?php
// ============================================================
// Programmer Names: Agatha Fei Pelayo, Ma Irene Adel, Mari Alessandrae Rosero, Mico Angelo Tazarte
// VALIDATION + SIMPLE JSON "DATABASE" HELPERS
// ============================================================

// Always clean up text before displaying/storing it
function sanitize_string($str) {
    return htmlspecialchars(trim($str ?? ''), ENT_QUOTES, 'UTF-8');
}

// ----- Required field -----
function validate_required($value) {
    return trim((string)$value) !== '';
}

// ----- Length validation -----
function validate_min_length($value, $min) {
    return strlen(trim((string)$value)) >= $min;
}

// ----- Email validation -----
function validate_email_format($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// ----- Number validation (e.g. "participants must be between 1 and 20") -----
function validate_number_range($value, $min, $max) {
    return is_numeric($value) && $value >= $min && $value <= $max;
}

// ----- Phone number validation: digits only, sensible length -----
function validate_phone($value) {
    return preg_match('/^[0-9]{7,15}$/', trim($value)) === 1;
}

// ----- Date validation: for things that must NOT be in the past -----
// (booking date, event date, appointment date, etc.)
function validate_date_not_past($date) {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) return false;
    return $date >= date('Y-m-d'); // today or later = OK
}

// ----- Date validation: for things that must NOT be in the future -----
// (birthdate, date already completed, etc. — kept here for reuse)
function validate_date_not_future($date) {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) return false;
    return $date <= date('Y-m-d'); // today or earlier = OK
}

// ----- Simple XML "database" read/write -----
function read_xml($file) {
    if (!file_exists($file)) return [];
    libxml_use_internal_errors(true);
    $xml = simplexml_load_file($file, 'SimpleXMLElement', LIBXML_NOCDATA);
    if ($xml === false) return [];

    $list = [];
    foreach ($xml->children() as $child) {
        $item = [];
        foreach ($child->children() as $key => $val) {
            $item[$key] = (string)$val;
        }
        if (empty($item)) {
            $item = (array)$child;
        }
        $list[] = $item;
    }
    return $list;
}

function write_xml($file, $data, $rootName = null, $itemName = null) {
    if ($rootName === null) {
        $base = basename($file, '.xml');
        if ($base === 'facilities') {
            $rootName = 'facilities';
            $itemName = 'facility';
        } elseif ($base === 'bookings') {
            $rootName = 'bookings';
            $itemName = 'booking';
        } else {
            $rootName = 'items';
            $itemName = 'item';
        }
    }

    $xml = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"UTF-8\"?><{$rootName}/>");
    foreach ($data as $item) {
        $node = $xml->addChild($itemName);
        foreach ($item as $key => $val) {
            $node->addChild($key, htmlspecialchars((string)$val, ENT_XML1, 'UTF-8'));
        }
    }

    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;
    $dom->loadXML($xml->asXML());
    $dom->save($file);
}

// ----- Backward compatibility JSON helpers -----
function read_json($file) {
    if (!file_exists($file)) return [];
    $content = file_get_contents($file);
    $data = json_decode($content, true);
    return is_array($data) ? $data : [];
}

function write_json($file, $data) {
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT), LOCK_EX);
}

