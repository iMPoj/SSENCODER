<?php
// FILE: parse_order_text.php
header('Content-Type: application/json');

// --- Helper Functions ---
function find_value($pattern, $text) {
    return preg_match($pattern, $text, $matches) ? trim($matches[1]) : null;
}

function parse_nutri_items($text) {
    $items = [];
    $lines = preg_split('/\r\n|\r|\n/', $text);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        if (preg_match('/^(\d+)\s*VENDOR\s+ITEM\s+CODE\s*:\s*(\d+)\s*BARCODE\s*:\s*(.*?)\s+00000000\s+PC\s+(\d+)/i', $line, $matches)) {
            $items[] = [
                'vendorCode' => $matches[1],      
                'barcode'    => $matches[2], 
                'description'=> trim($matches[3]),
                'quantity'   => (int)$matches[4]
            ];
        }
    }
    return $items;
}

function parse_health_hygiene_items($text) {
    // (Keeping your original hygiene logic just in case you paste other formats)
    $items = [];
    $lines = array_map('trim', explode("\n", $text));
    foreach ($lines as $i => $line) {
        if (preg_match('/^(\d{5,})\s+.*?(PC|PK|BX|SET|BOXOF\d+|PKOF\d+)\s+([0-9,]+)\s+0\s+0/i', $line, $data_matches)) {
            $quantity = (int)str_replace(',', '', $data_matches[3]);
            $vendorCode = $data_matches[1];
            $description = ''; $barcode = null;
            $lookback_limit = min(5, $i);
            for ($j = 1; $j <= $lookback_limit; $j++) {
                if (!isset($lines[$i - $j])) continue;
                $prev_line = $lines[$i - $j];
                if (stripos($prev_line, 'ITEM NO DESCRIPTION COST') !== false) break;
                if (preg_match('/BARCODE\s*:\s*(.*)/i', $prev_line, $bc_match)) {
                    $barcode_line = $bc_match[1];
                    if (preg_match('/^(\d+)\s*(.*)/', $barcode_line, $bc_parts)) {
                        $barcode = $bc_parts[1]; $description = $bc_parts[2] . ' ' . $description;
                    } else {
                        $description = $barcode_line . ' ' . $description;
                    }
                } elseif (preg_match('/VENDOR\s+ITEM\s+CODE\s*:\s*(\d*)\s*(.*)/i', $prev_line, $vic_match)) {
                    $description = ($vic_match[2] ?? '') . ' ' . $description;
                } elseif (!preg_match('/^\d/', $prev_line) && !preg_match('/(COST|AMOUNT|DISC|VAT)/i', $prev_line)) {
                    $description = $prev_line . ' ' . $description;
                }
            }
            $description = trim(preg_replace('/\s+/', ' ', $description));
            if ($quantity > 0 && !empty($description)) {
                $items[] = ['vendorCode' => $vendorCode, 'barcode' => $barcode, 'description' => $description, 'quantity' => $quantity];
            }
        }
    }
    return $items;
}

// --- Main Logic ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['text'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

$raw_text = $_POST['text'];

// Split the text by the separator (at least 10 equal signs)
$order_blocks = preg_split('/={10,}/', $raw_text);
$parsed_orders = [];

foreach ($order_blocks as $block) {
    $text = trim($block);
    if (empty($text)) continue;

    $supplier = find_value('/Supplier\s*Details\s*:\s*([^\n\r]+)/i', $text);
    $items = [];

    if (stripos($supplier, 'O.T.C') !== false || stripos($supplier, 'HEALTH') !== false || stripos($supplier, 'HYGIENE') !== false) {
        $items = parse_health_hygiene_items($text);
    } else {
        $items = parse_nutri_items($text);
    }

if (!empty($items)) {
        $shipTo = find_value('/Delivery\/Ship\s*To\s*([^\n\r]+)/i', $text);
        
        // --- SMART LOCATION DETECTION ---
        $detectedLocation = 'Davao'; // Default to Davao
        if ($shipTo) {
            // Add any cities or keywords here that belong to Gensan
            $gensan_keywords = ['05517', '05519', 'GENSAN', 'GENERAL SANTOS', 'POLOMOLOK', 'KORONADAL', 'TACURONG', 'SOUTH COTABATO', 'SARANGANI', 'SULTAN KUDARAT'];
            foreach ($gensan_keywords as $keyword) {
                if (stripos($shipTo, $keyword) !== false) {
                    $detectedLocation = 'Gensan';
                    break;
                }
            }
        }

        $parsed_orders[] = [
            'poNumber' => find_value('/PO\s*No\.?\s*:\s*([A-Z0-9\-]+)/i', $text),
            'customerName' => find_value('/Customer\s*:\s*([^\n\r]+)/i', $text) ?: find_value('/(ROJON\s+PHARMACY\s+CORPORATION|ROSE\s+PHARMACY\s+INCORPORATED|FELCRIS\s+GROUP\s+OF\s+COMPANIES)/i', $text),
            'shipTo' => $shipTo,
            'location' => $detectedLocation,
            'items' => $items
        ];
    }
}

if (empty($parsed_orders)) {
    echo json_encode(['success' => false, 'message' => 'No valid items were parsed. Please check the item format.']);
    exit;
}

echo json_encode([
    'success' => true,
    'message' => 'Parsed ' . count($parsed_orders) . ' orders successfully.',
    'data' => $parsed_orders // Returning an array of orders now!
]);
?>