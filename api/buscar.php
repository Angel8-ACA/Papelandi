<?php
require_once '../includes/config.php';
header('Content-Type: application/json');

$q      = trim($_GET['q']   ?? '');
$cat_id = (int)($_GET['cat'] ?? 0);

if (strlen($q) < 2 && !$cat_id) {
    echo json_encode([]);
    exit;
}

$where = []; $params = ''; $vals = [];
if ($q)      { $where[] = "(p.nombre LIKE ? OR p.descripcion LIKE ?)"; $params .= 'ss'; $vals[] = "%$q%"; $vals[] = "%$q%"; }
if ($cat_id) { $where[] = "p.categoria_id = ?"; $params .= 'i'; $vals[] = $cat_id; }
$w = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$stmt = db()->prepare("
    SELECT p.id, p.nombre, p.precio, p.stock, p.destacado,
           c.nombre AS cat_n, c.icono AS cat_ico
    FROM productos p
    LEFT JOIN categorias c ON c.id = p.categoria_id
    $w
    ORDER BY p.destacado DESC, p.nombre ASC
    LIMIT 20
");
if ($vals) $stmt->bind_param($params, ...$vals);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

echo json_encode($rows);
