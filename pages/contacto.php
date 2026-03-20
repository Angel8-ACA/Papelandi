<?php
require_once '../includes/config.php';
$pageTitle = 'Contacto — ' . SITE_NAME;
$ok = $err = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $nombre = trim($_POST['nombre']??'');
    $email  = trim($_POST['email']??'');
    $msg    = trim($_POST['mensaje']??'');
    if ($nombre && $email && $msg) $ok = "¡Gracias $nombre! Recibimos tu mensaje y te contactaremos pronto.";
    else $err = 'Por favor completa todos los campos.';
}
include '../includes/header.php';
?>
<div class="container" style="padding-bottom:80px">
  <div class="breadcrumb"><a href="../index.php">Inicio</a><span>›</span><span>Contacto</span></div>
  <div class="contact-grid">
    <div class="contact-info">
      <h2>Estamos aquí<br><em>para ayudarte</em></h2>
      <p style="color:var(--brown);font-size:.9rem;line-height:1.7;margin-bottom:26px">¿Dudas sobre un producto? ¿Pedido especial? Contáctanos y respondemos a la brevedad.</p>
      <div class="c-item"><div class="c-icon">📍</div><div><h4>Dirección</h4><p>Av. Madero 123, Centro Histórico<br>Morelia, Michoacán, C.P. 58000</p></div></div>
      <div class="c-item"><div class="c-icon">📞</div><div><h4>Teléfono</h4><p>(443) 123-4567<br>WhatsApp: 443 765 4321</p></div></div>
      <div class="c-item"><div class="c-icon">📧</div><div><h4>Correo</h4><p>hola@rincon.com</p></div></div>
      <div class="c-item"><div class="c-icon">🕐</div><div><h4>Horario</h4><p>Lun–Vie: 9am–8pm · Sáb: 10am–6pm</p></div></div>
    </div>
    <div class="form-box">
      <h3 style="font-family:var(--fd);font-size:1.5rem;font-weight:700;margin-bottom:6px">Envíanos un mensaje</h3>
      <p style="color:var(--brown);font-size:.85rem;margin-bottom:22px">Respondemos en menos de 24 horas hábiles</p>
      <?php if ($ok): ?><div class="alert alert-ok"><?= h($ok) ?></div><?php endif; ?>
      <?php if ($err): ?><div class="alert alert-err"><?= h($err) ?></div><?php endif; ?>
      <form method="POST">
        <div class="form-row">
          <div class="fg"><label>Nombre *</label><input type="text" name="nombre" placeholder="Tu nombre" required></div>
          <div class="fg"><label>Email *</label><input type="email" name="email" placeholder="tu@email.com" required></div>
        </div>
        <div class="fg"><label>Asunto</label><select name="asunto">
          <option>Consulta de producto</option><option>Pedido especial</option>
          <option>Cotización empresa</option><option>Devolución</option><option>Otro</option>
        </select></div>
        <div class="fg"><label>Mensaje *</label><textarea name="mensaje" rows="5" placeholder="Escribe tu mensaje..." required></textarea></div>
        <button type="submit" class="btn btn-amber" style="width:100%;justify-content:center">Enviar mensaje →</button>
      </form>
    </div>
  </div>
</div>
<?php include '../includes/footer.php'; ?>
