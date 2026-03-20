<?php
$base  = basePath();
$self  = basename($_SERVER['PHP_SELF']);
$inPages = strpos($_SERVER['PHP_SELF'], '/pages/') !== false;
$inAdmin = strpos($_SERVER['PHP_SELF'], '/admin/')  !== false;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $pageTitle ?? SITE_NAME ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,900;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= $base ?>css/style.css">
</head>
<body>

<header class="header">
  <div class="header-inner container">
    <a href="<?= $base ?>index.php" class="logo">
      <span class="logo-icon">✒️</span>
      <div>
        <div class="logo-main">El Rincón</div>
        <div class="logo-sub">del Saber</div>
      </div>
    </a>

    <nav class="nav">
      <a href="<?= $base ?>index.php"           class="<?= $self==='index.php'&&!$inAdmin?'active':'' ?>">Inicio</a>
      <a href="<?= $base ?>pages/catalogo.php"  class="<?= $self==='catalogo.php'?'active':'' ?>">Catálogo</a>
      <a href="<?= $base ?>pages/contacto.php"  class="<?= $self==='contacto.php'?'active':'' ?>">Contacto</a>

      <?php if (isLogged()): ?>
        <a href="<?= $base ?>pages/carrito.php" class="cart-btn <?= $self==='carrito.php'?'active':'' ?>">
          🛒 <span id="cart-count">0</span>
        </a>
        <div class="user-menu">
          <button class="user-btn" onclick="toggleUserMenu()">
            👤 <?= h(explode(' ', userName())[0]) ?> ▾
          </button>
          <div class="user-drop" id="userDrop">
            <?php if (isAdmin()): ?>
              <a href="<?= $base ?>admin/index.php">⚙️ Administración</a>
            <?php endif; ?>
            <a href="<?= $base ?>pages/perfil.php">👤 Mi perfil</a>
            <a href="<?= $base ?>pages/perfil.php">👤 Mi perfil</a>
      <a href="<?= $base ?>pages/mis-pedidos.php">📦 Mis pedidos</a>
            <hr style="margin:4px 0;border-color:var(--border)">
            <a href="<?= $base ?>logout.php" style="color:var(--terra)">🚪 Cerrar sesión</a>
          </div>
        </div>
      <?php else: ?>
        <a href="<?= $base ?>login.php" class="btn-nav-login <?= $self==='login.php'?'active':'' ?>">Iniciar sesión</a>
      <?php endif; ?>
    </nav>

    <button class="hamburger" onclick="toggleMenu()">☰</button>
  </div>

  <!-- Mobile nav -->
  <nav class="mobile-nav" id="mobileNav">
    <a href="<?= $base ?>index.php">🏠 Inicio</a>
    <a href="<?= $base ?>pages/catalogo.php">📦 Catálogo</a>
    <a href="<?= $base ?>pages/contacto.php">📩 Contacto</a>
    <?php if (isLogged()): ?>
      <a href="<?= $base ?>pages/carrito.php">🛒 Carrito</a>
      <a href="<?= $base ?>pages/perfil.php">👤 Mi perfil</a>
            <a href="<?= $base ?>pages/perfil.php">👤 Mi perfil</a>
      <a href="<?= $base ?>pages/mis-pedidos.php">📦 Mis pedidos</a>
      <?php if (isAdmin()): ?>
        <a href="<?= $base ?>admin/index.php">⚙️ Admin</a>
      <?php endif; ?>
      <a href="<?= $base ?>logout.php" style="color:var(--terra)">🚪 Cerrar sesión</a>
    <?php else: ?>
      <a href="<?= $base ?>login.php">🔑 Iniciar sesión</a>
      <a href="<?= $base ?>register.php">✍️ Registrarse</a>
    <?php endif; ?>
  </nav>
</header>
