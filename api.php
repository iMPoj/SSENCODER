<?php
date_default_timezone_set('Asia/Manila');

// --- ENVIRONMENT DETECTION ---
$is_local = (
    $_SERVER['SERVER_NAME'] === 'localhost' ||
    $_SERVER['SERVER_NAME'] === '127.0.0.1' ||
    strpos($_SERVER['SERVER_NAME'], '192.168.') === 0
);

// Only show errors in development — never in production
if ($is_local) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}

ini_set('max_execution_time', 120);

// --- ROBUST ERROR HANDLER ---
set_error_handler(function($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) { return; }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

// MUST match index.php to prevent the server from destroying the session early during API calls
ini_set('session.gc_maxlifetime', 86400);
session_set_cookie_params(0);

session_start();

// Enable GZIP compression to drastically reduce JSON payload size for slower devices/networks
if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) {
    ob_start("ob_gzhandler");
} else {
    ob_start();
}

header('Content-Type: application/json');

// --- WRAP ENTIRE APPLICATION IN A TRY...CATCH BLOCK ---
try {
    require __DIR__ . '/db_connect.php';

    // Auto-upgrade for Customer LV Limit per BU
    try { $pdo->exec("CREATE TABLE IF NOT EXISTS customer_lv_limits (customer_id INT, bu VARCHAR(50), lv_limit DECIMAL(15,2), PRIMARY KEY(customer_id, bu))"); } catch (PDOException $e) {}
    // Auto-upgrade for Saved Translator Invoices
    try { $pdo->exec("CREATE TABLE IF NOT EXISTS saved_invoices (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255), invoice_data LONGTEXT, bu VARCHAR(50), delivery_date VARCHAR(50), is_received TINYINT(1) DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)"); } catch (PDOException $e) {}
    
    // ---> ADD THESE TWO NEW LINES HERE:
    try { $pdo->exec("ALTER TABLE customer_address_codes ADD COLUMN location VARCHAR(50) DEFAULT 'Davao'"); } catch (PDOException $e) {}
    try { $pdo->exec("ALTER TABLE customers ADD COLUMN location VARCHAR(50) DEFAULT 'Davao'"); } catch (PDOException $e) {}

    $action = $_POST['action'] ?? $_GET['action'] ?? '';

    $viewer_actions = [
        'get_products', 'get_customers', 'get_orders', 'get_unserved_orders', 
        'get_dashboard_data', 'get_customer_dashboard_data', 'get_fulfillable_items',
        'get_rojon_dashboard_data', 
        'get_rojon_orders',
                'get_sales_summary_data',
        'get_stock_for_product',
        'find_product_with_best_sku',
        'search_pos_by_product',
        'get_address_by_code',
        'get_address_suggestions',
        'get_product_suggestions',
        'get_fulfilled_orders', 'get_advanced_dashboard_data'
    ];

    $public_actions = ['login', 'logout'];
    $user_role = $_SESSION['role'] ?? 'viewer';

    if (!in_array($action, $public_actions) && $user_role === 'viewer') {
        if (!in_array($action, $viewer_actions)) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Login required for this action.']);
            exit;
        }
    } else if (!in_array($action, $public_actions) && (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true)) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Authentication required.']);
        exit;
    }

    $admin_only_actions = [
        'get_orders_for_export', 'add_customer', 'delete_customer', 'add_product', 
        'bulk_add_products', 'bulk_update_stock', 'delete_code', 'bulk_add_aliases', 
        'toggle_customer_priority', 'get_unlinked_skus',
        'delete_order', 'cancel_order', 'uncancel_order',
        'get_address_codes', 'add_address_code', 'update_address_code', 'delete_address_code',
        'get_monthly_targets', 'set_monthly_targets',
        'toggle_pristine_status', 'update_customer_lv', 'get_customer_lvs',
        'set_display_month',
        'get_admin_products', 'get_product_details', 'save_product_base', 
        'delete_product_full', 'save_sku', 'delete_sku_by_id', 'bulk_update_salesmen',
        'get_np_import_data', 'import_database',
        'add_user', 'update_user', 'delete_user',
        'get_saved_invoices', 'save_translator_invoice', 'rename_saved_invoice', 'delete_saved_invoice', 'toggle_invoice_received', 'bulk_update_sku_pcs', 'bulk_deduct_stock'
    ];
    
    if (in_array($action, $admin_only_actions) && (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin')) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Admin permission required.']);
        exit;
    }

    // --- Main Router ---
    switch ($action) {
        case 'login': login($pdo); break;
        case 'logout': logout(); break;
        case 'get_products': getProducts($pdo); break;
        case 'get_customers': getCustomers($pdo); break;
        case 'get_orders': getOrders($pdo); break;
        case 'get_unserved_orders': getUnservedOrders($pdo); break;
        case 'get_order_details': getOrderDetails($pdo); break;
        case 'update_order_items': updateOrderItems($pdo); break;
        case 'add_order': addOrder($pdo); break;
        case 'get_dashboard_data': getDashboardData($pdo); break;
        case 'get_customer_dashboard_data': getCustomerDashboardData($pdo); break;
        case 'update_inventory_row': updateInventoryRow($pdo); break;
        case 'get_fulfillable_items': getFulfillableItems($pdo); break;
        case 'get_draft_orders': getDraftOrders($pdo); break;
        case 'get_orders_for_export': getOrdersForExport($pdo); break;
        case 'add_customer': addCustomer($pdo); break;
        case 'delete_customer': deleteCustomer($pdo); break;
        case 'toggle_customer_priority': toggleCustomerPriority($pdo); break;
        case 'update_customer_lv': updateCustomerLv($pdo); break;
        case 'get_customer_lvs': getCustomerLvs($pdo); break;
        case 'add_product': addProduct($pdo); break;
        case 'bulk_add_products': bulkAddProducts($pdo); break;
        case 'bulk_update_stock': bulkUpdateStock($pdo); break;
        case 'update_single_inventory': updateSingleInventory($pdo); break;
        case 'bulk_add_stock': bulkAddStock($pdo); break;
        case 'bulk_add_stock_no_price': bulkAddStockNoPrice($pdo); break;
        case 'update_order_date': updateOrderDate($pdo); break;
        case 'update_po_number': updatePoNumber($pdo); break;
        case 'delete_code': deleteCode($pdo); break;
        case 'bulk_add_aliases': bulkAddAliases($pdo); break;
        case 'get_unlinked_skus': getUnlinkedSkus($pdo); break;
        case 'delete_order': deleteOrder($pdo); break;
        case 'cancel_order': cancelOrder($pdo); break;
        case 'uncancel_order': uncancelOrder($pdo); break;
        case 'get_rojon_dashboard_data': getRojonDashboardData($pdo); break;
        case 'get_rojon_orders': getRojonOrders($pdo); break;
        case 'get_fulfilled_orders': getFulfilledOrders($pdo); break;
        case 'get_advanced_dashboard_data': getAdvancedDashboardData($pdo); break;
        case 'get_sales_summary_data': getSalesSummaryData($pdo); break;
        case 'get_address_codes': getAddressCodes($pdo); break;
        case 'add_address_code': addAddressCode($pdo); break;
        case 'update_address_code': updateAddressCode($pdo); break;
        case 'delete_address_code': deleteAddressCode($pdo); break;
        case 'get_monthly_targets': getMonthlyTargets($pdo); break;
        case 'set_monthly_targets': setMonthlyTargets($pdo); break;
        case 'get_stock_for_product': get_stock_for_product($pdo); break;
        case 'toggle_pristine_status': toggle_pristine_status($pdo); break;
        case 'find_product_with_best_sku': find_product_with_best_sku($pdo); break;
        case 'search_pos_by_product': search_pos_by_product($pdo); break;
        case 'get_address_by_code': get_address_by_code($pdo); break;
        case 'get_product_suggestions': get_product_suggestions($pdo); break;
        case 'set_display_month': set_display_month($pdo); break;
        case 'get_address_suggestions': getAddressSuggestions($pdo); break;
        case 'get_admin_products': getAdminProducts($pdo); break;
        case 'get_product_details': getProductDetails($pdo); break;
        case 'save_product_base': saveProductBase($pdo); break;
        case 'delete_product_full': deleteProductFull($pdo); break;
        case 'save_sku': saveSku($pdo); break;
        case 'delete_sku_by_id': deleteSkuById($pdo); break;
        case 'bulk_update_salesmen': bulkUpdateSalesmen($pdo); break;
        case 'get_np_import_data': getNpImportData($pdo); break;
        case 'import_database': importDatabase($pdo); break;
        case 'get_unserved_items_flat': getUnservedItemsFlat($pdo); break;
        case 'get_users': getUsers($pdo); break;
        case 'add_user': addUser($pdo); break;
        case 'update_user': updateUser($pdo); break;
        case 'delete_user': deleteUser($pdo); break;
        case 'get_saved_invoices': getSavedInvoices($pdo); break;
        case 'save_translator_invoice': saveTranslatorInvoice($pdo); break;
        case 'rename_saved_invoice': renameSavedInvoice($pdo); break;
        case 'delete_saved_invoice': deleteSavedInvoice($pdo); break;
        case 'toggle_invoice_received': toggleInvoiceReceived($pdo); break;
        case 'bulk_update_sku_pcs': bulkUpdateSkuPcs($pdo); break;
        case 'bulk_deduct_stock': bulkDeductStock($pdo); break;
        default: echo json_encode(['success' => false, 'message' => 'Invalid action specified']);
    }

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine()]);
}

// --- FUNCTIONS ---

function login($pdo) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    if (empty($username) || empty($password)) { throw new Exception('Username and password are required.'); }
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true); 
        $_SESSION['user_logged_in'] = true; 
        $_SESSION['user_id'] = $user['id']; 
        $_SESSION['username'] = $user['username']; 
        $_SESSION['role'] = $user['role'];
        $_SESSION['avatar_url'] = $user['avatar_url'] ?? null;
        $_SESSION['display_name'] = $user['display_name'] ?? $user['username'];
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid username or password.']);
    }
    exit;
}

function logout() {
    $_SESSION = array();
    session_destroy();
    echo json_encode(['success' => true, 'message' => 'Logged out successfully.']);
    exit;
}

function getCustomers($pdo) {
    $stmt = $pdo->query("
        SELECT 
            c.id, 
            c.name, 
            c.is_priority, 
            c.lv_limit,
            c.default_discount,
            c.location,
            (SELECT discount_percentage FROM orders WHERE customer_id = c.id AND discount_percentage > 0 ORDER BY order_date DESC LIMIT 1) as last_discount
        FROM customers c 
        ORDER BY c.name
    ");
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $customers]);
    exit;
}

function getProducts($pdo) {
    $sql = "SELECT p.id, p.description, p.bu, p.is_promo, pc.code, pc.type, pc.pieces_per_case, pc.sales_price, il.location, il.stock FROM products p JOIN product_codes pc ON p.id = pc.product_id LEFT JOIN inventory_levels il ON pc.code = il.product_code ORDER BY p.description, pc.code";
    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $productsById = [];
    foreach ($rows as $row) {
        $productId = $row['id'];
        if (!isset($productsById[$productId])) { $productsById[$productId] = ['id' => (int)$row['id'], 'description' => $row['description'], 'bu' => $row['bu'], 'is_promo' => (bool)$row['is_promo'], 'codes' => []]; }
        $code = $row['code'];
        $codeIndex = -1;
        foreach ($productsById[$productId]['codes'] as $idx => $existingCode) { if ($existingCode['code'] === $code) { $codeIndex = $idx; break; } }
        if ($codeIndex === -1) {
            $productsById[$productId]['codes'][] = ['code' => $code, 'type' => $row['type'], 'pieces_per_case' => (int)$row['pieces_per_case'], 'sales_price' => (float)$row['sales_price'], 'inventory' => []];
            $codeIndex = count($productsById[$productId]['codes']) - 1;
        }
        if ($row['location'] !== null) { $productsById[$productId]['codes'][$codeIndex]['inventory'][] = ['location' => $row['location'], 'stock' => (int)$row['stock']]; }
    }
    echo json_encode(['success' => true, 'data' => array_values($productsById)]);
    exit;
}

function getOrders($pdo) {
    $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
    $limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 20;
    $offset = ($page - 1) * $limit;
    $whereClauses = [];
    $params = [];
    
    $month = $_POST['month'] ?? date('m'); 
    $year = $_POST['year'] ?? date('Y');
    $poSearch = trim($_POST['po_number'] ?? '');
    $soSearch = trim($_POST['so_number'] ?? '');
    $daysFilter = $_POST['days'] ?? 'all';
    $statusFilter = $_POST['status_filter'] ?? 'all';

    // Auto-upgrade DB so we can read status / cancel_reason safely
    try { $pdo->exec("ALTER TABLE orders ADD COLUMN status VARCHAR(20) DEFAULT 'active'"); } catch (PDOException $e) {} 
    try { $pdo->exec("ALTER TABLE orders ADD COLUMN cancel_reason VARCHAR(255) DEFAULT NULL"); } catch (PDOException $e) {}
    try { $pdo->exec("ALTER TABLE orders ADD COLUMN encoded_by VARCHAR(100) DEFAULT NULL"); } catch (PDOException $e) {}
    
    // Specific Day filtering
    if ($daysFilter !== 'all' && !empty($daysFilter)) {
        $whereClauses[] = "DAY(o.order_date) = ?";
        $params[] = (int)$daysFilter;
    }

    if (!empty($year)) {
        $whereClauses[] = "YEAR(o.order_date) = ?";
        $params[] = (int)$year;
    }
    
    if ($month !== 'all' && !empty($month)) {
        $whereClauses[] = "MONTH(o.order_date) = ?";
        $params[] = (int)$month;
    }

    $errorFilter = $_POST['error_filter'] ?? 'all';

    // Status filter (active / cancelled / open / invoiced / all)
    if ($statusFilter === 'cancelled') {
        // Treat legacy 'deleted' rows as 'cancelled'
        $whereClauses[] = "o.status IN ('cancelled', 'deleted')";
    } else if ($statusFilter === 'active') {
        $whereClauses[] = "(o.status IS NULL OR (o.status <> 'cancelled' AND o.status <> 'deleted'))";
    } else if ($statusFilter === 'invoiced') {
        $whereClauses[] = "(o.status IS NULL OR (o.status <> 'cancelled' AND o.status <> 'deleted'))";
        $whereClauses[] = "o.so_number REGEXP '[a-zA-Z0-9]'";
    } else if ($statusFilter === 'open') {
        $whereClauses[] = "(o.status IS NULL OR (o.status <> 'cancelled' AND o.status <> 'deleted'))";
        $whereClauses[] = "(o.so_number IS NULL OR o.so_number NOT REGEXP '[a-zA-Z0-9]')";
    } else if ($statusFilter === 'all') {
        // Do nothing. Show absolutely everything.
    }

    // Build subquery date conditions so "Duplicate" filters respect the current time window
    $subDateCondPo = "";
    $subDateCondSo = "";
    if ($daysFilter !== 'all' && !empty($daysFilter)) {
        $d = (int)$daysFilter;
        $subDateCondPo .= " AND DAY(o2.order_date) = $d";
        $subDateCondSo .= " AND DAY(o3.order_date) = $d";
    }
    if (!empty($year)) {
        $y = (int)$year;
        $subDateCondPo .= " AND YEAR(o2.order_date) = $y";
        $subDateCondSo .= " AND YEAR(o3.order_date) = $y";
    }
    if ($month !== 'all' && !empty($month)) {
        $m = (int)$month;
        $subDateCondPo .= " AND MONTH(o2.order_date) = $m";
        $subDateCondSo .= " AND MONTH(o3.order_date) = $m";
    }

    // ★ Apply Error / Issue Filters (REGEXP '[a-zA-Z0-9]' ensures we ignore empty strings or [""])
    if ($errorFilter === 'no_so') {
        $whereClauses[] = "(o.so_number IS NULL OR o.so_number NOT REGEXP '[a-zA-Z0-9]')";
    } elseif ($errorFilter === 'dup_po') {
        $whereClauses[] = "o.po_number REGEXP '[a-zA-Z0-9]' AND EXISTS (SELECT 1 FROM orders o2 WHERE o2.po_number = o.po_number AND o2.id != o.id AND (o2.status IS NULL OR o2.status NOT IN ('cancelled', 'deleted')) $subDateCondPo)";
    } elseif ($errorFilter === 'dup_so') {
        $whereClauses[] = "o.so_number REGEXP '[a-zA-Z0-9]' AND EXISTS (SELECT 1 FROM orders o3 WHERE o3.so_number = o.so_number AND o3.id != o.id AND (o3.status IS NULL OR o3.status NOT IN ('cancelled', 'deleted')) $subDateCondSo)";
    }

    if (!empty($poSearch)) { $whereClauses[] = "o.po_number LIKE ?"; $params[] = '%' . $poSearch . '%'; }
    if (!empty($soSearch)) { $whereClauses[] = "o.so_number LIKE ?"; $params[] = '%' . $soSearch . '%'; }
    if (!empty($_POST['address'])) { $whereClauses[] = "o.customer_address LIKE ?"; $params[] = '%' . $_POST['address'] . '%'; }
    if (!empty($_POST['location']) && $_POST['location'] !== 'all') { $whereClauses[] = "o.location = ?"; $params[] = $_POST['location']; }
    if (!empty($_POST['customer']) && $_POST['customer'] !== 'all') { $whereClauses[] = "c.name = ?"; $params[] = $_POST['customer']; }
    if (!empty($_POST['bu']) && $_POST['bu'] !== 'all') { $whereClauses[] = "o.bu = ?"; $params[] = $_POST['bu']; }
    
    $whereSql = empty($whereClauses) ? '' : 'WHERE ' . implode(' AND ', $whereClauses);
    
    $countSql = "SELECT COUNT(DISTINCT o.id) FROM orders o LEFT JOIN customers c ON o.customer_id = c.id $whereSql";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $totalOrders = $countStmt->fetchColumn();
    
    $orderIdSql = "SELECT o.id FROM orders o LEFT JOIN customers c ON o.customer_id = c.id $whereSql ORDER BY o.order_date DESC, o.id DESC LIMIT ? OFFSET ?";
    $orderIdStmt = $pdo->prepare($orderIdSql);
    
    $paramIndex = 1;
    foreach ($params as $value) {
        $orderIdStmt->bindValue($paramIndex++, $value);
    }
    $orderIdStmt->bindValue($paramIndex++, $limit, PDO::PARAM_INT);
    $orderIdStmt->bindValue($paramIndex++, $offset, PDO::PARAM_INT);
    $orderIdStmt->execute();
    $orderIds = $orderIdStmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($orderIds)) {
        echo json_encode(['success' => true, 'data' => [], 'total_orders' => 0, 'pagination' => ['totalPages' => 0]]);
        exit;
    }
    
    $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
    $sql = "SELECT 
                o.id, o.po_number, o.order_date, o.location, o.bu, o.customer_address, o.is_pristine_checked,
                o.salesman_name, o.salesman_code, o.so_number,
                o.status, o.cancel_reason, o.encoded_by,
                c.name as customer_name,
                u.avatar_url as encoder_avatar, 
                u.display_name as encoder_display_name,
                (SELECT SUM(oi.price) FROM order_items oi WHERE oi.order_id = o.id) as total_value,
                (o.po_number REGEXP '[a-zA-Z0-9]' AND EXISTS (SELECT 1 FROM orders o2 WHERE o2.po_number = o.po_number AND o2.id != o.id AND (o2.status IS NULL OR o2.status NOT IN ('cancelled', 'deleted')) $subDateCondPo)) as is_duplicate_po,
                (o.so_number REGEXP '[a-zA-Z0-9]' AND EXISTS (SELECT 1 FROM orders o3 WHERE o3.so_number = o.so_number AND o3.id != o.id AND (o3.status IS NULL OR o3.status NOT IN ('cancelled', 'deleted')) $subDateCondSo)) as is_duplicate_so
            FROM orders o 
            LEFT JOIN customers c ON o.customer_id = c.id 
            LEFT JOIN users u ON o.encoded_by = u.username
            WHERE o.id IN ($placeholders) 
            ORDER BY FIELD(o.id, " . implode(',', $orderIds) . ")";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($orderIds);
    $finalOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true, 
        'data' => $finalOrders, 
        'total_orders' => (int)$totalOrders,
        'pagination' => [
            'total' => (int)$totalOrders,
            'page' => $page,
            'limit' => $limit,
            'totalPages' => ceil($totalOrders / $limit)
        ]
    ]);
    exit;
}

function getUnservedOrders($pdo) {
    $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
    $limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 50;
    $offset = ($page - 1) * $limit;
    
    $whereClauses = ["oi.status = 'unserved'"];
    $params = [];

    // --- ADDED DATE FILTER LOGIC ---
    $month = $_POST['month'] ?? date('m'); 
    $year = $_POST['year'] ?? date('Y');
    if ($month !== 'all') {
        if (!empty($year)) {
            $whereClauses[] = "YEAR(o.order_date) = ?";
            $params[] = $year;
        }
        if (!empty($month)) {
            $whereClauses[] = "MONTH(o.order_date) = ?";
            $params[] = $month;
        }
    }
    // --- END OF ADDED LOGIC ---

    if (!empty($_POST['location']) && $_POST['location'] !== 'all') { $whereClauses[] = "o.location = ?"; $params[] = $_POST['location']; }
    
    // --- THIS IS THE FIX: Changed 'o.bu' to 'p.bu' ---
    if (!empty($_POST['bu']) && $_POST['bu'] !== 'all') { $whereClauses[] = "p.bu = ?"; $params[] = $_POST['bu']; } 
    
    if (!empty($_POST['customer']) && $_POST['customer'] !== 'all') { $whereClauses[] = "c.name = ?"; $params[] = $_POST['customer']; }
    if (!empty($_POST['sku'])) {
        $whereClauses[] = "oi.sku LIKE ?";
        $params[] = '%' . $_POST['sku'] . '%';
    }
    
    $whereSql = 'WHERE ' . implode(' AND ', $whereClauses);
    
    // Updated to join products/product_codes to allow filtering by BU
    $baseFromSql = "FROM orders o 
                    JOIN order_items oi ON o.id = oi.order_id 
                    LEFT JOIN customers c ON o.customer_id = c.id
                    LEFT JOIN product_codes pc ON oi.sku = pc.code
                    LEFT JOIN products p ON pc.product_id = p.id";

    $countSql = "SELECT COUNT(DISTINCT o.id) $baseFromSql $whereSql";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $totalOrders = $countStmt->fetchColumn();

    $orderIdSql = "SELECT DISTINCT o.id $baseFromSql $whereSql ORDER BY o.order_date DESC LIMIT ? OFFSET ?";
    $orderIdStmt = $pdo->prepare($orderIdSql);
    $orderIdStmt->execute(array_merge($params, [$limit, $offset]));
    $orderIds = $orderIdStmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($orderIds)) {
        echo json_encode(['success' => true, 'data' => [], 'total_orders' => 0]);
        exit;
    }
    
    $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
    
    $sql = "SELECT o.id, o.po_number, o.order_date, o.location, o.bu, c.name as customer_name, 
                   oi.id as item_id, oi.sku, oi.description, oi.quantity, oi.price, oi.status 
            FROM orders o 
            LEFT JOIN customers c ON o.customer_id = c.id 
            JOIN order_items oi ON o.id = oi.order_id 
            WHERE o.id IN ($placeholders) 
            ORDER BY o.order_date DESC, o.id ASC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute($orderIds);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $ordersById = [];
    foreach ($rows as $row) {
        $orderId = $row['id'];
        if (!isset($ordersById[$orderId])) {
            $ordersById[$orderId] = [ 'id' => $orderId, 'location' => $row['location'], 'bu' => $row['bu'], 'customer' => ['name' => $row['customer_name'], 'poNumber' => $row['po_number']], 'date' => $row['order_date'], 'items' => [] ];
        }
        $ordersById[$orderId]['items'][] = $row;
    }
    
    $sortedOrders = [];
    foreach($orderIds as $id){ if(isset($ordersById[$id])){ $sortedOrders[] = $ordersById[$id]; } }
    
    echo json_encode(['success' => true, 'data' => $sortedOrders, 'total_orders' => $totalOrders]);
    exit;
}

function getOrderDetails($pdo) {
    $orderId = $_POST['id'] ?? 0;
    if (empty($orderId)) { throw new Exception('Order ID is required.'); }
    $orderStmt = $pdo->prepare("SELECT o.*, c.name as customer_name FROM orders o LEFT JOIN customers c ON o.customer_id = c.id WHERE o.id = ?");
    $orderStmt->execute([$orderId]); $order = $orderStmt->fetch(PDO::FETCH_ASSOC);
    if (!$order) { throw new Exception('Order not found.'); }
    $itemStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $itemStmt->execute([$orderId]); $items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
    $result = ['id' => $order['id'], 'location' => $order['location'], 'bu' => $order['bu'], 'discount' => (float)$order['discount_percentage'], 'customer' => ['name' => $order['customer_name'] ?? 'N/A', 'address' => $order['customer_address'], 'poNumber' => $order['po_number']], 'date' => $order['order_date'], 'items' => $items];
    echo json_encode(['success' => true, 'data' => $result]);
    exit;
}

function getFulfillableItems($pdo) {
    // 1. Get filters
    $month = $_POST['month'] ?? date('m');
    $year = $_POST['year'] ?? date('Y');
    $location = $_POST['location'] ?? 'all';
    $customer = $_POST['customer'] ?? 'all';
    $bu = $_POST['bu'] ?? 'all'; // NEW: Get BU filter

    // 2. Prepare WHERE conditions
    $whereClauses = [
        "oi.status = 'unserved'",
        "c.is_priority = 1"
    ];
    $params = [];

    if ($year !== 'all') {
        $whereClauses[] = "YEAR(o.order_date) = ?";
        $params[] = $year;
    }
    if ($month !== 'all') {
        $whereClauses[] = "MONTH(o.order_date) = ?";
        $params[] = $month;
    }
    if ($location !== 'all') {
        $whereClauses[] = "o.location = ?";
        $params[] = $location;
    }
    if ($customer !== 'all') {
        $whereClauses[] = "c.name = ?";
        $params[] = $customer;
    }
    // NEW: Filter by Business Unit
    if ($bu !== 'all') {
        $whereClauses[] = "p.bu = ?";
        $params[] = $bu;
    }

    $whereSql = "WHERE " . implode(" AND ", $whereClauses);

    // 3. The main SQL query
    // Added JOIN products p to access the 'bu' column
    $sql = "SELECT
                o.id AS order_id,
                o.po_number,
                o.so_number,
                o.customer_address,
                c.name AS customer_name,
                oi.id AS item_id,
                oi.sku, 
                oi.description,
                oi.quantity,
                p.bu, 
                (
                    SELECT COALESCE(SUM(il.stock), 0)
                    FROM inventory_levels il
                    JOIN product_codes pc2 ON il.product_code = pc2.code
                    WHERE pc2.product_id = pc1.product_id AND il.location = o.location AND pc2.type = 'sku'
                ) AS total_available_stock,
                (
                    SELECT pc2.code
                    FROM inventory_levels il
                    JOIN product_codes pc2 ON il.product_code = pc2.code
                    WHERE pc2.product_id = pc1.product_id 
                      AND il.location = o.location 
                      AND pc2.type = 'sku'
                      AND il.stock > 0
                    ORDER BY il.stock DESC
                    LIMIT 1
                ) AS available_sku,
                (
                    SELECT MAX(il.last_updated)
                    FROM inventory_levels il
                    JOIN product_codes pc2 ON il.product_code = pc2.code
                    WHERE pc2.product_id = pc1.product_id AND il.location = o.location AND pc2.type = 'sku'
                ) AS stock_update_date
            FROM
                order_items oi
            JOIN orders o ON oi.order_id = o.id
            JOIN customers c ON o.customer_id = c.id
            JOIN product_codes pc1 ON oi.sku = pc1.code
            JOIN products p ON pc1.product_id = p.id
            {$whereSql}
            HAVING
                total_available_stock >= oi.quantity
            ORDER BY
                o.order_date ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $items]);
    exit;
}


function getCustomerDashboardData($pdo) {
    $customerId = $_POST['customer_id'] ?? 0; if (!$customerId) throw new Exception("Customer ID is required.");
    $whereClauses = ["o.customer_id = ?"]; $params = [$customerId];
    if (!empty($_POST['location']) && $_POST['location'] !== 'all') { $whereClauses[] = "o.location = ?"; $params[] = $_POST['location']; }
    if (!empty($_POST['bu']) && $_POST['bu'] !== 'all') { $whereClauses[] = "o.bu = ?"; $params[] = $_POST['bu']; }
    $whereSql = 'WHERE ' . implode(' AND ', $whereClauses);
    $sql = "SELECT SUM(CASE WHEN oi.status = 'served' THEN oi.price ELSE 0 END) as totalServedValue, SUM(CASE WHEN oi.status = 'unserved' THEN oi.price ELSE 0 END) as totalUnservedValue FROM orders o JOIN order_items oi ON o.id = oi.order_id $whereSql";
    $stmt = $pdo->prepare($sql); $stmt->execute($params); $data = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $data]);
    exit;
}

function updateOrderItems($pdo) {
        $orderId = $_POST['order_id'] ?? 0;
        $itemsData = $_POST['items'] ?? null;
        $soNumbersJSON = $_POST['so_numbers'] ?? '[]';
        $remarksJSON = $_POST['remarks'] ?? '[]';
        $restoreStock = filter_var($_POST['restore_stock'] ?? 'true', FILTER_VALIDATE_BOOLEAN);
        // New Fields
        $newPo = $_POST['po_number'] ?? null;
        $newDate = $_POST['order_date'] ?? null;
        $newLocation = $_POST['location'] ?? null;
        $newAddress = trim($_POST['address'] ?? '');
        $newCustomerId = $_POST['customer_id'] ?? null;

        if (empty($orderId) || empty($itemsData)) {
        throw new Exception('Missing order ID or items data.');
    }

    $newItems = json_decode($itemsData, true);
    $soNumbers = json_decode($soNumbersJSON, true);
    $remarksArray = json_decode($remarksJSON, true);

    $pdo->beginTransaction();
    try {
        // 1. Fetch Current DB State
        $orderInfoStmt = $pdo->prepare("SELECT location, customer_address, customer_code, order_date FROM orders WHERE id = ?");
        $orderInfoStmt->execute([$orderId]);
        $orderInfo = $orderInfoStmt->fetch(PDO::FETCH_ASSOC);
        $oldLocation = $orderInfo['location'];

        // 2. Determine effective location (Did it change?)
        $locationChanged = ($newLocation && $newLocation !== $oldLocation);
        $targetLocation = $newLocation ?: $oldLocation;

        // 3. Update Order Details (Header)
        $updateSql = "UPDATE orders SET so_number = ?, remarks = ?";
        $params = [json_encode($soNumbers), json_encode($remarksArray)];

        if ($newPo) { $updateSql .= ", po_number = ?"; $params[] = $newPo; }
        if ($newCustomerId) { $updateSql .= ", customer_id = ?"; $params[] = $newCustomerId; }
        if ($newDate) {
            // If the frontend only sends YYYY-MM-DD (10 characters), keep the original time
            if (strlen(trim($newDate)) === 10 && !empty($orderInfo['order_date'])) {
                $originalTime = date('H:i:s', strtotime($orderInfo['order_date']));
                $newDate = trim($newDate) . ' ' . $originalTime;
            }
            $updateSql .= ", order_date = ?"; 
            $params[] = $newDate; 
        }
        if ($newLocation) { $updateSql .= ", location = ?"; $params[] = $newLocation; }
        
        if (!empty($newAddress) && $newAddress !== $orderInfo['customer_address']) { 
            $updateSql .= ", customer_address = ?"; 
            $params[] = $newAddress; 
            
            // Auto-fetch the new customer code & salesman based on the new address
            $codeStmt = $pdo->prepare("SELECT customer_code, salesman_name, salesman_code FROM customer_address_codes WHERE address = ?");
            $codeStmt->execute([$newAddress]);
            $addrData = $codeStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($addrData) {
                $updateSql .= ", customer_code = ?, salesman_name = ?, salesman_code = ?";
                $params[] = $addrData['customer_code'];
                $params[] = $addrData['salesman_name'];
                $params[] = $addrData['salesman_code'];
            }
        }
        
        $updateSql .= " WHERE id = ?";
        $params[] = $orderId;
        
        $orderUpdateStmt = $pdo->prepare($updateSql);
        $orderUpdateStmt->execute($params);

        // 4. Handle Inventory & Item Updates
        
        // Fetch original served items to calculate stock differences
        $originalItemsStmt = $pdo->prepare("SELECT sku, quantity FROM order_items WHERE order_id = ? AND status IN ('served', 'fulfilled')");
        $originalItemsStmt->execute([$orderId]);
        $originalServedItems = $originalItemsStmt->fetchAll(PDO::FETCH_KEY_PAIR); // [sku => qty]

        // Prepare Item Update Statement
        $itemUpdateStmt = $pdo->prepare("UPDATE order_items SET sku = ?, description = ?, quantity = ?, price = ?, status = ? WHERE id = ?");
        
        // Prepare Stock Update Statement
        $stockUpdateStmt = $pdo->prepare("UPDATE inventory_levels SET stock = stock + ? WHERE product_code = ? AND location = ?");

        $newServedItems = [];

        // Save Item Changes to DB first
        foreach ($newItems as $item) {
            if (!empty($item['id'])) {
                $itemUpdateStmt->execute([$item['sku'], $item['description'], $item['quantity'], $item['price'], $item['status'], $item['id']]);
                if ($item['status'] === 'served' || $item['status'] === 'fulfilled') {
                    $newServedItems[$item['sku']] = ($newServedItems[$item['sku']] ?? 0) + (int)$item['quantity'];
                }
            }
        }

        // --- STOCK LOGIC ---
        
        if ($locationChanged) {
            // SCENARIO A: Location Changed (Complex Move)
            // 1. Return ALL original stock to OLD location
            foreach ($originalServedItems as $sku => $qty) {
                if ($restoreStock) {
                    $stockUpdateStmt->execute([$qty, $sku, $oldLocation]);
                }
            }
            // 2. Deduct ALL new stock from NEW location
            foreach ($newServedItems as $sku => $qty) {
                // Deducting means adding a negative number
                $stockUpdateStmt->execute([-1 * abs($qty), $sku, $newLocation]);
            }
        } else {
            // SCENARIO B: Same Location (Standard Delta)
            $allSkusInvolved = array_unique(array_merge(array_keys($originalServedItems), array_keys($newServedItems)));
            
            foreach ($allSkusInvolved as $sku) {
                $originalQty = $originalServedItems[$sku] ?? 0;
                $newQty = $newServedItems[$sku] ?? 0;
                $adjustment = $originalQty - $newQty; // Positive = Return to stock, Negative = Deduct

                if ($adjustment > 0 && !$restoreStock) {
                    continue; // User chose not to restore stock
                }

                if ($adjustment != 0) {
                    $stockUpdateStmt->execute([$adjustment, $sku, $oldLocation]);
                }
            }
        }

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => "Order updated successfully."]);
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
    exit;
}

function addOrder($pdo) {
    $itemsJson = $_POST['items'] ?? '[]';
    $location = $_POST['location'] ?? '';
    $bu = $_POST['bu'] ?? '';
    $discount = (float)($_POST['discount'] ?? 0);
    $customerId = $_POST['customer_id'] ?? null;
    $customerName = trim($_POST['customer_name'] ?? '');
    $customerAddress = trim($_POST['customer_address'] ?? '');
    $poNumber = $_POST['po_number'] ?? '';
    

    // Drafts allow empty items, but usually we still want valid items.
    // If you want to allow saving a draft with NO items, remove the empty($itemsJson) check.
    if (empty($poNumber) || empty($itemsJson) || empty($location)) {
        throw new Exception("Missing required order data (PO, Items, or Location).");
    }

    $isValidCustomer = false;
    if (!empty($customerId)) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM customers WHERE id = ?");
        $stmt->execute([$customerId]);
        if ($stmt->fetchColumn() > 0) {
            $isValidCustomer = true;
        }
    }
    if (!$isValidCustomer && !empty($customerName)) {
        $stmt = $pdo->prepare("SELECT id FROM customers WHERE UPPER(name) = UPPER(?)");
        $stmt->execute([$customerName]);
        $existingId = $stmt->fetchColumn();
        if ($existingId) {
            $customerId = $existingId;
        } else {
            $stmt = $pdo->prepare("INSERT INTO customers (name) VALUES (?)");
            $stmt->execute([$customerName]);
            $customerId = $pdo->lastInsertId();
        }
    }
    if (empty($customerId)) {
        throw new Exception("Customer could not be found or created.");
    }
    
    $codeStmt = $pdo->prepare("SELECT customer_code, salesman_name, salesman_code FROM customer_address_codes WHERE address = ?");
    $codeStmt->execute([$customerAddress]);
    $addrData = $codeStmt->fetch(PDO::FETCH_ASSOC);
    
    $customer_code = $addrData['customer_code'] ?? null;
    $salesman_name = $addrData['salesman_name'] ?? null;
    $salesman_code = $addrData['salesman_code'] ?? null;
    
    $items = json_decode($itemsJson, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($items) || empty($items)) {
        throw new Exception("Invalid or empty items data provided.");
    }
    
    if (empty($bu) && !empty($items)) {
        $firstSku = $items[0]['sku'] ?? '';
        if($firstSku) {
            $buStmt = $pdo->prepare("SELECT p.bu FROM products p JOIN product_codes pc ON p.id = pc.product_id WHERE pc.code = ?");
            $buStmt->execute([$firstSku]);
            $bu = $buStmt->fetchColumn() ?: 'Health';
        }
    }

    $pdo->beginTransaction();
    try {
        $currentDateTime = date('Y-m-d H:i:s');
        $encodedBy = $_SESSION['username'] ?? 'Unknown';
        $stmt = $pdo->prepare("INSERT INTO orders (customer_id, customer_address, customer_code, salesman_name, salesman_code, po_number, location, bu, discount_percentage, order_date, encoded_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$customerId, $customerAddress, $customer_code, $salesman_name, $salesman_code, $poNumber, $location, $bu, $discount, $currentDateTime, $encodedBy]);
        $orderId = $pdo->lastInsertId();
        
        $itemInsertStmt = $pdo->prepare("INSERT INTO order_items (order_id, sku, description, quantity, price, status, stock_snapshot) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stockUpdateStmt = $pdo->prepare("UPDATE inventory_levels SET stock = stock - ? WHERE product_code = ? AND location = ?");
        $stockCheckStmt = $pdo->prepare("SELECT stock FROM inventory_levels WHERE product_code = ? AND location = ?");
        
        foreach ($items as $item) {
            if (empty($item['sku'])) { continue; }
            
            $priceStmt = $pdo->prepare("SELECT sales_price FROM product_codes WHERE code = ?");
            $priceStmt->execute([$item['sku']]);
            $sales_price_raw = $priceStmt->fetchColumn();
            $sales_price = is_numeric($sales_price_raw) ? (float)$sales_price_raw : 0;
            
            $quantity = isset($item['quantity']) && is_numeric($item['quantity']) ? (int)$item['quantity'] : 0;
            $gross_price = $sales_price * $quantity;
            $final_price = $gross_price;
            if ($discount > 0) {
                $final_price = $gross_price * (1 - ($discount / 100));
            }

            // Get Current Stock
            $stockCheckStmt->execute([$item['sku'], $location]);
            $currentStock = $stockCheckStmt->fetchColumn();
            if ($currentStock === false) $currentStock = 0;

            // Determine Status
            $passedStatus = $item['status'] ?? null;
            
            if ($passedStatus === 'unserved') {
                $status = 'unserved'; // Force unserved if explicitly toggled on front-end
            } else {
                $status = ($currentStock >= $quantity) ? 'served' : 'unserved';
            }

            // Insert Item
            $itemInsertStmt->execute([ $orderId, $item['sku'], $item['description'], $quantity, $final_price, $status, $currentStock ]);
            
            // Deduct Stock (ONLY if served or fulfilled)
            if ($status === 'served' || $status === 'fulfilled') {
                $stockUpdateStmt->execute([$quantity, $item['sku'], $location]);
            }
        }
        
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Order processed.', 'order_id' => $orderId]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
    exit;
}
function addCustomer($pdo) {
    $name = trim($_POST['name'] ?? '');
    if (empty($name)) { throw new Exception('Customer name is required.'); }
    $check = $pdo->prepare("SELECT id FROM customers WHERE LOWER(name) = LOWER(?)");
    $check->execute([$name]);
    $existing = $check->fetch();
    if ($existing) {
        echo json_encode(['success' => false, 'message' => 'A customer with this name already exists.']);
        exit;
    }
    $stmt = $pdo->prepare("INSERT INTO customers (name) VALUES (?)");
    $stmt->execute([$name]);
    echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
    exit;
}

function deleteCustomer($pdo) {
    $id = $_POST['id'] ?? 0; if (empty($id)) { throw new Exception('Customer ID is required.'); }
    $stmt = $pdo->prepare("DELETE FROM customers WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(['success' => true]);
    exit;
}

function toggleCustomerPriority($pdo) {
    $id = $_POST['id'] ?? 0; $is_priority = $_POST['is_priority'] ?? 0;
    if (empty($id)) { throw new Exception('Customer ID is required.'); }
    $stmt = $pdo->prepare("UPDATE customers SET is_priority = ? WHERE id = ?");
    $stmt->execute([(int)$is_priority, $id]);
    echo json_encode(['success' => true]);
    exit;
}

function getCustomerLvs($pdo) {
    $stmt = $pdo->query("
        SELECT c.id as customer_id, c.name as customer_name,
               MAX(CASE WHEN l.bu = 'Nutri' THEN l.lv_limit ELSE NULL END) as nutri_limit,
               MAX(CASE WHEN l.bu = 'Health' THEN l.lv_limit ELSE NULL END) as health_limit,
               MAX(CASE WHEN l.bu = 'Hygiene' THEN l.lv_limit ELSE NULL END) as hygiene_limit
        FROM customers c
        LEFT JOIN customer_lv_limits l ON c.id = l.customer_id
        GROUP BY c.id, c.name
        ORDER BY c.name ASC
    ");
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    exit;
}

function updateCustomerLv($pdo) {
    $customer_id = $_POST['customer_id'] ?? 0; 
    $bu = $_POST['bu'] ?? '';
    $lv_limit = !empty($_POST['lv_limit']) ? $_POST['lv_limit'] : null;
    
    if (empty($customer_id) || empty($bu)) { throw new Exception('Customer ID and BU are required.'); }
    
    if ($lv_limit === null) {
        $stmt = $pdo->prepare("DELETE FROM customer_lv_limits WHERE customer_id = ? AND bu = ?");
        $stmt->execute([$customer_id, $bu]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO customer_lv_limits (customer_id, bu, lv_limit) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE lv_limit = VALUES(lv_limit)");
        $stmt->execute([$customer_id, $bu, $lv_limit]);
    }
    echo json_encode(['success' => true]);
    exit;
}

function addProduct($pdo) {
    $sku = $_POST['sku'] ?? ''; $description = $_POST['description'] ?? ''; $bu = $_POST['bu'] ?? 'Health'; $stockQty = $_POST['stock'] ?? 0; $location = $_POST['location'] ?? '';
    if (empty($sku) || empty($description) || empty($location)) { throw new Exception('SKU, Description, and Location are required.'); }
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("SELECT id FROM products WHERE description = ? AND bu = ?");
    $stmt->execute([$description, $bu]); $productId = $stmt->fetchColumn();
    if (!$productId) {
        $stmt = $pdo->prepare("INSERT INTO products (description, bu) VALUES (?, ?)");
        $stmt->execute([$description, $bu]); $productId = $pdo->lastInsertId();
    }
    $stmt = $pdo->prepare("INSERT INTO product_codes (product_id, code, type) VALUES (?, ?, 'sku') ON DUPLICATE KEY UPDATE product_id = VALUES(product_id)");
    $stmt->execute([$productId, $sku]);
    $stockStmt = $pdo->prepare("INSERT INTO inventory_levels (product_code, location, stock) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE stock = VALUES(stock)");
    $stockStmt->execute([$sku, $location, (int)$stockQty]);
    $pdo->commit();
    echo json_encode(['success' => true]);
    exit;
}

function bulkAddProducts($pdo) {
    $data = $_POST['data'] ?? ''; if(empty($data)) { throw new Exception('No data provided.'); }
    $lines = explode("\n", trim($data)); $productsAdded = 0; $codesAdded = 0; $validBUs = ['Health', 'Hygiene', 'Nutri'];
    $pdo->beginTransaction();
    $productStmt = $pdo->prepare("INSERT INTO products (description, bu, is_promo) VALUES (?, ?, ?)");
    $findProductStmt = $pdo->prepare("SELECT id FROM products WHERE description = ? AND bu = ?");
    $updatePromoStmt = $pdo->prepare("UPDATE products SET is_promo = ? WHERE id = ?");
    $codeStmt = $pdo->prepare("INSERT INTO product_codes (product_id, code, type, pieces_per_case) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE pieces_per_case = VALUES(pieces_per_case)");
    foreach ($lines as $line) {
        if (empty(trim($line))) continue;
        $parts = preg_split('/\s+/', trim($line), -1, PREG_SPLIT_NO_EMPTY); if (count($parts) < 3) continue;
        $isPromo = false; $piecesPerCase = 1; $lastPart = $parts[count($parts) - 1];
        if (is_numeric($lastPart)) { $piecesPerCase = (int)array_pop($parts); }
        if (count($parts) >= 2) {
            $promoCheck = strtolower($parts[count($parts) - 2] . ' ' . $parts[count($parts) - 1]);
            if ($promoCheck === 'promo sku') { $isPromo = true; array_pop($parts); array_pop($parts); } 
            elseif ($promoCheck === 'regular sku') { $isPromo = false; array_pop($parts); array_pop($parts); }
        }
        $bu = 'Health'; $startIndex = 0; $firstPart = ucfirst(strtolower($parts[0]));
        if (in_array($firstPart, $validBUs)) { $bu = $firstPart; $startIndex = 1; }
        $numericCodes = []; $descriptionParts = []; $foundDescription = false;
        for ($i = $startIndex; $i < count($parts); $i++) { if (is_numeric($parts[$i]) && !$foundDescription) { $numericCodes[] = $parts[$i]; } else { $foundDescription = true; $descriptionParts[] = $parts[$i]; } }
        $description = implode(' ', $descriptionParts);
        if (empty($description) || empty($numericCodes)) continue;
        $findProductStmt->execute([$description, $bu]); $productId = $findProductStmt->fetchColumn();
        if (!$productId) { $productStmt->execute([$description, $bu, $isPromo]); $productId = $pdo->lastInsertId(); $productsAdded++; } else { $updatePromoStmt->execute([$isPromo, $productId]); }
        foreach (array_unique($numericCodes) as $code) {
            $type = strlen((string)$code) > 8 ? 'barcode' : 'sku';
            $codeStmt->execute([$productId, $code, $type, $piecesPerCase]);
            if ($codeStmt->rowCount() > 0) $codesAdded++;
        }
    }
    $pdo->commit();
    echo json_encode(['success' => true, 'message' => "$productsAdded products and $codesAdded codes processed."]);
    exit;
}

function bulkUpdateStock($pdo) {
    $data = $_POST['data'] ?? '';
    $location = $_POST['location'] ?? '';

    if(empty($data) || empty($location)) {
        throw new Exception('No stock data or location provided.');
    }

    $lines = explode("\n", trim($data));
    $notFoundCodes = [];
    $processedCount = 0;

    $pdo->beginTransaction();
    try {
        // Prepare statements outside the loop
        $checkCodeStmt = $pdo->prepare("SELECT COUNT(*) FROM product_codes WHERE code = ?");
        
        // Statement 1: Update inventory_levels table (Stock)
        $stockUpdateStmt = $pdo->prepare("
            INSERT INTO inventory_levels (product_code, location, stock)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE stock = VALUES(stock), last_updated = NOW()
        ");
        
        // --- NEW: Statement 2: Update product_codes table (Price) ---
        $priceUpdateStmt = $pdo->prepare(
            "UPDATE product_codes SET sales_price = ? WHERE code = ?"
        );
        // --- END NEW ---

        // This resets all stock for the location to 0 first, as requested.
        $pdo->prepare("UPDATE inventory_levels SET stock = 0 WHERE location = ?")->execute([$location]);

        foreach ($lines as $line) {
            if (empty(trim($line))) continue;

            // Split by whitespace
            $parts = preg_split('/\s+/', trim($line), -1, PREG_SPLIT_NO_EMPTY);
            // Need at least 3 parts: SKU, Quantity, Price
            if (count($parts) < 3) continue; 

            $code = $parts[0];
            // Get the second to last part (quantity)
            $quantityStr = str_replace(',', '', $parts[count($parts) - 2]);
            // --- NEW: Get the very last part (price) ---
            $priceStr = str_replace(',', '', $parts[count($parts) - 1]);

            // Validate all parts are numeric
            if (!is_numeric($code) || !is_numeric($quantityStr) || !is_numeric($priceStr)) {
                continue; // Skip lines with invalid numeric values
            }

            $quantity = (int)$quantityStr;
            $price = (float)$priceStr; // --- NEW ---

            // Check if SKU exists
            $checkCodeStmt->execute([$code]);
            if ($checkCodeStmt->fetchColumn() > 0) {
                // 1. Update stock level
                $stockUpdateStmt->execute([$code, $location, $quantity]);
                
                // 2. --- NEW: Update price ---
                $priceUpdateStmt->execute([$price, $code]);
                
                $processedCount++;
            } else {
                $notFoundCodes[] = $code;
            }
        } // End loop

        $pdo->commit(); // Save changes

        $message = "$processedCount records for '{$location}' updated (Stock & Price).";
        if (!empty($notFoundCodes)) {
            $message .= " SKIPPED non-existent SKUs: " . implode(', ', array_unique($notFoundCodes));
        }
        echo json_encode(['success' => true, 'message' => $message]);

    } catch (Exception $e) {
        $pdo->rollBack(); // Revert changes on error
        throw $e;
    }
    exit;
}

function deleteCode($pdo) {
    $code = $_POST['code'] ?? ''; if (empty($code)) { throw new Exception('Code (SKU/Barcode) is required.'); }
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("SELECT product_id FROM product_codes WHERE code = ?");
    $stmt->execute([$code]); $productId = $stmt->fetchColumn();
    $pdo->prepare("DELETE FROM product_codes WHERE code = ?")->execute([$code]);
    $pdo->prepare("DELETE FROM inventory_levels WHERE product_code = ?")->execute([$code]);
    if ($productId) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM product_codes WHERE product_id = ?");
        $stmt->execute([$productId]);
        if ($stmt->fetchColumn() == 0) { $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$productId]); }
    }
    $pdo->commit();
    echo json_encode(['success' => true]);
    exit;
}

function bulkAddAliases($pdo) {
    $data = $_POST['data'] ?? ''; if (empty($data)) { throw new Exception('No alias data provided.'); }
    $lines = explode("\n", trim($data)); $linkedCount = 0; $notFound = [];
    $pdo->beginTransaction();
    $findStmt = $pdo->prepare("SELECT product_id FROM product_codes WHERE code = ? AND type = 'barcode'");
    $insertStmt = $pdo->prepare("INSERT INTO product_codes (product_id, code, type) VALUES (?, ?, 'sku') ON DUPLICATE KEY UPDATE product_id = VALUES(product_id)");
    foreach ($lines as $line) {
        if (empty(trim($line))) continue;
        $parts = preg_split('/\s+/', trim($line), -1, PREG_SPLIT_NO_EMPTY); if (count($parts) < 2) continue;
        $customerSku = array_shift($parts); $barcode = array_pop($parts);
        if (!is_numeric($customerSku) || !is_numeric($barcode)) { continue; }
        $findStmt->execute([$barcode]); $productId = $findStmt->fetchColumn();
        if ($productId) {
            $insertStmt->execute([$productId, $customerSku]);
            if ($insertStmt->rowCount() > 0) $linkedCount++;
        } else { $notFound[] = $barcode; }
    }
    $pdo->commit();
    $message = "$linkedCount customer SKUs were successfully linked.";
    if (!empty($notFound)) { $message .= " Barcodes not found: " . implode(', ', array_unique($notFound)); }
    echo json_encode(['success' => true, 'message' => $message]);
    exit;
}

function getUnlinkedSkus($pdo) {
    $location = $_POST['location'] ?? 'Davao';
    $sql = "SELECT p.description, pc.code AS sku, (SELECT il.stock FROM inventory_levels il WHERE il.product_code = pc.code AND il.location = ?) as current_stock FROM products p JOIN product_codes pc ON p.id = pc.product_id WHERE p.id IN (SELECT product_id FROM product_codes GROUP BY product_id HAVING SUM(CASE WHEN type = 'barcode' THEN 1 ELSE 0 END) = 0) AND pc.type = 'sku' ORDER BY p.description;";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$location]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $results]);
    exit;
}
function deleteOrder($pdo) {
    $orderId = $_POST['order_id'] ?? 0;
    if (empty($orderId)) {
        throw new Exception('Order ID is required.');
    }

    $pdo->beginTransaction();
    try {
        // Just in case it wasn't cancelled yet, ensure any served stock is returned before hard wiping
        $itemsStmt = $pdo->prepare("SELECT sku, quantity, o.location FROM order_items oi JOIN orders o ON oi.order_id = o.id WHERE oi.order_id = ? AND oi.status IN ('served', 'fulfilled')");
        $itemsStmt->execute([$orderId]);
        $servedItems = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($servedItems)) {
            $stockUpdateStmt = $pdo->prepare("UPDATE inventory_levels SET stock = stock + ? WHERE product_code = ? AND location = ?");
            $location = $servedItems[0]['location'];
            foreach ($servedItems as $item) {
                $stockUpdateStmt->execute([$item['quantity'], $item['sku'], $location]);
            }
        }
        
        // HARD DELETE: Completely erase the records
        $pdo->prepare("DELETE FROM order_items WHERE order_id = ?")->execute([$orderId]);
        $pdo->prepare("DELETE FROM orders WHERE id = ?")->execute([$orderId]);
        
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => "Order #{$orderId} has been permanently deleted."]);
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
    exit;
}

function cancelOrder($pdo) {
    $orderId = $_POST['order_id'] ?? 0;
    $reason = $_POST['reason'] ?? '';

    if (empty($orderId)) {
        throw new Exception('Order ID is required.');
    }

    // --- AUTO-UPGRADE DATABASE ---
    // Silently add status and reason columns to orders table if they don't exist yet
    try { $pdo->exec("ALTER TABLE orders ADD COLUMN status VARCHAR(20) DEFAULT 'active'"); } catch (PDOException $e) {} 
    try { $pdo->exec("ALTER TABLE orders ADD COLUMN cancel_reason VARCHAR(255) DEFAULT NULL"); } catch (PDOException $e) {}
    // For Uncancel support: remember each item's status before it was cancelled
    try { $pdo->exec("ALTER TABLE order_items ADD COLUMN pre_cancel_status VARCHAR(20) DEFAULT NULL"); } catch (PDOException $e) {}

    $pdo->beginTransaction();
    try {
        // 1. Return stock for 'served' items back to inventory
        $itemsStmt = $pdo->prepare("SELECT sku, quantity, o.location FROM order_items oi JOIN orders o ON oi.order_id = o.id WHERE oi.order_id = ? AND oi.status IN ('served', 'fulfilled')");
        $itemsStmt->execute([$orderId]);
        $servedItems = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($servedItems)) {
            $stockUpdateStmt = $pdo->prepare("UPDATE inventory_levels SET stock = stock + ? WHERE product_code = ? AND location = ?");
            $location = $servedItems[0]['location'];
            foreach ($servedItems as $item) {
                $stockUpdateStmt->execute([$item['quantity'], $item['sku'], $location]);
            }
        }

        // 2. Save each item's CURRENT status into pre_cancel_status so Uncancel can restore it later
        $pdo->prepare("UPDATE order_items SET pre_cancel_status = status WHERE order_id = ? AND status <> 'cancelled'")->execute([$orderId]);

        // 3. Mark order items as cancelled (removes them from sales metrics)
        $pdo->prepare("UPDATE order_items SET status = 'cancelled' WHERE order_id = ?")->execute([$orderId]);

        // 4. Mark the order itself as cancelled and save the reason
        $pdo->prepare("UPDATE orders SET status = 'cancelled', cancel_reason = ? WHERE id = ?")->execute([$reason, $orderId]);

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => "Order #{$orderId} has been successfully cancelled."]);
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
    exit;
}

function uncancelOrder($pdo) {
    $orderId = $_POST['order_id'] ?? 0;
    if (empty($orderId)) {
        throw new Exception('Order ID is required.');
    }

    // Make sure the columns exist (auto-upgrade)
    try { $pdo->exec("ALTER TABLE orders ADD COLUMN status VARCHAR(20) DEFAULT 'active'"); } catch (PDOException $e) {} 
    try { $pdo->exec("ALTER TABLE orders ADD COLUMN cancel_reason VARCHAR(255) DEFAULT NULL"); } catch (PDOException $e) {}
    try { $pdo->exec("ALTER TABLE order_items ADD COLUMN pre_cancel_status VARCHAR(20) DEFAULT NULL"); } catch (PDOException $e) {}

    $pdo->beginTransaction();
    try {
        // 1. Look at each cancelled item and figure out what status to restore it to
        $itemsStmt = $pdo->prepare("SELECT oi.id, oi.sku, oi.quantity, COALESCE(oi.pre_cancel_status, 'served') as restore_status, o.location FROM order_items oi JOIN orders o ON oi.order_id = o.id WHERE oi.order_id = ? AND oi.status = 'cancelled'");
        $itemsStmt->execute([$orderId]);
        $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($items)) {
            throw new Exception('Nothing to restore – this order is not cancelled.');
        }

        // 2. For items going back to served/fulfilled, deduct stock again
        $stockUpdateStmt = $pdo->prepare("UPDATE inventory_levels SET stock = stock - ? WHERE product_code = ? AND location = ?");
        $restoreStmt = $pdo->prepare("UPDATE order_items SET status = ?, pre_cancel_status = NULL WHERE id = ?");
        foreach ($items as $item) {
            $newStatus = $item['restore_status'];
            if (in_array($newStatus, ['served', 'fulfilled'])) {
                $stockUpdateStmt->execute([$item['quantity'], $item['sku'], $item['location']]);
            }
            $restoreStmt->execute([$newStatus, $item['id']]);
        }

        // 3. Restore order status
        $pdo->prepare("UPDATE orders SET status = 'active', cancel_reason = NULL WHERE id = ?")->execute([$orderId]);

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => "Order #{$orderId} has been restored."]);
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
    exit;
}

function set_display_month($pdo) {
    if ($_SESSION['role'] !== 'admin') {
        throw new Exception('Admin permission required.');
    }
    $month = $_POST['month'] ?? null;
    $year = $_POST['year'] ?? null;
    if (!$month || !$year) {
        throw new Exception('Month and Year are required.');
    }
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('display_month', ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        $stmt->execute([$month]);
        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('display_year', ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        $stmt->execute([$year]);
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Display month updated successfully.']);
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
    exit;
}

function getDashboardData($pdo) {
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('display_month', 'display_year')");
    $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    $year = $settings['display_year'] ?? date('Y');
    $month = $settings['display_month'] ?? date('m');

    $prev_month = $month - 1;
    $prev_year = $year;
    if ($prev_month == 0) { $prev_month = 12; $prev_year--; }

    $location = $_POST['location'] ?? 'all';
    $bu = $_POST['bu'] ?? 'all';
    $customer = $_POST['customer'] ?? 'all';
    
    // Decode JSON if it's an array of multiple customers
    $customersList = [];
    if ($customer !== 'all') {
        $decoded = json_decode($customer, true);
        if (is_array($decoded)) {
            $customersList = $decoded;
        } else {
            $customersList = [$customer];
        }
    }

    $whereClauses = ["MONTH(o.order_date) = ?", "YEAR(o.order_date) = ?"];
    $params = [$month, $year];

    if ($location !== 'all') { $whereClauses[] = "o.location = ?"; $params[] = $location; }
    if ($bu !== 'all') { $whereClauses[] = "p.bu = ?"; $params[] = $bu; }
    if (!empty($customersList)) {
        $placeholders = implode(',', array_fill(0, count($customersList), '?'));
        $whereClauses[] = "c.name IN ($placeholders)";
        $params = array_merge($params, $customersList);
    }

    $whereSql = 'WHERE ' . implode(' AND ', $whereClauses);
    $baseFrom = "FROM orders o JOIN order_items oi ON o.id = oi.order_id LEFT JOIN customers c ON o.customer_id = c.id LEFT JOIN product_codes pc ON oi.sku = pc.code LEFT JOIN products p ON pc.product_id = p.id";

    // 1. Current Month KPIs & BU Breakdowns
    $stmt = $pdo->prepare("SELECT 
        p.bu, 
        SUM(CASE WHEN oi.status = 'served' THEN (pc.sales_price * oi.quantity) ELSE 0 END) as served_gross,
        SUM(CASE WHEN oi.status = 'served' THEN oi.price ELSE 0 END) as served_net,
        SUM(CASE WHEN oi.status = 'unserved' THEN (pc.sales_price * oi.quantity) ELSE 0 END) as unserved_gross,
        SUM(CASE WHEN oi.status = 'unserved' THEN oi.price ELSE 0 END) as unserved_net,
        SUM(CASE WHEN oi.status = 'served' THEN oi.quantity ELSE 0 END) as served_qty
        $baseFrom $whereSql AND p.bu IS NOT NULL GROUP BY p.bu");
    $stmt->execute($params); 
    $current_bu_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Previous Month Fulfilled & BU Breakdown
    $prevWhereClauses = ["MONTH(o.order_date) = ?", "YEAR(o.order_date) = ?"];
    $prevParams = [$prev_month, $prev_year];
    if ($location !== 'all') { $prevWhereClauses[] = "o.location = ?"; $prevParams[] = $location; }
    if ($bu !== 'all') { $prevWhereClauses[] = "p.bu = ?"; $prevParams[] = $bu; }
    if (!empty($customersList)) {
        $placeholders = implode(',', array_fill(0, count($customersList), '?'));
        $prevWhereClauses[] = "c.name IN ($placeholders)";
        $prevParams = array_merge($prevParams, $customersList);
    }
    $prevWhereSql = 'WHERE ' . implode(' AND ', $prevWhereClauses);

    $stmt = $pdo->prepare("SELECT p.bu, SUM(pc.sales_price * oi.quantity) as prev_gross, SUM(oi.price) as prev_net $baseFrom $prevWhereSql AND oi.status = 'fulfilled' AND p.bu IS NOT NULL GROUP BY p.bu");
    $stmt->execute($prevParams); 
    $prev_bu_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Raw Salesmen Data (For JS Aggregation: BU & Store breakdown)
    $stmt = $pdo->prepare("SELECT o.salesman_name, c.name as customer_name, p.bu, SUM(pc.sales_price * oi.quantity) as total_gross, SUM(oi.price) as total_net $baseFrom $whereSql AND oi.status = 'served' AND o.salesman_name IS NOT NULL AND o.salesman_name != '' GROUP BY o.salesman_name, c.name, p.bu");
    $stmt->execute($params); $salesmen_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. Raw Customers Data (For JS Aggregation: BU breakdown)
    $stmt = $pdo->prepare("SELECT c.name as customer_name, p.bu, SUM(pc.sales_price * oi.quantity) as total_gross, SUM(oi.price) as total_net $baseFrom $whereSql AND oi.status = 'served' AND c.name IS NOT NULL GROUP BY c.name, p.bu");
    $stmt->execute($params); $customers_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 5. Top Served Products (Fetched up to 100 to allow frontend to split Top 10 per BU safely)
    $stmt = $pdo->prepare("SELECT oi.sku, oi.description, p.bu, SUM(oi.quantity) as qty, SUM(pc.sales_price * oi.quantity) as total_gross, SUM(oi.price) as total_net $baseFrom $whereSql AND oi.status = 'served' GROUP BY oi.sku, oi.description, p.bu ORDER BY total_net DESC LIMIT 100");
    $stmt->execute($params); $top_served_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 6. Unserved Items List
    $stmt = $pdo->prepare("SELECT oi.sku, oi.description, p.bu, SUM(oi.quantity) as qty, SUM(pc.sales_price * oi.quantity) as total_gross, SUM(oi.price) as total_net $baseFrom $whereSql AND oi.status = 'unserved' GROUP BY oi.sku, oi.description, p.bu ORDER BY total_net DESC");
    $stmt->execute($params); $unserved_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 7. Cancelled Orders List
    $stmt = $pdo->prepare("SELECT o.po_number, c.name as customer_name, o.customer_address, p.bu, o.cancel_reason, SUM(pc.sales_price * oi.quantity) as total_gross, SUM(oi.price) as total_net $baseFrom $whereSql AND oi.status = 'cancelled' GROUP BY o.po_number, c.name, o.customer_address, p.bu, o.cancel_reason ORDER BY total_net DESC");
    $stmt->execute($params); $cancelled_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 7b. Invoiced vs Open KPIs (current month) - "Invoiced" = order has at least one non-empty SO Number, "Open" = no SO Number yet
    // Build a separate where for orders-only (no item join) so counts are by ORDER not by line item
    $orderOnlyWhere = ["MONTH(o.order_date) = ?", "YEAR(o.order_date) = ?"];
    $orderOnlyParams = [$month, $year];
    if ($location !== 'all') { $orderOnlyWhere[] = "o.location = ?"; $orderOnlyParams[] = $location; }
    if (!empty($customersList)) {
        $ph = implode(',', array_fill(0, count($customersList), '?'));
        $orderOnlyWhere[] = "c.name IN ($ph)";
        $orderOnlyParams = array_merge($orderOnlyParams, $customersList);
    }
    // Exclude cancelled orders from invoiced/open totals
    $orderOnlyWhere[] = "(o.status IS NULL OR o.status <> 'cancelled')";
    $orderOnlyWhereSql = "WHERE " . implode(" AND ", $orderOnlyWhere);

    // Add BU filter through a subquery on order_items if specified
    $buFilterJoin = "";
    if ($bu !== 'all') {
        $buFilterJoin = "AND EXISTS (SELECT 1 FROM order_items oi2 LEFT JOIN product_codes pc2 ON oi2.sku = pc2.code LEFT JOIN products p2 ON pc2.product_id = p2.id WHERE oi2.order_id = o.id AND p2.bu = " . $pdo->quote($bu) . ")";
    }

    $invoicedRegex = "o.so_number REGEXP '[a-zA-Z0-9]'";
    $stmt = $pdo->prepare("SELECT 
            SUM(CASE WHEN $invoicedRegex THEN 1 ELSE 0 END) as invoiced_count,
            SUM(CASE WHEN NOT($invoicedRegex) THEN 1 ELSE 0 END) as open_count,
            COALESCE(SUM(CASE WHEN $invoicedRegex THEN (SELECT SUM(oi3.price) FROM order_items oi3 WHERE oi3.order_id = o.id AND oi3.status <> 'cancelled') ELSE 0 END),0) as invoiced_value,
            COALESCE(SUM(CASE WHEN NOT($invoicedRegex) THEN (SELECT SUM(oi3.price) FROM order_items oi3 WHERE oi3.order_id = o.id AND oi3.status <> 'cancelled') ELSE 0 END),0) as open_value
        FROM orders o LEFT JOIN customers c ON o.customer_id = c.id $orderOnlyWhereSql $buFilterJoin");
    $stmt->execute($orderOnlyParams);
    $invoiced_open_stats = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['invoiced_count'=>0,'open_count'=>0,'invoiced_value'=>0,'open_value'=>0];

    // 8. Previous Month Fulfilled Items List
    $stmt = $pdo->prepare("SELECT oi.sku, c.name as customer_name, o.customer_address, oi.description, p.bu, SUM(oi.quantity) as qty, SUM(pc.sales_price * oi.quantity) as total_gross, SUM(oi.price) as total_net $baseFrom $prevWhereSql AND oi.status = 'fulfilled' GROUP BY oi.sku, c.name, o.customer_address, oi.description, p.bu ORDER BY total_net DESC LIMIT 100");
    $stmt->execute($prevParams); $prev_fulfilled_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ★ NEW: Store LV Limits & Current Sales Utilization (Per BU)
    $stmt = $pdo->prepare("
        SELECT c.name as customer_name, cll.bu, cll.lv_limit,
               (
                   SELECT COALESCE(SUM(oi.price), 0)
                   FROM orders o
                   JOIN order_items oi ON o.id = oi.order_id
                   JOIN product_codes pc ON oi.sku = pc.code
                   JOIN products p ON pc.product_id = p.id
                   WHERE o.customer_id = cll.customer_id
                     AND MONTH(o.order_date) = ? AND YEAR(o.order_date) = ?
                     AND (o.status IS NULL OR o.status <> 'cancelled')
                     AND oi.status = 'served'
                     AND p.bu = cll.bu
               ) as current_sales
        FROM customer_lv_limits cll
        JOIN customers c ON cll.customer_id = c.id
        WHERE cll.lv_limit IS NOT NULL AND cll.lv_limit > 0
        ORDER BY c.name ASC, cll.bu ASC
    ");
    $stmt->execute([$month, $year]);
    $store_lv_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ★ NEW: Top 5 Recent Sales (Synced with Global Filters)
    $recentSalesStmt = $pdo->prepare("
        SELECT o.id, o.po_number, c.name as customer_name, o.order_date,
               SUM(CASE WHEN oi.status = 'served' THEN (pc.sales_price * oi.quantity) ELSE 0 END) as total_gross,
               SUM(CASE WHEN oi.status = 'served' THEN oi.price ELSE 0 END) as total_net
        $baseFrom
        $whereSql AND (o.status IS NULL OR o.status <> 'cancelled')
        GROUP BY o.id, o.po_number, c.name, o.order_date
        ORDER BY o.order_date DESC, o.id DESC
        LIMIT 5
    ");
    $recentSalesStmt->execute($params);
    $recent_sales = $recentSalesStmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => [
        'recent_sales' => $recent_sales,
        'store_lv_stats' => $store_lv_stats,
        'current_bu_stats' => $current_bu_stats,
        'prev_bu_stats' => $prev_bu_stats,
        'salesmen_raw' => $salesmen_raw,
        'customers_raw' => $customers_raw,
        'top_served_products' => $top_served_products,
        'unserved_items' => $unserved_items,
        'cancelled_orders' => $cancelled_orders,
        'prev_fulfilled_items' => $prev_fulfilled_items,
        'invoiced_open_stats' => $invoiced_open_stats,
        'live_encoded_pos' => $live_encoded_pos, // Added for Live Feed table
        'live_po_totals' => $live_po_totals      // Added for Live Count & Amount
    ]]);
    exit;
}


function getRojonDashboardData($pdo) {
    $VAT_RATE = 1.12;

    $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('display_month', 'display_year')");
    $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    $year = $settings['display_year'] ?? date('Y');
    $month = $settings['display_month'] ?? date('m');

    $location = $_POST['location'] ?? 'all';
    $bu = $_POST['bu'] ?? 'all';
    $customer_name = 'ROJON PHARMACY CORPORATION';

    $whereClauses = ["c.name = ?", "YEAR(o.order_date) = ?", "MONTH(o.order_date) = ?"];
    $params = [$customer_name, $year, $month];

    if ($location !== 'all') { $whereClauses[] = "o.location = ?"; $params[] = $location; }
    if ($bu !== 'all') { $whereClauses[] = "p.bu = ?"; $params[] = $bu; }
    $whereSql = 'WHERE ' . implode(' AND ', $whereClauses);

    $baseSql = "SELECT
                    oi.status, oi.price, oi.quantity, oi.sku, oi.description,
                    p.bu, o.id as order_id, o.location, o.customer_address, o.po_number,
                    o.discount_percentage,
                    pc.sales_price, pc.product_id
                FROM orders o
                JOIN order_items oi ON o.id = oi.order_id
                JOIN customers c ON o.customer_id = c.id
                LEFT JOIN product_codes pc ON oi.sku = pc.code
                LEFT JOIN products p ON pc.product_id = p.id $whereSql";

    $stmt = $pdo->prepare($baseSql);
    $stmt->execute(!empty($params) ? $params : []);
    $allItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $productIds = array_unique(array_column($allItems, 'product_id'));
    $barcodeMap = [];
    if (!empty($productIds)) {
        $placeholders = implode(',', array_fill(0, count($productIds), '?'));
        $codeStmt = $pdo->prepare("SELECT product_id, code FROM product_codes WHERE product_id IN ($placeholders) AND type = 'barcode'");
        $codeStmt->execute(array_values($productIds));
        $allBarcodes = $codeStmt->fetchAll(PDO::FETCH_ASSOC);
        foreach($allBarcodes as $bc) {
            if ($bc['product_id']) {
                $barcodeMap[$bc['product_id']] = $bc['code'];
            }
        }
    }

    $stats = [ 'totalServedValue' => 0, 'totalUnservedValue' => 0, 'totalUnservedQty' => 0, 'unservedSkuCount' => 0 ];
    $buPerformance = []; $unservedItems = []; $unservedSkus = []; $fillRateByPo = [];

    foreach ($allItems as $item) {
        if ($item['bu']) {
            if (!isset($buPerformance[$item['bu']])) {
                $buPerformance[$item['bu']] = [
                    'bu' => $item['bu'], 'served_net_vat_in' => 0,
                    'served_gross' => 0, 'unserved' => 0
                ];
            }
            
            $gross_value = (float)($item['sales_price'] ?? 0) * (int)$item['quantity'];

            if ($item['status'] === 'served') {
                $stats['totalServedValue'] += $item['price'];
                $buPerformance[$item['bu']]['served_gross'] += $gross_value;
                $buPerformance[$item['bu']]['served_net_vat_in'] += (float)$item['price'];
            } else {
                $stats['totalUnservedValue'] += $item['price'];
                $buPerformance[$item['bu']]['unserved'] += $gross_value;

                $stats['totalUnservedQty'] += $item['quantity'];
                if (!in_array($item['sku'], $unservedSkus)) { $unservedSkus[] = $item['sku']; }
                if (!isset($unservedItems[$item['sku']])) {
                    $unservedItems[$item['sku']] = [
                        'description' => $item['description'],
                        'sku' => $item['sku'],
                        'barcode' => $item['product_id'] ? ($barcodeMap[$item['product_id']] ?? 'N/A') : 'N/A',
                        'total_qty' => 0, 'total_value' => 0
                    ];
                }
                $unservedItems[$item['sku']]['total_qty'] += $item['quantity'];
                $unservedItems[$item['sku']]['total_value'] += $gross_value;
            }
        }
        if (!isset($fillRateByPo[$item['order_id']])) { $fillRateByPo[$item['order_id']] = ['served_qty' => 0, 'total_qty' => 0]; }
        if ($item['status'] === 'served') { $fillRateByPo[$item['order_id']]['served_qty'] += $item['quantity']; }
        $fillRateByPo[$item['order_id']]['total_qty'] += $item['quantity'];
    }

    foreach ($buPerformance as &$buData) {
        $buData['po_amount_total'] = ($buData['served_gross'] ?? 0) + ($buData['unserved'] ?? 0);
        $net_vat_in = $buData['served_net_vat_in'];
        $buData['served_net_vat_ex'] = $net_vat_in / $VAT_RATE;
        $buData['vat_amount'] = $net_vat_in - $buData['served_net_vat_ex'];
    }
    unset($buData);

    $stats['totalPoValue'] = $stats['totalServedValue'] + $stats['totalUnservedValue']; $stats['unservedSkuCount'] = count($unservedSkus);
    $all_fill_rates = []; foreach($fillRateByPo as $po) { if ($po['total_qty'] > 0) { $all_fill_rates[] = ($po['served_qty'] * 100.0) / $po['total_qty']; } } $stats['quantityFillRateByPo'] = count($all_fill_rates) > 0 ? array_sum($all_fill_rates) / count($all_fill_rates) : 0;
    $finalUnservedItems = array_values($unservedItems);
    usort($finalUnservedItems, function($a, $b) { return $b['total_value'] <=> $a['total_value']; });

    $recentPoSql = "SELECT
                        o.id, o.po_number, o.customer_address, o.so_number, o.order_date,
                        (SELECT SUM(price) FROM order_items WHERE order_id = o.id AND status = 'served') as total_amount
                    FROM orders o
                    JOIN customers c ON o.customer_id = c.id
                    WHERE c.name = ?
                    ORDER BY o.order_date DESC
                    LIMIT 5";
    $stmt = $pdo->prepare($recentPoSql);
    $stmt->execute([$customer_name]);
    $recentPOs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $fulfillableWhereClauses = $whereClauses;
    $fulfillableWhereClauses[] = "oi.status = 'unserved'";
    $fulfillableWhereClauses[] = "il.stock >= oi.quantity";

    $fulfillableWhereSql = 'WHERE ' . implode(' AND ', $fulfillableWhereClauses);

    $fulfillableSkuSql = "SELECT p.bu, o.customer_address, oi.sku, oi.description, pc.sales_price, o.id as order_id, o.po_number, oi.quantity, il.stock as inventory_left FROM order_items oi JOIN orders o ON oi.order_id = o.id JOIN customers c ON o.customer_id = c.id LEFT JOIN product_codes pc ON oi.sku = pc.code LEFT JOIN products p ON pc.product_id = p.id LEFT JOIN inventory_levels il ON oi.sku = il.product_code AND o.location = il.location {$fulfillableWhereSql} ORDER BY p.bu, o.customer_address, oi.description";

    $stmt = $pdo->prepare($fulfillableSkuSql);
    $stmt->execute($params);
    $fulfillableSkus = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $targetParams = [$year, $month]; $targetWhere = '';
    if ($bu !== 'all') { $targetWhere = ' AND bu = ?'; $targetParams[] = $bu; }
    $targetsStmt = $pdo->prepare("SELECT location, bu, target_amount FROM monthly_targets WHERE year = ? AND month = ?{$targetWhere}"); $targetsStmt->execute($targetParams);
    $targets = $targetsStmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);

    $progressParams = [$customer_name, $year, $month]; $progressWhere = '';
    if ($bu !== 'all') { $progressWhere = ' AND p.bu = ?'; $progressParams[] = $bu; }
    $progressSql = "SELECT o.location, p.bu, SUM(oi.price) as current_total FROM orders o JOIN order_items oi ON o.id = oi.order_id JOIN customers c ON o.customer_id = c.id LEFT JOIN product_codes pc ON oi.sku = pc.code LEFT JOIN products p ON pc.product_id = p.id WHERE c.name = ? AND YEAR(o.order_date) = ? AND MONTH(o.order_date) = ?{$progressWhere} GROUP BY o.location, p.bu";
    $progressStmt = $pdo->prepare($progressSql); $progressStmt->execute($progressParams); $progressData = $progressStmt->fetchAll(PDO::FETCH_ASSOC);
    $progress = []; foreach($progressData as $row) { if (!isset($progress[$row['location']])) { $progress[$row['location']] = []; } $progress[$row['location']][$row['bu']] = $row['current_total']; }

    $responseData = [ 'stats' => $stats, 'buPerformance' => array_values($buPerformance), 'unservedItems' => $finalUnservedItems, 'fulfillableSkus' => $fulfillableSkus, 'targets' => $targets, 'progress' => $progress, 'recent_pos' => $recentPOs ];
    echo json_encode(['success' => true, 'data' => $responseData]);
    exit;
}

function getRojonOrders($pdo) {
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('display_month', 'display_year')");
    $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    $year = $settings['display_year'] ?? date('Y');
    $month = $settings['display_month'] ?? date('m');
    $location = $_POST['location'] ?? 'all';
    $bu = $_POST['bu'] ?? 'all';
    $search = $_POST['search'] ?? '';
    $limit = 50;
    $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
    $offset = ($page - 1) * $limit;
    $rojon_customer_id = 34;
    $whereClauses = ["o.customer_id = ?", "YEAR(o.order_date) = ?", "MONTH(o.order_date) = ?"];
    $params = [$rojon_customer_id, $year, $month];
    if ($location !== 'all') { $whereClauses[] = "o.location = ?"; $params[] = $location; }
    if ($bu !== 'all') { $whereClauses[] = "o.bu = ?"; $params[] = $bu; }
    if (!empty($search)) {
        $whereClauses[] = "(o.po_number LIKE ? OR o.so_number LIKE ?)";
        $searchTerm = '%' . $search . '%';
        array_push($params, $searchTerm, $searchTerm);
    }
    $whereSql = 'WHERE ' . implode(' AND ', $whereClauses);
    $countSql = "SELECT COUNT(id) FROM orders o $whereSql";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $totalOrders = $countStmt->fetchColumn();
    $ordersSql = "SELECT id, po_number, order_date, customer_address, so_number FROM orders o $whereSql ORDER BY o.order_date DESC LIMIT ? OFFSET ?";
    $ordersStmt = $pdo->prepare($ordersSql);
    $ordersStmt->execute(array_merge($params, [$limit, $offset]));
    $finalOrders = $ordersStmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode([
        'success' => true, 
        'data' => $finalOrders,
        'pagination' => [
            'total' => (int)$totalOrders,
            'page' => $page,
            'limit' => $limit,
            'totalPages' => ceil($totalOrders / $limit)
        ]
    ]);
    exit;
}

function get_stock_for_product($pdo) {
    // FIX: Look for $_POST first, then fallback to $_GET
    $productId = $_POST['product_id'] ?? $_GET['product_id'] ?? 0;
    
    if (!$productId) {
        echo json_encode(['success' => false, 'message' => 'Product ID required.']);
        exit;
    }
    
    $sql = "SELECT il.product_code, il.location, il.stock
            FROM inventory_levels il
            JOIN product_codes pc ON il.product_code = pc.code
            WHERE pc.product_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$productId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stockBySku = [];
    foreach ($rows as $row) {
        if (!isset($stockBySku[$row['product_code']])) {
            $stockBySku[$row['product_code']] = [];
        }
        $stockBySku[$row['product_code']][$row['location']] = (int)$row['stock'];
    }
    echo json_encode(['success' => true, 'data' => $stockBySku]);
    exit;
}

function find_product_with_best_sku($pdo) {
    $term = $_POST['term'] ?? '';
    $location = $_POST['location'] ?? '';
    $quantityNeeded = (int)($_POST['quantity'] ?? 1); 

    if (empty($term)) {
        echo json_encode(['success' => false, 'message' => 'Search term is required.']);
        exit;
    }

    $productId = null;
    
    if (is_numeric($term)) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE id = ?");
        $stmt->execute([$term]);
        if ($stmt->fetchColumn() > 0) {
            $productId = $term;
        }
        if (!$productId) {
            $stmt = $pdo->prepare("SELECT product_id FROM product_codes WHERE code = ?");
            $stmt->execute([$term]);
            $productId = $stmt->fetchColumn();
        }
    }
    
    if (!$productId) {
        $stmt = $pdo->prepare("SELECT id FROM products WHERE description LIKE ? LIMIT 1");
        $stmt->execute(['%' . $term . '%']);
        $productId = $stmt->fetchColumn();
    }

    if (!$productId) {
        echo json_encode(['success' => false, 'message' => 'Product not found.']);
        exit;
    }

    $productInfoStmt = $pdo->prepare("SELECT description, bu FROM products WHERE id = ?");
    $productInfoStmt->execute([$productId]);
    $productInfo = $productInfoStmt->fetch(PDO::FETCH_ASSOC);

    if (!$productInfo) {
        echo json_encode(['success' => false, 'message' => 'Product details could not be found.']);
        exit;
    }

    $stmt = $pdo->prepare("
         SELECT 
            pc.code, pc.type, pc.sales_price, pc.pieces_per_case, il.stock
        FROM product_codes pc
        LEFT JOIN inventory_levels il ON pc.code = il.product_code AND il.location = ?
        WHERE pc.product_id = ?
        ORDER BY pc.id DESC
    ");
    $stmt->execute([$location, $productId]);
    $allCodes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $bestSku = '';
    $sellableSkus = [];

    foreach ($allCodes as $code) {
        if ($code['type'] === 'sku' && (float)$code['sales_price'] > 0) {
            $sellableSkus[] = [
                'code' => $code['code'],
                'stock' => (int)($code['stock'] ?? 0),
                'price' => (float)$code['sales_price']
            ];
        }
    }

    if (!empty($sellableSkus)) {
        $totalStock = array_sum(array_column($sellableSkus, 'stock'));
        
        if ($totalStock <= 0) {
            usort($sellableSkus, function($a, $b) {
                return $b['price'] <=> $a['price']; 
            });
            $bestSku = $sellableSkus[0]['code'];
        } else {
            $sufficientSkus = array_filter($sellableSkus, function($s) use ($quantityNeeded) {
                return $s['stock'] >= $quantityNeeded;
            });
            
            if (!empty($sufficientSkus)) {
                usort($sufficientSkus, function($a, $b) {
                    return $a['stock'] <=> $b['stock']; 
                });
                $bestSku = reset($sufficientSkus)['code'];
            } else {
                $partialSkus = array_filter($sellableSkus, function($s) {
                    return $s['stock'] > 0;
                });
                usort($partialSkus, function($a, $b) {
                    return $b['stock'] <=> $a['stock']; 
                });
                $bestSku = reset($partialSkus)['code'];
            }
        }
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'productId' => $productId,
            'description' => $productInfo['description'],
            'bu' => $productInfo['bu'],
            'bestSku' => $bestSku,
            'allSkus' => $allCodes
        ]
    ]);
    exit;
}

function toggle_pristine_status($pdo) {
    $orderId = $_POST['order_id'] ?? 0;
    $status = $_POST['status'] ?? 0;
    if (empty($orderId)) {
        throw new Exception('Order ID is required.');
    }
    $stmt = $pdo->prepare("UPDATE orders SET is_pristine_checked = ? WHERE id = ?");
    $stmt->execute([(int)$status, $orderId]);
    echo json_encode(['success' => true, 'message' => 'Pristine status updated.']);
    exit;
}

function search_pos_by_product($pdo) {
    $term = $_POST['term'] ?? '';
    if (strlen($term) < 3) {
        echo json_encode(['success' => true, 'data' => []]);
        exit;
    }

    // --- NEW FILTER LOGIC ---
    $searchTerm = '%' . $term . '%';
    $month = $_POST['month'] ?? 'all';
    $year = $_POST['year'] ?? date('Y');
    $customer = $_POST['customer'] ?? 'all';
    $location = $_POST['location'] ?? 'all';
    $address = $_POST['address'] ?? '';

    $whereClauses = ["(oi.sku LIKE ? OR oi.description LIKE ?)"];
    $params = [$searchTerm, $searchTerm];

    if (!empty($address)) {
        $whereClauses[] = "o.customer_address LIKE ?";
        $params[] = '%' . $address . '%';
    }

    if ($location !== 'all') {
        $whereClauses[] = "o.location = ?";
        $params[] = $location;
    }

    if ($month !== 'all') {
        $whereClauses[] = "MONTH(o.order_date) = ?";
        $params[] = $month;
    }
    if (!empty($year)) {
        $whereClauses[] = "YEAR(o.order_date) = ?";
        $params[] = $year;
    }
    if ($customer !== 'all') {
        $whereClauses[] = "c.name = ?";
        $params[] = $customer;
    }

    $whereSql = implode(' AND ', $whereClauses);
    // --- END NEW LOGIC ---

$sql = "SELECT 
            oi.order_id, oi.sku, oi.description, oi.quantity, oi.status, oi.price,
            o.po_number, o.order_date, o.location,
            c.name as customer_name
        FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            LEFT JOIN customers c ON o.customer_id = c.id
            WHERE $whereSql
            ORDER BY o.order_date DESC
            LIMIT 100";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params); // Use the new params array
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $results]);
    exit;
}

function get_address_by_code($pdo) {
    $code = $_POST['code'] ?? '';
    if (empty($code)) {
        echo json_encode(['success' => false, 'message' => 'Code required']);
        exit;
    }
    $stmt = $pdo->prepare("SELECT customer_code FROM customer_address_codes WHERE address = ?");
    $stmt->execute([$code]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        echo json_encode(['success' => true, 'data' => $result]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Not found']);
    }
    exit;
}

function getAddressCodes($pdo) {
    // Join with customers table to fetch the customer name
    $stmt = $pdo->query("
        SELECT cac.*, c.name as customer_name 
        FROM customer_address_codes cac 
        LEFT JOIN customers c ON cac.customer_id = c.id 
        ORDER BY cac.address ASC
    ");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $results]);
    exit;
}

function addAddressCode($pdo) {
    $customer_id = $_POST['customer_id'] ?? null;
    $address = $_POST['address'] ?? '';
    $customer_code = $_POST['customer_code'] ?? '';
    $salesman_name = $_POST['salesman_name'] ?? null;
    $salesman_code = $_POST['salesman_code'] ?? null;
    $location = $_POST['location'] ?? 'Davao'; // <-- Grabs the new dropdown
    
    if (empty($customer_id) || empty($address) || empty($customer_code)) {
        throw new Exception('Customer, Address, and Customer Code are required.');
    }
    
    $stmt = $pdo->prepare("INSERT INTO customer_address_codes (customer_id, address, customer_code, salesman_name, salesman_code, location) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$customer_id, $address, $customer_code, $salesman_name, $salesman_code, $location]);
    
    // Make sure the main customer table stays synced!
    try { $pdo->prepare("UPDATE customers SET location = ? WHERE id = ?")->execute([$location, $customer_id]); } catch(Exception $e) {}

    echo json_encode(['success' => true, 'message' => 'Address mapping added.']);
    exit;
}

function updateAddressCode($pdo) {
    $id = $_POST['id'] ?? 0;
    $customer_id = $_POST['customer_id'] ?? null;
    $address = $_POST['address'] ?? '';
    $customer_code = $_POST['customer_code'] ?? '';
    $salesman_name = $_POST['salesman_name'] ?? null;
    $salesman_code = $_POST['salesman_code'] ?? null;
    $location = $_POST['location'] ?? 'Davao'; // <-- Grabs the new dropdown
    
    if (empty($id) || empty($customer_id) || empty($address) || empty($customer_code)) {
        throw new Exception('ID, Customer, Address, and Customer Code are required.');
    }
    
    $stmt = $pdo->prepare("UPDATE customer_address_codes SET customer_id = ?, address = ?, customer_code = ?, salesman_name = ?, salesman_code = ?, location = ? WHERE id = ?");
    $stmt->execute([$customer_id, $address, $customer_code, $salesman_name, $salesman_code, $location, $id]);

    // Make sure the main customer table stays synced!
    try { $pdo->prepare("UPDATE customers SET location = ? WHERE id = ?")->execute([$location, $customer_id]); } catch(Exception $e) {}

    echo json_encode(['success' => true, 'message' => 'Address mapping updated.']);
    exit;
}

function deleteAddressCode($pdo) {
    $id = $_POST['id'] ?? 0;
    if (empty($id)) {
        throw new Exception('ID is required.');
    }
    $stmt = $pdo->prepare("DELETE FROM customer_address_codes WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(['success' => true, 'message' => 'Address code deleted.']);
    exit;
}

function getMonthlyTargets($pdo) {
    $stmt = $pdo->query("SELECT * FROM monthly_targets");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $results]);
    exit;
}

function setMonthlyTargets($pdo) {
    $targetsJson = $_POST['targets'] ?? '[]';
    $targets = json_decode($targetsJson, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($targets)) {
        throw new Exception("Invalid targets data provided.");
    }
    $year = date('Y');
    $month = date('m');
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare(
            "INSERT INTO monthly_targets (year, month, location, bu, target_amount) 
             VALUES (?, ?, ?, ?, ?) 
             ON DUPLICATE KEY UPDATE target_amount = VALUES(target_amount)"
        );
        foreach ($targets as $target) {
            $stmt->execute([$year, $month, $target['location'], $target['bu'], $target['amount']]);
        }
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Monthly targets have been saved.']);
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
    exit;
}

function getOrdersForExport($pdo) {
    $exportType = $_POST['export_type'] ?? 'sales';
    $month = $_POST['month'] ?? 0;
    $year = $_POST['year'] ?? 0;
    $location = $_POST['location'] ?? 'all';
    $customer = $_POST['customer'] ?? 'all'; 
    
    if(empty($month) || empty($year)){
        echo json_encode(['success' => false, 'message' => 'Month and year are required for export.']);
        exit;
    }

    $whereClauses = ["MONTH(o.order_date) = ?", "YEAR(o.order_date) = ?"];
    $params = [$month, $year];
    
    if($location !== 'all'){
        $whereClauses[] = "o.location = ?";
        $params[] = $location;
    }

    // Export Type Filtering
    if ($exportType === 'sales') {
        $whereClauses[] = "(o.status IS NULL OR o.status NOT IN ('cancelled', 'deleted'))";
        $whereClauses[] = "(oi.status IS NULL OR oi.status NOT IN ('cancelled', 'deleted'))";
    } else if ($exportType === 'issues') {
        $whereClauses[] = "o.status IN ('cancelled', 'deleted')";
    } else if ($exportType === 'unserved' || $exportType === 'fulfillable') {
        $whereClauses[] = "(o.status IS NULL OR o.status NOT IN ('cancelled', 'deleted'))";
        $whereClauses[] = "oi.status = 'unserved'";
        if ($exportType === 'fulfillable') {
            $whereClauses[] = "(SELECT COALESCE(SUM(il.stock), 0) FROM inventory_levels il JOIN product_codes pc2 ON il.product_code = pc2.code WHERE pc2.product_id = p.id AND il.location = o.location AND pc2.type = 'sku') >= oi.quantity";
        }
    }

    // Decode the JSON array from the frontend checkboxes
    $customersList = [];
    if ($customer !== 'all') {
        $decoded = json_decode($customer, true);
        if (is_array($decoded)) {
            $customersList = $decoded;
        } else {
            $customersList = [$customer];
        }
    }

    // Apply the IN (...) filter for multiple customers
    if (!empty($customersList)) {
        $placeholders = implode(',', array_fill(0, count($customersList), '?'));
        $whereClauses[] = "c.name IN ($placeholders)";
        $params = array_merge($params, $customersList);
    }
    
    $whereSql = "WHERE " . implode(' AND ', $whereClauses);

    $sql = "SELECT
                o.id as order_id, o.po_number, o.order_date, o.location, o.bu,
                o.customer_address, o.customer_code, o.so_number, o.remarks,
                o.discount_percentage, o.salesman_name, o.salesman_code,
                o.cancel_reason, o.status as order_status,
                c.name as customer_name,
                oi.sku, oi.description, oi.quantity, oi.status as item_status,
                pc.sales_price
            FROM orders o
            JOIN customers c ON o.customer_id = c.id
            LEFT JOIN order_items oi ON o.id = oi.order_id
            LEFT JOIN product_codes pc ON oi.sku = pc.code
            LEFT JOIN products p ON pc.product_id = p.id
            $whereSql
            ORDER BY o.order_date ASC, o.id ASC, oi.id ASC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if(empty($results)){
        echo json_encode(['success' => true, 'data' => []]);
        exit;
    }

    $VAT_RATE = 1.12;

    foreach ($results as &$row) {
        $unit_price = (float)($row['sales_price'] ?? 0);
        $quantity = (int)($row['quantity'] ?? 0);
        $discount = (float)($row['discount_percentage'] ?? 0);

        $gross_price = $unit_price * $quantity;
        $final_price = $gross_price * (1 - ($discount / 100));
        
        $row['vat_ex_wo_disc'] = $gross_price / $VAT_RATE;
        $row['vat_in_wo_disc'] = $gross_price;
        $row['vat_ex_w_disc']  = $final_price / $VAT_RATE;
        $row['vat_in_w_disc']  = $final_price;
        
        $row['price'] = $final_price;
    }
    unset($row);
    
    echo json_encode(['success' => true, 'data' => $results]);
    exit;
}

function getSalesSummaryData($pdo) {
    $VAT_RATE = 1.12;

    $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('display_month', 'display_year')");
    $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    $year = $settings['display_year'] ?? date('Y');
    $month = $settings['display_month'] ?? date('m');

    $location = $_POST['location'] ?? 'all';
    $bu_filter = $_POST['bu'] ?? 'all';
    $customer_name = $_POST['customer'] ?? 'all';

    $whereClauses = ["YEAR(o.order_date) = ?", "MONTH(o.order_date) = ?"];
    $params = [$year, $month];

    if ($location !== 'all') { $whereClauses[] = "o.location = ?"; $params[] = $location; }
    if ($bu_filter !== 'all') { $whereClauses[] = "p.bu = ?"; $params[] = $bu_filter; }
    if ($customer_name !== 'all') { $whereClauses[] = "c.name = ?"; $params[] = $customer_name; }

    $whereSql = 'WHERE ' . implode(' AND ', $whereClauses);

    $baseSql = "SELECT
                    oi.status, oi.quantity, p.bu,
                    pc.sales_price, o.discount_percentage 
                FROM orders o
                JOIN order_items oi ON o.id = oi.order_id
                JOIN customers c ON o.customer_id = c.id
                LEFT JOIN product_codes pc ON oi.sku = pc.code
                LEFT JOIN products p ON pc.product_id = p.id $whereSql";

    $stmt = $pdo->prepare($baseSql);
    $stmt->execute($params);
    $allItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $buPerformance = [];

    foreach ($allItems as $item) {
        if ($item['bu']) {
            if (!isset($buPerformance[$item['bu']])) {
                $buPerformance[$item['bu']] = [
                    'bu' => $item['bu'],
                    // TOTALS
                    'total_gross' => 0, 'total_net' => 0, 'total_pristine' => 0,
                    // SERVED
                    'served_gross' => 0, 'served_net' => 0, 'served_pristine' => 0,
                    // UNSERVED
                    'unserved_gross' => 0, 'unserved_net' => 0, 'unserved_pristine' => 0
                ];
            }
            
            $gross_value = (float)($item['sales_price'] ?? 0) * (int)$item['quantity'];
            $discount = (float)($item['discount_percentage'] ?? 0);
            $net_value = $gross_value * (1 - ($discount / 100));
            $pristine_value = $gross_value / $VAT_RATE;

            // Add to TOTALS
            $buPerformance[$item['bu']]['total_gross'] += $gross_value;
            $buPerformance[$item['bu']]['total_net'] += $net_value;
            $buPerformance[$item['bu']]['total_pristine'] += $pristine_value;

            // Add to SERVED or UNSERVED
            if ($item['status'] === 'served') {
                $buPerformance[$item['bu']]['served_gross'] += $gross_value;
                $buPerformance[$item['bu']]['served_net'] += $net_value;
                $buPerformance[$item['bu']]['served_pristine'] += $pristine_value;
            } else {
                $buPerformance[$item['bu']]['unserved_gross'] += $gross_value;
                $buPerformance[$item['bu']]['unserved_net'] += $net_value;
                $buPerformance[$item['bu']]['unserved_pristine'] += $pristine_value;
            }
        }
    }
    
    echo json_encode(['success' => true, 'data' => array_values($buPerformance)]);
    exit;
}
function getAddressSuggestions($pdo) {
    $term = $_POST['term'] ?? '';
    $customerId = $_POST['customer_id'] ?? '';

    // If no customer is selected, require 2 characters. 
    // If customer IS selected, allow 0 characters (so they can see list immediately if frontend logic permits)
    if (empty($customerId) && strlen($term) < 2) {
        echo json_encode(['success' => true, 'data' => []]);
        exit;
    }
    
    $sql = "SELECT address, customer_code FROM customer_address_codes WHERE 1=1";
    $params = [];

    // Filter by Customer ID if provided
    if (!empty($customerId)) {
        $sql .= " AND customer_id = ?";
        $params[] = $customerId;
    }

    // Filter by Search Term if provided
    if (!empty($term)) {
        $sql .= " AND address LIKE ?";
        $params[] = '%' . $term . '%';
    }

    $sql .= " ORDER BY address ASC LIMIT 20";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $results]);
    exit;
}

function get_product_suggestions($pdo) {
    $term = $_POST['term'] ?? '';
    $bu = $_POST['bu'] ?? '';
    if (strlen($term) < 2) {
        echo json_encode(['success' => true, 'data' => []]);
        exit;
    }
    
    $searchTerm = '%' . $term . '%';
    $params = [$searchTerm, $searchTerm];

    // This query now correctly finds a product by its description, SKU, OR barcode.
    $sql = "SELECT DISTINCT
                p.id,
                p.description,
                (SELECT code FROM product_codes WHERE product_id = p.id AND type='sku' LIMIT 1) as sku,
                (SELECT code FROM product_codes WHERE product_id = p.id AND type='barcode' LIMIT 1) as barcode
            FROM products p
            JOIN product_codes pc ON p.id = pc.product_id
            WHERE (p.description LIKE ? OR pc.code LIKE ?)";

    if (!empty($bu)) {
        $sql .= " AND p.bu = ?";
        $params[] = $bu;
    }

    $sql .= " LIMIT 10";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $results]);
    exit;
}

function bulkAddStock($pdo) {
    // FUNCTION 1: EXPECTS PRICE (SKU ... Qty Price)
    $data = $_POST['data'] ?? '';
    $location = $_POST['location'] ?? '';

    if(empty($data) || empty($location)) {
        throw new Exception('No stock data or location provided.');
    }

    $lines = explode("\n", trim($data));
    $notFoundCodes = [];
    $processedCount = 0;

    // FIX (double-add bug): Aggregate by SKU FIRST so duplicate lines are summed once.
    $aggregated = [];
    foreach ($lines as $line) {
        if (empty(trim($line))) continue;
        $parts = preg_split('/\s+/', trim($line), -1, PREG_SPLIT_NO_EMPTY);
        if (count($parts) < 3) continue; // Needs at least 3 parts: SKU, Qty, Price
        $code = $parts[0];
        $quantityStr = str_replace(',', '', $parts[count($parts) - 2]);
        $priceStr    = str_replace(',', '', $parts[count($parts) - 1]);
        if (!is_numeric($code) || !is_numeric($quantityStr) || !is_numeric($priceStr)) continue;
        $code = (string)$code;
        if (!isset($aggregated[$code])) {
            $aggregated[$code] = ['qty' => 0, 'price' => (float)$priceStr];
        }
        $aggregated[$code]['qty']   += (int)$quantityStr;
        $aggregated[$code]['price']  = (float)$priceStr; // most recent price wins
    }

    $pdo->beginTransaction();
    try {
        $checkCodeStmt = $pdo->prepare("SELECT COUNT(*) FROM product_codes WHERE code = ?");
        $stockAddStmt = $pdo->prepare("INSERT INTO inventory_levels (product_code, location, stock) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE stock = stock + VALUES(stock), last_updated = NOW()");
        $priceUpdateStmt = $pdo->prepare("UPDATE product_codes SET sales_price = ? WHERE code = ?");

        foreach ($aggregated as $code => $info) {
            $checkCodeStmt->execute([$code]);
            if ($checkCodeStmt->fetchColumn() > 0) {
                $stockAddStmt->execute([$code, $location, $info['qty']]);
                $priceUpdateStmt->execute([$info['price'], $code]);
                $processedCount++;
            } else {
                $notFoundCodes[] = $code;
            }
        }
        $pdo->commit();
        $message = "$processedCount items added (Stock & Price) to '{$location}'.";
        if (!empty($notFoundCodes)) $message .= " Skipped: " . implode(', ', array_unique($notFoundCodes));
        echo json_encode(['success' => true, 'message' => $message]);
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
    exit;
}

function bulkAddStockNoPrice($pdo) {
    // FUNCTION 2: QTY ONLY (SKU ... Description ... Qty)
    $data = $_POST['data'] ?? '';
    $location = $_POST['location'] ?? '';

    if(empty($data) || empty($location)) {
        throw new Exception('No stock data or location provided.');
    }

    $lines = explode("\n", trim($data));
    $notFoundCodes = [];
    $processedCount = 0;

    // FIX (double-add bug): Aggregate by SKU FIRST so duplicate lines are summed once.
    $aggregated = [];
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        $parts = preg_split('/\s+/', $line, -1, PREG_SPLIT_NO_EMPTY);
        if (count($parts) < 2) continue;
        $code = $parts[0];
        $quantityStr = array_pop($parts);
        $quantityStr = str_replace(',', '', $quantityStr);
        if (!is_numeric($code) || !is_numeric($quantityStr)) continue;
        $code = (string)$code;
        if (!isset($aggregated[$code])) $aggregated[$code] = 0;
        $aggregated[$code] += (float)$quantityStr;
    }

    $pdo->beginTransaction();
    try {
        $checkCodeStmt = $pdo->prepare("SELECT COUNT(*) FROM product_codes WHERE code = ?");
        // Updates stock ONLY
        $stockAddStmt = $pdo->prepare("INSERT INTO inventory_levels (product_code, location, stock) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE stock = stock + VALUES(stock), last_updated = NOW()");

        foreach ($aggregated as $code => $quantity) {
            $checkCodeStmt->execute([$code]);
            if ($checkCodeStmt->fetchColumn() > 0) {
                $stockAddStmt->execute([$code, $location, $quantity]);
                $processedCount++;
            } else {
                $notFoundCodes[] = $code;
            }
        }
        $pdo->commit();
        $message = "$processedCount items added (Stock Only) to '{$location}'.";
        if (!empty($notFoundCodes)) $message .= " Skipped: " . implode(', ', array_unique($notFoundCodes));
        echo json_encode(['success' => true, 'message' => $message]);
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
    exit;
}




function getDraftOrders($pdo) {
    // This fetches all orders containing 'draft' items and checks if they have stock now
    $location = $_POST['location'] ?? 'all';
    $bu = $_POST['bu'] ?? 'all';
    
    $whereClauses = ["oi.status = 'draft'"];
    $params = [];

    if ($location !== 'all') { $whereClauses[] = "o.location = ?"; $params[] = $location; }
    if ($bu !== 'all') { $whereClauses[] = "p.bu = ?"; $params[] = $bu; }

    $whereSql = "WHERE " . implode(" AND ", $whereClauses);

    $sql = "SELECT 
                o.id as order_id, o.po_number, o.so_number, o.order_date, o.location, o.customer_address,
                c.name as customer_name,
                oi.sku, oi.description, oi.quantity, oi.price,
                p.bu,
                (SELECT stock FROM inventory_levels il WHERE il.product_code = oi.sku AND il.location = o.location) as current_stock
            FROM orders o
            JOIN order_items oi ON o.id = oi.order_id
            JOIN customers c ON o.customer_id = c.id
            LEFT JOIN product_codes pc ON oi.sku = pc.code
            LEFT JOIN products p ON pc.product_id = p.id
            $whereSql
            ORDER BY o.order_date DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $rows]);
    exit;
}

function updateSingleInventory($pdo) {
    $sku = $_POST['sku'] ?? '';
    $location = $_POST['location'] ?? '';
    $stock = $_POST['stock'] ?? null;
    $pcsPerCase = $_POST['pcs_per_case'] ?? null;

    if (empty($sku) || empty($location) || $stock === null || $pcsPerCase === null) {
        throw new Exception("Missing required fields (SKU, Location, Stock, or Pcs/Case).");
    }

    $pdo->beginTransaction();
    try {
        // 1. Update Stock Level
        $stockStmt = $pdo->prepare("
            INSERT INTO inventory_levels (product_code, location, stock, last_updated) 
            VALUES (?, ?, ?, NOW()) 
            ON DUPLICATE KEY UPDATE stock = VALUES(stock), last_updated = NOW()
        ");
        $stockStmt->execute([$sku, $location, $stock]);

        // 2. Update Pieces Per Case (Conversion)
        $conversionStmt = $pdo->prepare("UPDATE product_codes SET pieces_per_case = ? WHERE code = ?");
        $conversionStmt->execute([$pcsPerCase, $sku]);

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Inventory updated successfully.']);
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
    exit;
    
   
}
function updateInventoryRow($pdo) {
    $sku = $_POST['sku'] ?? '';
    $pcs = $_POST['pcs'] ?? 1;
    $stockDavao = $_POST['stock_davao'] ?? 0;
    $stockGensan = $_POST['stock_gensan'] ?? 0;

    if (empty($sku)) {
        throw new Exception("SKU is required.");
    }

    $pdo->beginTransaction();
    try {
        // 1. Update Pcs Per Case
        $stmt = $pdo->prepare("UPDATE product_codes SET pieces_per_case = ? WHERE code = ?");
        $stmt->execute([$pcs, $sku]);

        // 2. Update Davao Stock
        $stmt = $pdo->prepare("INSERT INTO inventory_levels (product_code, location, stock, last_updated) VALUES (?, 'Davao', ?, NOW()) ON DUPLICATE KEY UPDATE stock = VALUES(stock), last_updated = NOW()");
        $stmt->execute([$sku, $stockDavao]);

        // 3. Update Gensan Stock
        $stmt = $pdo->prepare("INSERT INTO inventory_levels (product_code, location, stock, last_updated) VALUES (?, 'Gensan', ?, NOW()) ON DUPLICATE KEY UPDATE stock = VALUES(stock), last_updated = NOW()");
        $stmt->execute([$sku, $stockGensan]);

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Inventory updated successfully.']);
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
    exit;
}

function updateOrderDate($pdo) {
    $orderId = $_POST['order_id'] ?? '';
    $newDate = $_POST['new_date'] ?? '';

    if (empty($orderId) || empty($newDate)) {
        throw new Exception("Order ID and New Date are required.");
    }

    $stmt = $pdo->prepare("UPDATE orders SET order_date = ? WHERE id = ?");
    $stmt->execute([$newDate, $orderId]);

    echo json_encode(['success' => true, 'message' => 'Order date updated successfully.']);
    exit;
}

function updatePoNumber($pdo) {
    $orderId = $_POST['order_id'] ?? '';
    $newPo = $_POST['new_po'] ?? '';

    if (empty($orderId) || empty($newPo)) {
        throw new Exception("Order ID and New PO Number are required.");
    }

    $stmt = $pdo->prepare("UPDATE orders SET po_number = ? WHERE id = ?");
    $stmt->execute([$newPo, $orderId]);

    echo json_encode(['success' => true, 'message' => 'PO Number updated successfully.']);
    exit;
}

// =================================================================
// NEW PRODUCT MANAGEMENT FUNCTIONS
// =================================================================

function getAdminProducts($pdo) {
    // 1. Fetch products
    $stmt = $pdo->prepare("SELECT * FROM products ORDER BY description ASC");
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Fetch all codes/SKUs to map them efficiently
    // (Fetching all at once is more efficient than looping queries)
    $skuStmt = $pdo->prepare("SELECT * FROM product_codes");
    $skuStmt->execute();
    $allCodes = $skuStmt->fetchAll(PDO::FETCH_ASSOC);

    // Group codes by product_id
    $codesByProduct = [];
    foreach ($allCodes as $code) {
        $codesByProduct[$code['product_id']][] = $code;
    }

    // Attach codes to products
    foreach ($products as &$product) {
        $product['codes'] = $codesByProduct[$product['id']] ?? [];
    }

    echo json_encode(['success' => true, 'data' => $products]);
    exit;
}

function getProductDetails($pdo) {
    $id = $_GET['id'] ?? $_POST['id'] ?? null;
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Product ID required']);
        exit;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit;
    }

    $skuStmt = $pdo->prepare("SELECT * FROM product_codes WHERE product_id = ?");
    $skuStmt->execute([$id]);
    $product['codes'] = $skuStmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $product]);
    exit;
}

function saveProductBase($pdo) {
    $id = $_POST['id'] ?? null;
    $description = trim($_POST['description'] ?? '');
    $bu = trim($_POST['bu'] ?? '');

    if (empty($description) || empty($bu)) {
        echo json_encode(['success' => false, 'message' => 'Description and BU are required']);
        exit;
    }

    try {
        if ($id) {
            // Update
            $stmt = $pdo->prepare("UPDATE products SET description = ?, bu = ? WHERE id = ?");
            $stmt->execute([$description, $bu, $id]);
            echo json_encode(['success' => true, 'message' => 'Product updated successfully']);
        } else {
            // Insert
            $stmt = $pdo->prepare("INSERT INTO products (description, bu) VALUES (?, ?)");
            $stmt->execute([$description, $bu]);
            echo json_encode(['success' => true, 'message' => 'Product created successfully', 'new_id' => $pdo->lastInsertId()]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]);
    }
    exit;
}

function deleteProductFull($pdo) {
    $id = $_POST['id'] ?? null;
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'ID required']);
        exit;
    }

    try {
        $pdo->beginTransaction();
        
        // Delete SKUs first (if cascade isn't set up, though your SQL likely has it)
        $pdo->prepare("DELETE FROM product_codes WHERE product_id = ?")->execute([$id]);
        
        // Delete Product
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);
        
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Product and associated SKUs deleted']);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

function saveSku($pdo) {
    $id = $_POST['sku_id'] ?? null;
    $product_id = $_POST['product_id'] ?? null;
    $code = trim($_POST['code'] ?? '');
    $type = $_POST['type'] ?? 'sku';
    $ppc = $_POST['pieces_per_case'] ?? 1;
    $price = $_POST['sales_price'] ?? 0.00;

    if (empty($code) || empty($product_id)) {
        echo json_encode(['success' => false, 'message' => 'Product ID and Code are required']);
        exit;
    }

    try {
        if ($id) {
            // Update
            $stmt = $pdo->prepare("UPDATE product_codes SET code = ?, type = ?, pieces_per_case = ?, sales_price = ? WHERE id = ?");
            $stmt->execute([$code, $type, $ppc, $price, $id]);
            echo json_encode(['success' => true, 'message' => 'SKU updated']);
        } else {
            // Insert
            // Check for duplicates
            $check = $pdo->prepare("SELECT id FROM product_codes WHERE code = ?");
            $check->execute([$code]);
            if($check->rowCount() > 0) {
                 echo json_encode(['success' => false, 'message' => 'This Code/SKU already exists in the system.']);
                 exit;
            }

            $stmt = $pdo->prepare("INSERT INTO product_codes (product_id, code, type, pieces_per_case, sales_price) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$product_id, $code, $type, $ppc, $price]);
            echo json_encode(['success' => true, 'message' => 'SKU added']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

function deleteSkuById($pdo) {
    $id = $_POST['id'] ?? null;
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'ID required']);
        exit;
    }
    try {
        $stmt = $pdo->prepare("DELETE FROM product_codes WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true, 'message' => 'SKU deleted']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

function bulkUpdateSalesmen($pdo) {
    $data = $_POST['data'] ?? '';
    if(empty($data)) { throw new Exception('No data provided.'); }
    
    $lines = explode("\n", trim($data));
    $processedCount = 0;
    $notFoundCodes = []; // Array to track missing codes
    
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("UPDATE customer_address_codes SET salesman_name = ?, salesman_code = ? WHERE customer_code = ?");
        foreach ($lines as $line) {
            if (empty(trim($line))) continue;
            // Split by tabs or multiple spaces
            $parts = preg_split('/\t| {2,}/', trim($line), -1, PREG_SPLIT_NO_EMPTY);
            
            if (count($parts) >= 3) {
                $customerCode = trim($parts[0]);
                $salesmanName = trim($parts[1]);
                $salesmanCode = trim($parts[2]);
                
                $stmt->execute([$salesmanName, $salesmanCode, $customerCode]);
                
                // If rowCount is 0, it means the code wasn't found in the database
                if ($stmt->rowCount() > 0) { 
                    $processedCount++; 
                } else {
                    $notFoundCodes[] = $customerCode;
                }
            }
        }
        $pdo->commit();
        
        // Build the final message
        $message = "$processedCount addresses updated successfully.";
        
        // If there are codes that weren't found, append them to the alert
        if (!empty($notFoundCodes)) {
            $message .= "\n\nThe following Customer Codes were NOT FOUND and skipped:\n" . implode(', ', array_unique($notFoundCodes));
        }
        
        echo json_encode(['success' => true, 'message' => $message]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
    exit;
}

function getNpImportData($pdo) {
    // Replace the 'T' from datetime-local with a space for MySQL format
    $start = str_replace('T', ' ', $_POST['start_date'] ?? '');
    $end = str_replace('T', ' ', $_POST['end_date'] ?? '');
    $customer = $_POST['customer'] ?? 'all';
    $location = $_POST['location'] ?? 'all';
    $status = $_POST['status'] ?? 'served';
    $bu = $_POST['bu'] ?? 'all';
    
    if(empty($start) || empty($end)) {
        echo json_encode(['success' => false, 'message' => 'Start and End dates are required.']);
        exit;
    }
    
    $whereClauses = ["o.order_date >= ?", "o.order_date <= ?"];
    $params = [$start, $end];
    
    // Apply Status Filter to the ORDER (so we fetch all items of the order to preserve 1-12 indexing)
    if ($status !== 'all') {
        $whereClauses[] = "EXISTS (SELECT 1 FROM order_items sq_oi WHERE sq_oi.order_id = o.id AND sq_oi.status = ?)";
        $params[] = $status;
    }

    // Apply Location filter
    if ($location !== 'all') {
        $whereClauses[] = "o.location = ?";
        $params[] = $location;
    }
    
    // Apply BU filter
    if ($bu !== 'all') {
        $whereClauses[] = "p.bu = ?";
        $params[] = $bu;
    }
    
    // Decode the JSON array from the frontend checkboxes
    $customersList = [];
    if ($customer !== 'all') {
        $decoded = json_decode($customer, true);
        if (is_array($decoded)) {
            $customersList = $decoded;
        } else {
            $customersList = [$customer];
        }
    }

    // Apply the IN (...) filter for multiple customers
    if (!empty($customersList)) {
        $placeholders = implode(',', array_fill(0, count($customersList), '?'));
        $whereClauses[] = "c.name IN ($placeholders)";
        $params = array_merge($params, $customersList);
    }
    
    $whereSql = "WHERE " . implode(' AND ', $whereClauses);
    
    // We fetch ALL items to preserve original 1-12 chunking, but include status to filter later
    $sql = "SELECT 
                o.order_date, o.customer_code, o.salesman_code, o.po_number, p.bu,
                oi.sku, oi.quantity, oi.status, o.id as order_id
            FROM orders o
            JOIN order_items oi ON o.id = oi.order_id
            JOIN customers c ON o.customer_id = c.id
            LEFT JOIN product_codes pc ON oi.sku = pc.code
            LEFT JOIN products p ON pc.product_id = p.id
            $whereSql
            ORDER BY o.order_date ASC, o.id ASC, oi.id ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $results]);
    exit;
}

function importDatabase($pdo) {
    if (!isset($_FILES['sql_file']) || $_FILES['sql_file']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("No valid file uploaded. (Check your server's upload size limits).");
    }

    $fileTmpPath = $_FILES['sql_file']['tmp_name'];
    $sql = file_get_contents($fileTmpPath);

    if (empty($sql)) {
        throw new Exception("The uploaded file is empty.");
    }

    try {
        // Disable foreign key checks to prevent dropping errors if tables depend on each other
        $pdo->exec("SET FOREIGN_KEY_CHECKS=0;");
        
        // Execute the entire SQL dump. 
        $pdo->exec($sql);
        
        // Re-enable foreign key checks
        $pdo->exec("SET FOREIGN_KEY_CHECKS=1;");
        
        echo json_encode(['success' => true, 'message' => 'Database successfully refreshed from file!']);
    } catch (Exception $e) {
        // Try to re-enable foreign keys just in case it failed midway
        $pdo->exec("SET FOREIGN_KEY_CHECKS=1;");
        throw new Exception("Error importing database: " . $e->getMessage());
    }
    exit;
}
function getFulfilledOrders($pdo) {
    $month = $_POST['month'] ?? 'all';
    $year = $_POST['year'] ?? date('Y');
    $location = $_POST['location'] ?? 'all';
    $customer = $_POST['customer'] ?? 'all';
    $bu = $_POST['bu'] ?? 'all';

    // We only want items that are marked as 'fulfilled'
    $whereClauses = ["oi.status = 'fulfilled'"];
    $params = [];

    if ($month !== 'all') {
        $whereClauses[] = "MONTH(o.order_date) = ?";
        $params[] = $month;
    }
    if (!empty($year)) {
        $whereClauses[] = "YEAR(o.order_date) = ?";
        $params[] = $year;
    }
    if ($location !== 'all') {
        $whereClauses[] = "o.location = ?";
        $params[] = $location;
    }
    if ($customer !== 'all') {
        $whereClauses[] = "c.name = ?";
        $params[] = $customer;
    }
    if ($bu !== 'all') {
        $whereClauses[] = "p.bu = ?";
        $params[] = $bu;
    }

    $whereSql = "WHERE " . implode(" AND ", $whereClauses);

    $sql = "SELECT 
                o.id as order_id, o.po_number, o.so_number, o.order_date, o.location, o.customer_address,
                c.name as customer_name,
                oi.sku, oi.description, oi.quantity, oi.price,
                p.bu,
                (SELECT COUNT(*) FROM order_items oi2 WHERE oi2.order_id = o.id) as total_items_count
            FROM orders o
            JOIN order_items oi ON o.id = oi.order_id
            JOIN customers c ON o.customer_id = c.id
            LEFT JOIN product_codes pc ON oi.sku = pc.code
            LEFT JOIN products p ON pc.product_id = p.id
            $whereSql
            ORDER BY o.order_date ASC, o.id ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Group the items by their Order ID so we display distinct PO cards
    $orders = [];
    foreach ($rows as $row) {
        $oid = $row['order_id'];
        if (!isset($orders[$oid])) {
            $orders[$oid] = [
                'order_id' => $oid,
                'po_number' => $row['po_number'],
                'so_number' => $row['so_number'] ?? null,
                'total_items_count' => $row['total_items_count'] ?? 0,
                'customer_name' => $row['customer_name'],
                'order_date' => $row['order_date'],
                'location' => $row['location'],
                'items' => []
            ];
        }
        $orders[$oid]['items'][] = $row;
    }

    // ★ NEW: Compute aggregate totals STRICTLY for the filtered fulfilled items
    $totals = [
        'total_pos'    => count($orders),
        'total_qty'    => 0,
        'total_amount' => 0,
    ];

    foreach ($rows as $r) {
        $totals['total_qty']    += (int)$r['quantity'];
        $totals['total_amount'] += (float)$r['price'];
    }
    
    // array_values resets the keys for JSON encoding
    echo json_encode(['success' => true, 'data' => array_values($orders), 'totals' => $totals]);
    exit;
}

function getAdvancedDashboardData($pdo) {
    // Get currently viewed month/year from settings
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('display_month', 'display_year')");
    $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    $year = $settings['display_year'] ?? date('Y');
    $month = $settings['display_month'] ?? date('m');

    // Calculate previous month and year
    $prev_month = $month - 1;
    $prev_year = $year;
    if ($prev_month == 0) {
        $prev_month = 12;
        $prev_year--;
    }

    $response = [];
    $baseFrom = "FROM orders o JOIN order_items oi ON o.id = oi.order_id LEFT JOIN product_codes pc ON oi.sku = pc.code LEFT JOIN products p ON pc.product_id = p.id";

    // 1. Sales on NUTRI, HEALTH, HYGIENE
    $stmt = $pdo->prepare("SELECT p.bu, SUM(oi.price) as total $baseFrom WHERE oi.status = 'served' AND MONTH(o.order_date) = ? AND YEAR(o.order_date) = ? AND p.bu IS NOT NULL GROUP BY p.bu");
    $stmt->execute([$month, $year]);
    $response['bu_sales'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Top Sales from Salesman
    $stmt = $pdo->prepare("SELECT o.salesman_name, SUM(oi.price) as total $baseFrom WHERE oi.status = 'served' AND MONTH(o.order_date) = ? AND YEAR(o.order_date) = ? AND o.salesman_name IS NOT NULL AND o.salesman_name != '' GROUP BY o.salesman_name ORDER BY total DESC LIMIT 5");
    $stmt->execute([$month, $year]);
    $response['top_salesmen'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. UNSERVES of NUTRI, HEALTH, HYGIENE
    $stmt = $pdo->prepare("SELECT p.bu, SUM(oi.price) as total $baseFrom WHERE oi.status = 'unserved' AND MONTH(o.order_date) = ? AND YEAR(o.order_date) = ? AND p.bu IS NOT NULL GROUP BY p.bu");
    $stmt->execute([$month, $year]);
    $response['bu_unserved'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. TOP SERVED of NUTRI, HEALTH, HYGIENE
    $stmt = $pdo->prepare("SELECT oi.description, p.bu, SUM(oi.quantity) as total_qty, SUM(oi.price) as total $baseFrom WHERE oi.status = 'served' AND MONTH(o.order_date) = ? AND YEAR(o.order_date) = ? GROUP BY oi.sku, oi.description, p.bu ORDER BY total DESC LIMIT 10");
    $stmt->execute([$month, $year]);
    $response['top_served_products'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 5. FULFILLED from Previous Month
    $stmt = $pdo->prepare("SELECT p.bu, SUM(oi.price) as total $baseFrom WHERE oi.status = 'served' AND MONTH(o.order_date) = ? AND YEAR(o.order_date) = ? AND p.bu IS NOT NULL GROUP BY p.bu");
    $stmt->execute([$prev_month, $prev_year]);
    $response['prev_month_fulfilled'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 6. Top Sales from Customers
    $stmt = $pdo->prepare("SELECT c.name as customer_name, SUM(oi.price) as total FROM orders o JOIN order_items oi ON o.id = oi.order_id JOIN customers c ON o.customer_id = c.id WHERE oi.status = 'served' AND MONTH(o.order_date) = ? AND YEAR(o.order_date) = ? GROUP BY c.id, c.name ORDER BY total DESC LIMIT 5");
    $stmt->execute([$month, $year]);
    $response['top_customers'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $response]);
    exit;
}

function getUnservedItemsFlat($pdo) {
    $month = $_POST['month'] ?? 'all';
    $year  = $_POST['year']  ?? 'all';
    $where = ["oi.status = 'unserved'", "o.status != 'cancelled'"];
    $params = [];
    if ($month !== 'all') { $where[] = 'MONTH(o.order_date) = ?'; $params[] = $month; }
    if ($year  !== 'all') { $where[] = 'YEAR(o.order_date) = ?';  $params[] = $year;  }
    $sql = "SELECT c.name as customer, o.po_number, oi.sku, oi.description, oi.quantity, oi.price
              FROM orders o
              JOIN order_items oi ON o.id = oi.order_id
              JOIN customers c ON o.customer_id = c.id
              WHERE " . implode(' AND ', $where) . "
              ORDER BY c.name ASC, o.po_number ASC, oi.description ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
    exit;
}

function ensureUserColumns($pdo) {
    $existing = $pdo->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('display_name', $existing)) { try { $pdo->exec("ALTER TABLE users ADD COLUMN display_name VARCHAR(100) DEFAULT NULL"); } catch(\Exception $e) {} }
    if (!in_array('avatar_url',   $existing)) { try { $pdo->exec("ALTER TABLE users ADD COLUMN avatar_url VARCHAR(255) DEFAULT NULL"); }   catch(\Exception $e) {} }
    if (!in_array('created_at',   $existing)) { try { $pdo->exec("ALTER TABLE users ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP"); } catch(\Exception $e) {} }
    return $pdo->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN);
}

function getUsers($pdo) {
    $cols = ensureUserColumns($pdo);
    $displayCol  = in_array('display_name', $cols) ? "COALESCE(display_name, username)" : "username";
    $avatarCol    = in_array('avatar_url',   $cols) ? "avatar_url" : "NULL";
    $createdCol   = in_array('created_at',   $cols) ? "created_at" : "NULL";
    $stmt = $pdo->query("SELECT id, username, role, {$displayCol} as display_name, {$avatarCol} as avatar_url, {$createdCol} as created_at FROM users ORDER BY id ASC");
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
    exit;
}

function handleAvatarUpload() {
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $dir = __DIR__ . '/uploads/avatars/';
        if (!is_dir($dir)) mkdir($dir, 0777, true);
        $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $filename = uniqid('avatar_') . '.' . $ext;
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $dir . $filename)) {
                return 'uploads/avatars/' . $filename;
            }
        }
    }
    return null;
}

function addUser($pdo) {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role = $_POST['role'] ?? 'encoder';
    $display_name = trim($_POST['display_name'] ?? '');
    if (empty($username) || empty($password)) { throw new Exception('Username and password are required.'); }
    $check = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $check->execute([$username]);
    if ($check->fetch()) { echo json_encode(['success' => false, 'message' => 'Username already exists.']); exit; }
    
    $avatar_url = handleAvatarUpload();
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $cols2 = ensureUserColumns($pdo);
    
    if (in_array('display_name', $cols2)) {
        $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role, display_name, avatar_url) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$username, $hash, $role, $display_name ?: $username, $avatar_url]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)");
        $stmt->execute([$username, $hash, $role]);
    }
    echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
    exit;
}

function updateUser($pdo) {
    $id = $_POST['id'] ?? 0;
    $role = $_POST['role'] ?? '';
    $display_name = trim($_POST['display_name'] ?? '');
    $password = trim($_POST['password'] ?? '');
    if (empty($id)) { throw new Exception('User ID is required.'); }
    
    $avatar_url = handleAvatarUpload();
    $avatar_sql = $avatar_url ? ", avatar_url = ?" : "";
    $params = $avatar_url ? [$avatar_url] : [];
    
    $ucols = $pdo->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN);
    
    if ($password) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        if (in_array('display_name', $ucols)) {
            $sql = "UPDATE users SET role = ?, display_name = ?, password_hash = ? {$avatar_sql} WHERE id = ?";
            $finalParams = array_merge([$role, $display_name, $hash], $params, [$id]);
        } else {
            $sql = "UPDATE users SET role = ?, password_hash = ? WHERE id = ?";
            $finalParams = [$role, $hash, $id];
        }
    } else {
        if (in_array('display_name', $ucols)) {
            $sql = "UPDATE users SET role = ?, display_name = ? {$avatar_sql} WHERE id = ?";
            $finalParams = array_merge([$role, $display_name], $params, [$id]);
        } else {
            $sql = "UPDATE users SET role = ? WHERE id = ?";
            $finalParams = [$role, $id];
        }
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($finalParams);
    
    // If the user is updating their OWN profile, refresh their session instantly
    if ($id == ($_SESSION['user_id'] ?? 0)) {
        if ($avatar_url) $_SESSION['avatar_url'] = $avatar_url;
        if (!empty($display_name)) $_SESSION['display_name'] = $display_name;
    }
    
    echo json_encode(['success' => true]);
    exit;
}

function deleteUser($pdo) {
    $id = $_POST['id'] ?? 0;
    if (empty($id)) { throw new Exception('User ID is required.'); }
    if ($id == ($_SESSION['user_id'] ?? 0)) { echo json_encode(['success' => false, 'message' => 'Cannot delete your own account.']); exit; }
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(['success' => true]);
    exit;
}

// ================= SAVED INVOICES API =================

function getSavedInvoices($pdo) {
    // Orders by delivery date (if set), otherwise falls back to creation date (newest at the top)
    $stmt = $pdo->query("SELECT * FROM saved_invoices ORDER BY CASE WHEN delivery_date IS NOT NULL AND delivery_date != '' THEN delivery_date ELSE created_at END DESC");
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    exit;
}

function saveTranslatorInvoice($pdo) {
    $name = $_POST['name'] ?? '';
    $data = $_POST['data'] ?? '';
    $bu = $_POST['bu'] ?? '';
    $location = $_POST['location'] ?? 'Davao';
    $delivery = $_POST['delivery'] ?? null;
    
    if (empty($name) || empty($data)) {
        throw new Exception("Invoice name and data are required.");
    }
    
    // Auto-upgrade the table to support the new location column
    try {
        $pdo->exec("ALTER TABLE saved_invoices ADD COLUMN location VARCHAR(50) DEFAULT 'Davao' AFTER bu");
    } catch(Exception $e) { /* Column likely already exists */ }
    
    $stmt = $pdo->prepare("INSERT INTO saved_invoices (name, invoice_data, bu, location, delivery_date) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$name, $data, $bu, $location, $delivery]);
    
    echo json_encode(['success' => true, 'message' => 'Invoice saved to database.']);
    exit;
}

function bulkUpdateSkuPcs($pdo) {
    $updates = json_decode($_POST['updates'] ?? '[]', true);
    if (is_array($updates) && count($updates) > 0) {
        $stmt = $pdo->prepare("UPDATE product_codes SET pieces_per_case = ? WHERE code = ?");
        foreach ($updates as $u) {
            try { $stmt->execute([$u['pcs'], $u['code']]); } catch (Exception $e) {}
        }
    }
    echo json_encode(['success' => true]);
    exit;
}

function renameSavedInvoice($pdo) {
    $id = $_POST['id'] ?? 0;
    $name = $_POST['name'] ?? '';
    if (empty($id) || empty($name)) throw new Exception("ID and Name required.");
    $stmt = $pdo->prepare("UPDATE saved_invoices SET name = ? WHERE id = ?");
    $stmt->execute([$name, $id]);
    echo json_encode(['success' => true]);
    exit;
}

function toggleInvoiceReceived($pdo) {
    $id = $_POST['id'] ?? 0;
    $status = $_POST['is_received'] ?? 0;
    if (empty($id)) throw new Exception("ID required.");
    $stmt = $pdo->prepare("UPDATE saved_invoices SET is_received = ? WHERE id = ?");
    $stmt->execute([(int)$status, $id]);
    echo json_encode(['success' => true]);
    exit;
}

function deleteSavedInvoice($pdo) {
    $id = $_POST['id'] ?? 0;
    if (empty($id)) throw new Exception("ID required.");
    $stmt = $pdo->prepare("DELETE FROM saved_invoices WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(['success' => true]);
    exit;
}

function bulkDeductStock($pdo) {
    $orderIdsJson = $_POST['order_ids'] ?? '[]';
    $orderIds = json_decode($orderIdsJson, true);
    
    if (empty($orderIds) || !is_array($orderIds)) {
        throw new Exception('No orders selected.');
    }

    $pdo->beginTransaction();
    try {
        // Prepare stock deduction statement
        $stockUpdateStmt = $pdo->prepare("UPDATE inventory_levels SET stock = stock - ? WHERE product_code = ? AND location = ?");

        // Use placeholders for the IN clause
        $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
        
        // Fetch only served or fulfilled items for these specific orders to prevent deducting unserved items
        $stmt = $pdo->prepare("
            SELECT oi.sku, oi.quantity, o.location 
            FROM order_items oi 
            JOIN orders o ON oi.order_id = o.id 
            WHERE o.id IN ($placeholders) AND oi.status IN ('served', 'fulfilled')
        ");
        $stmt->execute($orderIds);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $deductedCount = 0;
        foreach ($items as $item) {
            $stockUpdateStmt->execute([$item['quantity'], $item['sku'], $item['location']]);
            $deductedCount++;
        }

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => "Successfully re-deducted $deductedCount items across " . count($orderIds) . " orders."]);
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
    exit;
}
?>