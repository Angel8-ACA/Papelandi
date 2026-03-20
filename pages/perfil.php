<?php
require_once '../includes/config.php';
requireLogin();
$pageTitle = 'Mi perfil — ' . SITE_NAME;

$uid = userId();
$u   = db()->query("SELECT * FROM usuarios WHERE id=$uid")->fetch_assoc();

$ok = $err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    // ── Actualizar datos personales
    if ($accion === 'datos') {
        $nombre = trim($_POST['nombre'] ?? '');
        $email  = trim($_POST['email']  ?? '');

        if (!$nombre || !$email) {
            $err = 'El nombre y correo son obligatorios.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $err = 'El correo no es válido.';
        } else {
            // Verificar email duplicado (de otro usuario)
            $chk = db()->prepare("SELECT id FROM usuarios WHERE email=? AND id!=?");
            $chk->bind_param('si', $email, $uid);
            $chk->execute();
            if ($chk->get_result()->num_rows > 0) {
                $err = 'Ese correo ya está en uso por otra cuenta.';
            } else {
                $stmt = db()->prepare("UPDATE usuarios SET nombre=?, email=? WHERE id=?");
                $stmt->bind_param('ssi', $nombre, $email, $uid);
                $stmt->execute();
                $_SESSION['nombre'] = $nombre;
                $u['nombre'] = $nombre;
                $u['email']  = $email;
                $ok = '✅ Datos actualizados correctamente.';
            }
        }
    }

    // ── Cambiar contraseña
    if ($accion === 'password') {
        $actual = $_POST['actual']    ?? '';
        $nueva  = $_POST['nueva']     ?? '';
        $conf   = $_POST['confirmar'] ?? '';

        if (!$actual || !$nueva || !$conf) {
            $err = 'Completa todos los campos de contraseña.';
        } elseif (!password_verify($actual, $u['password'])) {
            $err = 'La contraseña actual es incorrecta.';
        } elseif (strlen($nueva) < 6) {
            $err = 'La nueva contraseña debe tener al menos 6 caracteres.';
        } elseif ($nueva !== $conf) {
            $err = 'Las contraseñas nuevas no coinciden.';
        } else {
            $hash = password_hash($nueva, PASSWORD_DEFAULT);
            $stmt = db()->prepare("UPDATE usuarios SET password=? WHERE id=?");
            $stmt->bind_param('si', $hash, $uid);
            $stmt->execute();
            $ok = '✅ Contraseña cambiada correctamente.';
        }
    }
}

// Pedidos del usuario
$pedidos = db()->query("SELECT COUNT(*) AS t FROM ventas WHERE usuario_id=$uid")->fetch_assoc()['t'];
$gasto   = db()->query("SELECT COALESCE(SUM(total),0) AS t FROM ventas WHERE usuario_id=$uid")->fetch_assoc()['t'];

include '../includes/header.php';
?>

<div class="container" style="padding-top:24px;padding-bottom:64px">
  <div class="breadcrumb">
    <a href="../index.php">Inicio</a><span>›</span><span>Mi perfil</span>
  </div>

  <h1 style="font-family:var(--fd);font-size:2rem;font-weight:900;margin:20px 0 32px">👤 Mi perfil</h1>

  <!-- Tarjeta resumen -->
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;margin-bottom:36px">
    <div class="stat" style="background:var(--white);border:1px solid var(--border);border-radius:var(--rl);padding:22px;position:relative;overflow:hidden">
      <div style="position:absolute;left:0;top:0;width:4px;height:100%;background:var(--amber)"></div>
      <div style="font-size:1.7rem;margin-bottom:5px">👤</div>
      <div style="font-family:var(--fd);font-size:1.1rem;font-weight:900"><?= h($u['nombre']) ?></div>
      <div style="font-size:.76rem;color:var(--brown)"><?= h($u['email']) ?></div>
    </div>
    <div class="stat" style="background:var(--white);border:1px solid var(--border);border-radius:var(--rl);padding:22px;position:relative;overflow:hidden">
      <div style="position:absolute;left:0;top:0;width:4px;height:100%;background:var(--sage)"></div>
      <div style="font-size:1.7rem;margin-bottom:5px">📦</div>
      <div style="font-family:var(--fd);font-size:1.7rem;font-weight:900"><?= $pedidos ?></div>
      <div style="font-size:.76rem;color:var(--brown)">Pedidos realizados</div>
    </div>
    <div class="stat" style="background:var(--white);border:1px solid var(--border);border-radius:var(--rl);padding:22px;position:relative;overflow:hidden">
      <div style="position:absolute;left:0;top:0;width:4px;height:100%;background:var(--amber)"></div>
      <div style="font-size:1.7rem;margin-bottom:5px">💰</div>
      <div style="font-family:var(--fd);font-size:1.7rem;font-weight:900">$<?= number_format($gasto,0) ?></div>
      <div style="font-size:.76rem;color:var(--brown)">Total gastado</div>
    </div>
    <div class="stat" style="background:var(--white);border:1px solid var(--border);border-radius:var(--rl);padding:22px;position:relative;overflow:hidden">
      <div style="position:absolute;left:0;top:0;width:4px;height:100%;background:var(--terra)"></div>
      <div style="font-size:1.7rem;margin-bottom:5px"><?= $u['rol']==='admin'?'⚙️':'🎓' ?></div>
      <div style="font-family:var(--fd);font-size:1.1rem;font-weight:900"><?= ucfirst($u['rol']) ?></div>
      <div style="font-size:.76rem;color:var(--brown)">Tipo de cuenta</div>
    </div>
  </div>

  <?php if ($ok): ?><div class="alert alert-ok"><?= $ok ?></div><?php endif; ?>
  <?php if ($err): ?><div class="alert alert-err"><?= $err ?></div><?php endif; ?>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px">

    <!-- Datos personales -->
    <div class="form-box">
      <h3 style="font-family:var(--fd);font-size:1.35rem;font-weight:700;margin-bottom:6px">✏️ Datos personales</h3>
      <p style="color:var(--brown);font-size:.85rem;margin-bottom:22px">Actualiza tu nombre y correo electrónico</p>
      <form method="POST">
        <input type="hidden" name="accion" value="datos">
        <div class="fg">
          <label>Nombre completo *</label>
          <input type="text" name="nombre" value="<?= h($u['nombre']) ?>" required>
        </div>
        <div class="fg">
          <label>Correo electrónico *</label>
          <input type="email" name="email" value="<?= h($u['email']) ?>" required>
        </div>
        <div class="fg">
          <label>Miembro desde</label>
          <input type="text" value="<?= date('d/m/Y', strtotime($u['created_at'])) ?>" disabled style="opacity:.6;cursor:not-allowed">
        </div>
        <button type="submit" class="btn btn-amber" style="width:100%;justify-content:center">
          Guardar cambios →
        </button>
      </form>
    </div>

    <!-- Cambiar contraseña -->
    <div class="form-box">
      <h3 style="font-family:var(--fd);font-size:1.35rem;font-weight:700;margin-bottom:6px">🔒 Cambiar contraseña</h3>
      <p style="color:var(--brown);font-size:.85rem;margin-bottom:22px">Usa una contraseña segura de al menos 6 caracteres</p>
      <form method="POST">
        <input type="hidden" name="accion" value="password">
        <div class="fg">
          <label>Contraseña actual *</label>
          <input type="password" name="actual" placeholder="••••••••" required>
        </div>
        <div class="fg">
          <label>Nueva contraseña *</label>
          <input type="password" name="nueva" placeholder="••••••••" required minlength="6">
        </div>
        <div class="fg">
          <label>Confirmar nueva contraseña *</label>
          <input type="password" name="confirmar" placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn btn-dark" style="width:100%;justify-content:center">
          Cambiar contraseña →
        </button>
      </form>
    </div>

  </div>

  <!-- Accesos rápidos -->
  <div style="margin-top:24px;display:flex;gap:12px;flex-wrap:wrap">
    <a href="mis-pedidos.php" class="btn btn-outline">📦 Ver mis pedidos</a>
    <a href="catalogo.php"    class="btn btn-outline">🛍️ Ir al catálogo</a>
    <?php if (isAdmin()): ?>
    <a href="../admin/index.php" class="btn btn-amber">⚙️ Panel de administración</a>
    <?php endif; ?>
    <a href="../logout.php" class="btn btn-danger btn-sm" style="margin-left:auto">🚪 Cerrar sesión</a>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
