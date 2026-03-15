<?php
header('Content-Type: application/json');
require_once '../functions.php';

$product_id = intval($_GET['product_id'] ?? 0);

if ($product_id <= 0) {
    echo json_encode(['error' => 'Invalid product ID']);
    exit;
}

$stmt = $pdo->prepare("
    SELECT ird.*, ir.import_date, ir.id as receipt_id, p.profit_margin, p.name, p.code
    FROM import_receipt_details ird 
    JOIN import_receipts ir ON ird.receipt_id = ir.id 
    JOIN products p ON ird.product_id = p.id
    WHERE ird.product_id = ? AND ir.status = 'completed'
    ORDER BY ir.import_date DESC
");
$stmt->execute([$product_id]);
$batches = $stmt->fetchAll();

echo json_encode([
    'success' => true,
    'batches' => array_map(function($b) {
        return [
            'receipt_id' => $b['receipt_id'],
            'import_date' => date('d/m/Y', strtotime($b['import_date'])),
            'quantity' => $b['quantity'],
            'import_price' => formatPrice($b['import_price']),
            'profit_margin' => $b['profit_margin'],
            'selling_price' => formatPrice(getSellingPrice($b['import_price'], $b['profit_margin']))
        ];
    }, $batches)
]);
