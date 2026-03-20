<?php
require_once '../includes/config.php';
requireLogin();
$pageTitle = 'Mis pedidos — ' . SITE_NAME;

$uid = userId();
$ventas = db()->query("SELECT * FROM ventas WHERE usuario_id=$uid ORDER BY fecha DESC");

include '../includes/header.php';
?>
<div class="container" style="padding-top:24px;padding-bottom:64px">
  <div class="breadcrumb"><a href="../index.php">Inicio</a><span>›</span><span>Mis pedidos</span></div>
  <h1 style="font-family:var(--fd);font-size:2rem;font-weight:900;margin:20px 0 28px">📦 Mis pedidos</h1>

  <?php if (!$ventas->num_rows): ?>
  <div class="empty">
    <div class="empty-ico">📦</div>
    <h3>Aún no tienes pedidos</h3>
    <p>¡Explora el catálogo y haz tu primera compra!</p>
    <a href="catalogo.php" class="btn btn-amber" style="margin-top:16px">Ver catálogo</a>
  </div>
  <?php else: ?>
  <div class="orders-grid">
    <?php while ($v = $ventas->fetch_assoc()):
      $items = db()->query("SELECT dv.*, p.nombre FROM detalle_ventas dv LEFT JOIN productos p ON p.id=dv.producto_id WHERE dv.venta_id={$v['id']}");
    ?>
    <div class="order-card">
      <div class="order-head">
        <div>
          <div class="order-id">Pedido #<?= $v['id'] ?></div>
          <div class="order-date"><?= date('d/m/Y H:i', strtotime($v['fecha'])) ?></div>
        </div>
        <div class="order-total">$<?= number_format($v['total'],2) ?></div>
      </div>
      <div class="order-items">
        <?php while ($it=$items->fetch_assoc()): ?>
        <div class="order-item">
          <span><?= h($it['nombre']??'Producto eliminado') ?></span>
          <span><?= $it['cantidad'] ?>× $<?= number_format($it['precio_unitario'],2) ?></span>
        </div>
        <?php endwhile; ?>
      </div>
    </div>
    <?php endwhile; ?>
  </div>
  <?php endif; ?>
</div>
<?php include '../includes/footer.php'; ?>
