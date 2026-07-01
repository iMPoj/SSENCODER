<?php
// manage_customers.php
// FIXED: Now uses PDO to match your db_connect.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// This file provides the $pdo variable
require_once 'db_connect.php'; 

$message = '';
$messageType = '';
$skippedLines = []; // NEW: Array to store the exact lines that fail

// --- HANDLE BATCH IMPORT ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import_batch'])) {
    $rawText = $_POST['batch_text'];
    $lines = explode("\n", $rawText);
    $imported = 0;
    $errors = 0;

    try {
        // Start Transaction
        $pdo->beginTransaction();

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Automatically skip the header row if you accidentally paste it
            if (stripos($line, 'Customer code') !== false && stripos($line, 'Salesman') !== false) {
                continue;
            }

            // Split by tab or multiple spaces
            $parts = preg_split('/\s{2,}|\t/', $line);
            $parts = array_map('trim', $parts);
            $parts = array_values(array_filter($parts));

            // We now expect exactly 5 columns
            if (count($parts) >= 5) {
                $custName = $parts[0];
                $address = $parts[1];
                $custCode = $parts[2];
                $salesmanName = $parts[3];
                $salesmanCode = $parts[4];

                // 1. Find or Create Customer
                $stmt = $pdo->prepare("SELECT id FROM customers WHERE name = ?");
                $stmt->execute([$custName]);
                $row = $stmt->fetch();
                
                if ($row) {
                    $customerId = $row['id'];
                } else {
                    $stmt = $pdo->prepare("INSERT INTO customers (name) VALUES (?)");
                    $stmt->execute([$custName]);
                    $customerId = $pdo->lastInsertId();
                }

                // 2. CLEANUP: Delete ALL existing records with this exact Customer Code
                $stmt = $pdo->prepare("DELETE FROM customer_address_codes WHERE customer_code = ?");
                $stmt->execute([$custCode]);

                // 3. INSERT the correct, fresh record
                $stmt = $pdo->prepare("INSERT INTO customer_address_codes (customer_id, customer_code, address, salesman_name, salesman_code) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$customerId, $custCode, $address, $salesmanName, $salesmanCode]);
                
                $imported++;
            } else {
                // NEW: If it fails, count it AND save the line text
                $errors++;
                $skippedLines[] = $line;
            }
        }
        
        $pdo->commit();
        $message = "Successfully processed $imported records. ($errors skipped/invalid)";
        $messageType = "green";

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $message = "Error importing: " . $e->getMessage();
        $messageType = "red";
    }
}

// --- FETCH DATA FOR DISPLAY ---
$sql = "SELECT c.name, cac.customer_code, cac.address 
        FROM customer_address_codes cac 
        JOIN customers c ON cac.customer_id = c.id 
        ORDER BY c.name ASC, cac.address ASC";

try {
    $stmt = $pdo->query($sql);
    $results = $stmt->fetchAll();
} catch (Exception $e) {
    $results = [];
    $message = "Database Error: " . $e->getMessage();
    $messageType = "red";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Address Manager</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 p-8">
    <div class="max-w-6xl mx-auto space-y-8">
        
        <div class="flex justify-between items-center">
            <h1 class="text-3xl font-bold text-slate-800">Customer Address Manager</h1>
            <a href="index.php" class="text-blue-600 hover:underline">Back to Dashboard</a>
        </div>

        <?php if($message): ?>
            <div class="p-4 rounded-md bg-<?php echo $messageType; ?>-100 text-<?php echo $messageType; ?>-700 border border-<?php echo $messageType; ?>-400">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if(!empty($skippedLines)): ?>
            <div class="p-4 rounded-md bg-yellow-50 text-yellow-800 border border-yellow-300">
                <h3 class="font-bold mb-2">The following lines were skipped because they did not have exactly 5 columns separated by tabs/spaces:</h3>
                <ul class="list-disc pl-5 font-mono text-sm space-y-1">
                    <?php foreach($skippedLines as $errLine): ?>
                        <li><?php echo htmlspecialchars($errLine); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
            <h2 class="text-xl font-bold mb-4 text-slate-700">Batch Import Customers</h2>
            
            <p class="text-sm text-slate-500 mb-2">Paste text below. Format: <strong>Customer</strong> [tab/space] <strong>Address</strong> [tab/space] <strong>Code</strong> [tab/space] <strong>Salesman</strong> [tab/space] <strong>Salesman Code</strong></p>
            
            <form method="POST">
                <textarea name="batch_text" rows="6" class="w-full p-3 border border-slate-300 rounded-lg font-mono text-xs focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Felcris Group   10003-DCWC DIGOS...   ZKB07376-001   GLENN BUCAG   SSDB07"></textarea>
                <button type="submit" name="import_batch" class="mt-4 bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 font-semibold transition">
                    Process Import
                </button>
            </form>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-slate-700">Customer Extraction</h2>
                <button onclick="copyTable()" class="text-xs bg-slate-200 hover:bg-slate-300 px-3 py-1 rounded text-slate-700">Copy to Clipboard</button>
            </div>

            <div class="overflow-x-auto max-h-[600px] border rounded-lg">
                <table id="customerTable" class="w-full text-sm text-left">
                    <thead class="bg-slate-50 text-slate-600 sticky top-0">
                        <tr>
                            <th class="p-3 border-b">Combined String (Name Address Code)</th>
                            <th class="p-3 border-b w-32">Code</th>
                            <th class="p-3 border-b">Raw Address</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (count($results) > 0): ?>
                            <?php foreach($results as $row): ?>
                                <?php 
                                    $combined = $row['name'] . ' ' . $row['address'] . ' ' . $row['customer_code'];
                                ?>
                                <tr class="hover:bg-slate-50">
                                    <td class="p-3 font-mono text-slate-700 select-all"><?php echo htmlspecialchars($combined); ?></td>
                                    <td class="p-3 text-slate-500"><?php echo htmlspecialchars($row['customer_code']); ?></td>
                                    <td class="p-3 text-slate-500 truncate max-w-md"><?php echo htmlspecialchars($row['address']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="p-8 text-center text-slate-400">
                                    No linked addresses found. Please run the import first.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function copyTable() {
            const table = document.getElementById('customerTable');
            let text = "";
            for (let i = 1; i < table.rows.length; i++) {
                text += table.rows[i].cells[0].innerText + "\n";
            }
            navigator.clipboard.writeText(text).then(() => {
                alert('List copied to clipboard!');
            });
        }
    </script>
</body>
</html>