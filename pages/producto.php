<?php
require_once '../includes/config.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: catalogo.php'); exit; }

$p = db()->query("
    SELECT p.*, c.nombre AS cat_n, c.icono AS cat_ico
    FROM productos p
    LEFT JOIN categorias c ON c.id = p.categoria_id
    WHERE p.id = $id
")->fetch_assoc();

if (!$p) { header('Location: catalogo.php'); exit; }

$pageTitle = h($p['nombre']) . ' — ' . SITE_NAME;

// Productos relacionados (misma categoría)
$rel = db()->query("
    SELECT p.*, c.icono AS cat_ico
    FROM productos p
    LEFT JOIN categorias c ON c.id = p.categoria_id
    WHERE p.categoria_id = {$p['categoria_id']} AND p.id != $id
    ORDER BY RAND() LIMIT 4
");

include '../includes/header.php';
?>

<div class="container" style="padding-top:24px;padding-bottom:64px">
  <div class="breadcrumb">
    <a href="../index.php">Inicio</a><span>›</span>
    <a href="catalogo.php">Catálogo</a><span>›</span>
    <a href="catalogo.php?cat=<?= $p['categoria_id'] ?>"><?= $p['cat_ico'].' '.h($p['cat_n']) ?></a>
    <span>›</span><span><?= h($p['nombre']) ?></span>
  </div>

  <!-- Producto principal -->
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:48px;margin-top:28px;align-items:start">

    <!-- Imagen -->
    <div style="background:linear-gradient(135deg,var(--paper),var(--cream));border-radius:var(--rl);border:2px solid var(--border);display:flex;align-items:center;justify-content:center;min-height:360px;position:relative;overflow:hidden">
      <span style="font-size:8rem"><?= $p['cat_ico']??'📦' ?></span>
      <?php if ($p['destacado']): ?>
        <span class="badge badge-star" style="position:absolute;top:16px;left:16px;font-size:.72rem;padding:6px 14px">⭐ Producto destacado</span>
      <?php endif; ?>
      <?php if (!$p['stock']): ?>
        <span class="badge badge-out" style="position:absolute;top:16px;right:16px;font-size:.72rem;padding:6px 14px">Sin stock</span>
      <?php endif; ?>
    </div>

    <!-- Info -->
    <div>
      <div style="font-size:.7rem;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:var(--amber);margin-bottom:8px">
        <?= $p['cat_ico'].' '.h($p['cat_n']) ?>
      </div>
      <h1 style="font-family:var(--fd);font-size:2rem;font-weight:900;line-height:1.15;letter-spacing:-.02em;margin-bottom:16px">
        <?= h($p['nombre']) ?>
      </h1>
      <p style="font-size:.95rem;color:var(--brown);line-height:1.75;margin-bottom:24px">
        <?= h($p['descripcion']) ?>
      </p>

      <!-- Precio -->
      <div style="display:flex;align-items:baseline;gap:6px;margin-bottom:20px">
        <span style="font-family:var(--fd);font-size:2.6rem;font-weight:900">$<?= number_format($p['precio'],2) ?></span>
        <span style="font-size:.85rem;color:var(--brown)">MXN</span>
      </div>

      <!-- Stock -->
      <div style="margin-bottom:24px;padding:14px 18px;background:var(--paper);border-radius:var(--r);border-left:4px solid <?= $p['stock']==0?'var(--terra)':($p['stock']<10?'var(--amber)':'var(--sage)') ?>">
        <?php if ($p['stock']==0): ?>
          <span style="color:var(--terra);font-weight:600;font-size:.9rem">❌ Producto agotado por el momento</span>
        <?php elseif ($p['stock']<10): ?>
          <span style="color:var(--amber);font-weight:600;font-size:.9rem">⚠️ ¡Últimas <?= $p['stock'] ?> unidades disponibles!</span>
        <?php else: ?>
          <span style="color:var(--sage);font-weight:600;font-size:.9rem">✓ En stock — <?= $p['stock'] ?> unidades disponibles</span>
        <?php endif; ?>
      </div>

      <!-- Cantidad + botón -->
      <?php if ($p['stock']>0): ?>
        <?php if (isLogged()): ?>
        <div style="display:flex;gap:12px;align-items:center;margin-bottom:16px">
          <div style="display:flex;align-items:center;border:2px solid var(--border);border-radius:var(--r);overflow:hidden">
            <button onclick="cambiarQty(-1)" style="background:var(--paper);border:none;padding:10px 16px;font-size:1.1rem;cursor:pointer;transition:var(--ease)" onmouseover="this.style.background='var(--border)'" onmouseout="this.style.background='var(--paper)'">−</button>
            <input type="number" id="qty" value="1" min="1" max="<?= $p['stock'] ?>" style="width:56px;text-align:center;border:none;border-left:2px solid var(--border);border-right:2px solid var(--border);padding:10px;font-size:.95rem;font-weight:600;outline:none">
            <button onclick="cambiarQty(1)"  style="background:var(--paper);border:none;padding:10px 16px;font-size:1.1rem;cursor:pointer;transition:var(--ease)" onmouseover="this.style.background='var(--border)'" onmouseout="this.style.background='var(--paper)'">+</button>
          </div>
          <button class="btn btn-amber" style="flex:1;justify-content:center" onclick="agregarVariosAlCarrito(<?= $p['id'] ?>,'<?= addslashes($p['nombre']) ?>',<?= $p['precio'] ?>,'<?= addslashes($p['cat_n']) ?>')">
            🛒 Agregar al carrito
          </button>
        </div>
        <?php else: ?>
        <a href="../login.php" class="btn btn-amber" style="width:100%;justify-content:center;margin-bottom:16px">
          🔑 Inicia sesión para comprar
        </a>
        <?php endif; ?>
      <?php else: ?>
        <button class="btn btn-dark" disabled style="width:100%;justify-content:center;opacity:.5;cursor:not-allowed;margin-bottom:16px">
          Sin stock disponible
        </button>
      <?php endif; ?>

      <a href="catalogo.php?cat=<?= $p['categoria_id'] ?>" class="btn btn-outline" style="width:100%;justify-content:center">
        ← Ver más <?= h($p['cat_n']) ?>
      </a>

      <!-- Detalles del producto -->
      <div style="margin-top:28px;padding-top:24px;border-top:2px solid var(--border)">
        <h4 style="font-family:var(--fd);font-size:1rem;font-weight:700;margin-bottom:14px">Detalles del producto</h4>
        <table style="width:100%;font-size:.85rem;border-collapse:collapse">
          <tr style="border-bottom:1px solid var(--border)">
            <td style="padding:8px 0;color:var(--brown);width:40%">Categoría</td>
            <td style="padding:8px 0;font-weight:500"><?= $p['cat_ico'].' '.h($p['cat_n']) ?></td>
          </tr>
          <tr style="border-bottom:1px solid var(--border)">
            <td style="padding:8px 0;color:var(--brown)">Precio</td>
            <td style="padding:8px 0;font-weight:500">$<?= number_format($p['precio'],2) ?> MXN</td>
          </tr>
          <tr style="border-bottom:1px solid var(--border)">
            <td style="padding:8px 0;color:var(--brown)">Disponibilidad</td>
            <td style="padding:8px 0;font-weight:500"><?= $p['stock']>0?'En stock':'Agotado' ?></td>
          </tr>
          <tr>
            <td style="padding:8px 0;color:var(--brown)">SKU</td>
            <td style="padding:8px 0;font-weight:500;font-family:monospace">RINC-<?= str_pad($p['id'],4,'0',STR_PAD_LEFT) ?></td>
          </tr>
        </table>
      </div>
    </div>
  </div>

  <!-- Productos relacionados -->
  <?php if ($rel->num_rows): ?>
  <div style="margin-top:64px">
    <div class="section-head" style="margin-bottom:28px">
      <span class="section-label">De la misma categoría</span>
      <h2 class="section-title">Productos relacionados</h2>
    </div>
    <div class="prod-grid">
      <?php while ($r=$rel->fetch_assoc()): ?>
      <div class="prod-card fade-in">
        <div class="prod-img">
          <span><?= $r['cat_ico']??'📦' ?></span>
          <?php if (!$r['stock']): ?><span class="badge badge-out">Agotado</span><?php endif; ?>
        </div>
        <div class="prod-body">
          <h3 class="prod-name"><?= h($r['nombre']) ?></h3>
          <p class="prod-desc"><?= h(mb_substr($r['descripcion']??'',0,70)) ?>...</p>
          <div class="prod-footer">
            <div class="prod-price"><small>$</small><?= number_format($r['precio'],2) ?></div>
            <a href="producto.php?id=<?= $r['id'] ?>" class="btn-add" style="text-decoration:none;display:inline-flex">Ver →</a>
          </div>
        </div>
      </div>
      <?php endwhile; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<script>
function cambiarQty(delta) {
  const inp = document.getElementById('qty');
  const max = parseInt(inp.max);
  let v = parseInt(inp.value) + delta;
  inp.value = Math.max(1, Math.min(max, v));
}
function agregarVariosAlCarrito(id, nombre, precio, cat) {
  const qty = parseInt(document.getElementById('qty').value) || 1;
  for (let i = 0; i < qty; i++) addToCart(id, nombre, precio, cat);
}
</script>

<?php include '../includes/footer.php'; ?>
