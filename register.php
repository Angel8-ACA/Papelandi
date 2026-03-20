<?php
require_once 'includes/config.php';
if (isLogged()) { header('Location: index.php'); exit; }

$error = $ok = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']   ?? '');
    $email  = trim($_POST['email']    ?? '');
    $pass   = $_POST['password']      ?? '';
    $pass2  = $_POST['password2']     ?? '';

    if (!$nombre || !$email || !$pass) {
        $error = 'Todos los campos son obligatorios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El correo electrónico no es válido.';
    } elseif (strlen($pass) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';
    } elseif ($pass !== $pass2) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        // Verificar si el email ya existe
        $check = db()->prepare("SELECT id FROM usuarios WHERE email = ?");
        $check->bind_param('s', $email);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $error = 'Ese correo ya está registrado. ¿Quieres iniciar sesión?';
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = db()->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, 'cliente')");
            $stmt->bind_param('sss', $nombre, $email, $hash);
            $stmt->execute();

            // Login automático
            $_SESSION['usuario_id'] = db()->insert_id;
            $_SESSION['nombre']     = $nombre;
            $_SESSION['rol']        = 'cliente';
            header('Location: index.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Crear cuenta — <?= SITE_NAME ?></title>
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

    <h2>Crear cuenta</h2>
    <p>Regístrate para comprar en nuestra tienda</p>

    <?php if ($error): ?>
      <div class="alert alert-err"><?= h($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="register.php">
      <div class="fg">
        <label>Nombre completo *</label>
        <input type="text" name="nombre" value="<?= h($_POST['nombre'] ?? '') ?>" placeholder="Tu nombre" required autofocus>
      </div>
      <div class="fg">
        <label>Correo electrónico *</label>
        <input type="email" name="email" value="<?= h($_POST['email'] ?? '') ?>" placeholder="tu@email.com" required>
      </div>
      <div class="fg">
        <label>Contraseña * <small style="color:var(--brown);font-weight:400">(mínimo 6 caracteres)</small></label>
        <input type="password" name="password" placeholder="••••••••" required minlength="6">
      </div>
      <div class="fg">
        <label>Confirmar contraseña *</label>
        <input type="password" name="password2" placeholder="••••••••" required>
      </div>
      <button type="submit" class="btn btn-amber" style="width:100%;justify-content:center;margin-top:6px">
        Crear cuenta →
      </button>
    </form>

    <div class="auth-divider">¿Ya tienes cuenta?</div>
    <a href="login.php" class="btn btn-outline" style="width:100%;justify-content:center">Iniciar sesión</a>
    <div style="text-align:center;margin-top:18px">
      <a href="index.php" style="font-size:.82rem;color:var(--brown);text-decoration:none">← Volver al inicio</a>
    </div>
  </div>
</div>

<div id="toast" class="toast"></div>
<script src="js/app.js"></script>
</body>
</html>
