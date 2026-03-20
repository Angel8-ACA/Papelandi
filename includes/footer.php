
<footer class="footer">
  <div class="container footer-g">
    <div class="f-brand">
      <div class="f-logo">✒️ El Rincón del Saber</div>
      <p>Tu papelería de confianza desde 1998. Calidad, variedad y el mejor precio en artículos escolares y de oficina.</p>
      <div class="f-social"><span>📘</span><span>📷</span><span>💬</span></div>
    </div>
    <div><h4>Navegación</h4><ul>
      <li><a href="<?= $base ?>index.php">Inicio</a></li>
      <li><a href="<?= $base ?>pages/catalogo.php">Catálogo</a></li>
      <li><a href="<?= $base ?>pages/contacto.php">Contacto</a></li>
    </ul></div>
    <div><h4>Categorías</h4><ul>
      <li><a href="<?= $base ?>pages/catalogo.php?cat=1">✏️ Escritura</a></li>
      <li><a href="<?= $base ?>pages/catalogo.php?cat=2">📄 Papel</a></li>
      <li><a href="<?= $base ?>pages/catalogo.php?cat=3">🎨 Arte</a></li>
      <li><a href="<?= $base ?>pages/catalogo.php?cat=4">📎 Oficina</a></li>
    </ul></div>
    <div><h4>Contacto</h4>
      <p>📍 Av. Madero 123, Centro</p>
      <p>📞 (443) 123-4567</p>
      <p>📧 hola@rincon.com</p>
      <p>🕐 Lun–Sáb: 9am – 8pm</p>
    </div>
  </div>
  <div class="footer-bot">© <?= date('Y') ?> El Rincón del Saber · Todos los derechos reservados</div>
</footer>

<div id="toast" class="toast"></div>
<script src="<?= $base ?>js/app.js"></script>
</body>
</html>
