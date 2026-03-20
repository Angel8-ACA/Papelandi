<?php
require_once '../includes/config.php';
requireLogin();
$pageTitle = 'Mi carrito — ' . SITE_NAME;
include '../includes/header.php';
?>
<div class="container" style="padding-top:24px;padding-bottom:64px">
  <div class="breadcrumb"><a href="../index.php">Inicio</a><span>›</span><span>Carrito</span></div>
  <h1 style="font-family:var(--fd);font-size:2rem;font-weight:900;margin:20px 0 28px">🛒 Tu carrito</h1>
  <div class="cart-layout">
    <div>
      <table class="cart-tbl">
        <thead><tr><th>Producto</th><th>Precio</th><th>Cantidad</th><th>Subtotal</th><th></th></tr></thead>
        <tbody id="cart-body"><tr><td colspan="5" style="text-align:center;padding:32px;color:var(--brown)">Cargando...</td></tr></tbody>
      </table>
      <div style="margin-top:16px"><a href="catalogo.php" class="btn btn-outline">← Seguir comprando</a></div>
    </div>
    <div class="summary-box">
      <h3>Resumen del pedido</h3>
      <div id="cart-sum"><p style="color:var(--brown);text-align:center;font-size:.88rem">Cargando...</p></div>
    </div>
  </div>
</div>
<?php include '../includes/footer.php'; ?>
