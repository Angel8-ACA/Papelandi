<?php
require_once 'includes/config.php';
$pageTitle = 'Inicio — ' . SITE_NAME;

// Categorías con conteo
$cats = db()->query("SELECT c.*, COUNT(p.id) AS total FROM categorias c LEFT JOIN productos p ON p.categoria_id=c.id GROUP BY c.id LIMIT 6");

// Destacados
$dest = db()->query("SELECT p.*, c.nombre AS cat_n, c.icono AS cat_ico FROM productos p LEFT JOIN categorias c ON c.id=p.categoria_id WHERE p.destacado=1 ORDER BY RAND() LIMIT 8");

include 'includes/header.php';
?>

<!-- HERO -->
<section class="hero">
  <div class="hero-dots"></div><div class="hero-glow"></div>
  <div class="container hero-inner">
    <div class="fade-in">
      <span class="hero-badge">✨ Papelería desde 1998</span>
      <h1>Todo para <em>escribir</em><br>tu mejor historia</h1>
      <p>Artículos escolares, de oficina y arte. La mejor calidad al mejor precio en Morelia, Michoacán.</p>
      <div class="hero-btns">
        <a href="pages/catalogo.php" class="btn btn-amber">Ver catálogo →</a>
        <?php if (!isLogged()): ?>
          <a href="register.php" class="btn btn-ghost">Crear cuenta</a>
        <?php else: ?>
          <a href="pages/carrito.php" class="btn btn-ghost">Mi carrito 🛒</a>
        <?php endif; ?>
      </div>
    </div>
    <div class="hero-cards fade-in d2">
      <div class="hero-card"><div class="hero-card-icon">✏️</div><h3>Escritura</h3><p>Plumas, lápices y marcadores</p></div>
      <div class="hero-card"><div class="hero-card-icon">📚</div><h3>Papelería</h3><p>Cuadernos y libretas</p></div>
      <div class="hero-card"><div class="hero-card-icon">🎨</div><h3>Arte</h3><p>Acuarelas y colores</p></div>
      <div class="hero-card"><div class="hero-card-icon">🚚</div><h3>Envío gratis</h3><p>En compras +$500</p></div>
    </div>
  </div>
</section>

<!-- CATEGORÍAS -->
<section class="section">
  <div class="container">
    <div class="section-head">
      <span class="section-label">Explora por categoría</span>
      <h2 class="section-title">¿Qué estás buscando?</h2>
    </div>
    <div class="cat-grid">
      <?php while ($c = $cats->fetch_assoc()): ?>
      <a href="pages/catalogo.php?cat=<?= $c['id'] ?>" class="cat-card fade-in">
        <div class="cat-icon"><?= $c['icono'] ?></div>
        <div class="cat-name"><?= h($c['nombre']) ?></div>
        <div class="cat-count"><?= $c['total'] ?> artículos</div>
      </a>
      <?php endwhile; ?>
    </div>
  </div>
</section>

<!-- DESTACADOS -->
<section class="section section-paper">
  <div class="container">
    <div class="section-head">
      <span class="section-label">Lo más popular</span>
      <h2 class="section-title">Productos destacados</h2>
      <p class="section-sub">Los favoritos de nuestros clientes</p>
    </div>
    <div class="prod-grid">
      <?php while ($p = $dest->fetch_assoc()): ?>
      <div class="prod-card fade-in">
        <div class="prod-img">
          <span><?= $p['cat_ico'] ?? '📦' ?></span>
          <span class="badge badge-star">⭐ Destacado</span>
        </div>
        <div class="prod-body">
          <div class="prod-cat"><?= h($p['cat_ico'].' '.$p['cat_n']) ?></div>
          <h3 class="prod-name"><?= h($p['nombre']) ?></h3>
          <p class="prod-desc"><?= h(mb_substr($p['descripcion'],0,80)) ?>...</p>
          <div class="prod-footer">
            <div class="prod-price"><small>$</small><?= number_format($p['precio'],2) ?></div>
            <?php if (isLogged()): ?>
              <button class="btn-add" onclick="addToCart(<?= $p['id'] ?>,'<?= addslashes($p['nombre']) ?>',<?= $p['precio'] ?>,'<?= addslashes($p['cat_n']) ?>')">+ Agregar</button>
            <?php else: ?>
              <a href="login.php" class="btn-add" style="text-decoration:none;display:inline-flex">🔑 Login</a>
            <?php endif; ?>
          </div>
          <div class="<?= $p['stock']<10?'stock-low':'stock-ok' ?>">
            <?= $p['stock']===0?'❌ Sin stock':($p['stock']<10?'⚠️ Últimas '.$p['stock'].' uds':'✓ En stock') ?>
          </div>
        </div>
      </div>
      <?php endwhile; ?>
    </div>
    <div style="text-align:center;margin-top:40px">
      <a href="pages/catalogo.php" class="btn btn-dark">Ver todos los productos →</a>
    </div>
  </div>
</section>

<!-- BANNER -->
<section class="banner">
  <div class="container">
    <h2>¿Necesitas asesoría?</h2>
    <p>Nuestros expertos te ayudan a encontrar el artículo perfecto.</p>
    <a href="pages/contacto.php" class="btn btn-dark">Contáctanos ahora</a>
  </div>
</section>

<!-- VENTAJAS -->
<section class="section">
  <div class="container">
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:30px;text-align:center">
      <?php foreach([['🚀','Entrega rápida','Mismo día en Morelia antes de las 2pm'],['💰','Precios justos','Los mejores precios sin sacrificar calidad'],['🔄','Devoluciones','30 días para cambios sin preguntas'],['❤️','Atención personal','Más de 25 años sirviéndote']] as $i=>$v): ?>
      <div class="fade-in d<?= $i ?>">
        <div style="font-size:2.3rem;margin-bottom:9px"><?= $v[0] ?></div>
        <h4 style="font-family:var(--fd);font-size:1rem;font-weight:700;margin-bottom:6px"><?= $v[1] ?></h4>
        <p style="font-size:.81rem;color:var(--brown);line-height:1.6"><?= $v[2] ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
