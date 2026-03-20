<?php
require_once 'includes/config.php';
if (isLogged()) { header('Location: index.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if ($email && $pass) {
        $stmt = db()->prepare("SELECT id, nombre, password, rol FROM usuarios WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $u = $stmt->get_result()->fetch_assoc();

        if ($u && password_verify($pass, $u['password'])) {
            $_SESSION['usuario_id'] = $u['id'];
            $_SESSION['nombre']     = $u['nombre'];
            $_SESSION['rol']        = $u['rol'];
            $redir = $_GET['next'] ?? ($u['rol'] === 'admin' ? 'admin/index.php' : 'index.php');
            header('Location: ' . $redir);
            exit;
        } else {
            $error = 'Correo o contraseña incorrectos.';
        }
    } else {
        $error = 'Por favor completa todos los campos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Iniciar sesión — <?= SITE_NAME ?></title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,900;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="auth-wrap">
  <div class="auth-box fade-in">
    <div class="auth-logo">
      <span class="logo-icon">✒️</span>
      <span class="logo-main">El Rincón del Saber</span>
      <span class="logo-sub">Papelería</span>
    </div>

    <h2>Bienvenido de vuelta</h2>
    <p>Inicia sesión para continuar</p>

    <?php if ($error): ?>
      <div class="alert alert-err"><?= h($error) ?></div>
    <?php endif; ?>

    <!-- Demo credentials hint -->
    <div class="alert alert-info" style="font-size:.8rem;line-height:1.6">
      <strong>Cuentas demo:</strong><br>
      Admin: <code>admin@rincon.com</code><br>
      Cliente: <code>maria@ejemplo.com</code><br>
      Contraseña para ambos: <code>password</code>
    </div>

    <form method="POST" action="login.php<?= isset($_GET['next']) ? '?next='.urlencode($_GET['next']) : '' ?>">
      <div class="fg">
        <label>Correo electrónico</label>
        <input type="email" name="email" value="<?= h($_POST['email'] ?? '') ?>" placeholder="tu@email.com" required autofocus>
      </div>
      <div class="fg">
        <label>Contraseña</label>
        <input type="password" name="password" placeholder="••••••••" required>
      </div>
      <button type="submit" class="btn btn-amber" style="width:100%;justify-content:center;margin-top:6px">
        Iniciar sesión →
      </button>
    </form>

    <div class="auth-divider">¿No tienes cuenta?</div>
    <a href="register.php" class="btn btn-outline" style="width:100%;justify-content:center">Crear cuenta gratis</a>
    <div style="text-align:center;margin-top:18px">
      <a href="index.php" style="font-size:.82rem;color:var(--brown);text-decoration:none">← Volver al inicio</a>
    </div>
  </div>
</div>

<div id="toast" class="toast"></div>
<script src="js/app.js"></script>
</body>
</html>
