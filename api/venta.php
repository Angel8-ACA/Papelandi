<?php
require_once '../includes/config.php';
header('Content-Type: application/json');

if (!isLogged()) { echo json_encode(['success'=>false,'error'=>'No autenticado']); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['success'=>false]); exit; }

$data  = json_decode(file_get_contents('php://input'), true);
$items = $data['items'] ?? [];
if (!$items) { echo json_encode(['success'=>false,'error'=>'Carrito vacío']); exit; }

$uid   = userId();
$total = array_sum(array_map(fn($i) => $i['precio'] * $i['qty'], $items));

db()->query("INSERT INTO ventas (usuario_id, total) VALUES ($uid, $total)");
$vid = db()->insert_id;

foreach ($items as $item) {
    $pid   = (int)$item['id'];
    $qty   = (int)$item['qty'];
    $price = (float)$item['precio'];
    $stmt  = db()->prepare("INSERT INTO detalle_ventas (venta_id,producto_id,cantidad,precio_unitario) VALUES (?,?,?,?)");
    $stmt->bind_param('iiid', $vid, $pid, $qty, $price);
    $stmt->execute();
    db()->query("UPDATE productos SET stock=GREATEST(0,stock-$qty) WHERE id=$pid");
}

echo json_encode(['success'=>true,'venta_id'=>$vid]);
