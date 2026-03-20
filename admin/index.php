<?php
require_once '../includes/config.php';
requireAdmin();
$pageTitle = 'Admin — ' . SITE_NAME;

$msg = $msg_t = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $a = $_POST['action'];

    if ($a === 'add_product') {
        $n  = db()->real_escape_string(trim($_POST['nombre']));
        $d  = db()->real_escape_string(trim($_POST['desc']));
        $pr = (float)$_POST['precio'];
        $s  = (int)$_POST['stock'];
        $c  = (int)$_POST['cat'];
        $de = isset($_POST['dest']) ? 1 : 0;
        db()->query("INSERT INTO productos (nombre,descripcion,precio,stock,categoria_id,destacado) VALUES ('$n','$d',$pr,$s,$c,$de)");
        $msg = '✅ Producto agregado'; $msg_t = 'ok';
    }

    if ($a === 'edit_product') {
        $id = (int)$_POST['id'];
        $n  = db()->real_escape_string(trim($_POST['nombre']));
        $d  = db()->real_escape_string(trim($_POST['desc']));
        $pr = (float)$_POST['precio'];
        $s  = (int)$_POST['stock'];
        $c  = (int)$_POST['cat'];
        $de = isset($_POST['dest']) ? 1 : 0;
        db()->query("UPDATE productos SET nombre='$n',descripcion='$d',precio=$pr,stock=$s,categoria_id=$c,destacado=$de WHERE id=$id");
        $msg = '✅ Producto actualizado'; $msg_t = 'ok';
    }

    if ($a === 'delete_product') {
        db()->query("DELETE FROM productos WHERE id=".(int)$_POST['id']);
        $msg = '🗑️ Producto eliminado'; $msg_t = 'info';
    }

    if ($a === 'toggle_dest') {
        $id = (int)$_POST['id']; $val = (int)$_POST['val'];
        db()->query("UPDATE productos SET destacado=$val WHERE id=$id");
        $msg = '⭐ Producto '.(($val)?'marcado':'desmarcado').' como destacado'; $msg_t='ok';
    }

    if ($a === 'update_stock') {
        db()->query("UPDATE productos SET stock=".(int)$_POST['stock']." WHERE id=".(int)$_POST['id']);
        $msg = '✅ Stock actualizado'; $msg_t = 'ok';
    }

    if ($a === 'delete_user') {
        $id = (int)$_POST['id'];
        if ($id !== userId()) { db()->query("DELETE FROM usuarios WHERE id=$id"); $msg='🗑️ Usuario eliminado'; $msg_t='info'; }
    }

    if ($a === 'change_rol') {
        $id = (int)$_POST['id']; $rol = $_POST['rol']==='admin'?'admin':'cliente';
        if ($id !== userId()) { db()->query("UPDATE usuarios SET rol='$rol' WHERE id=$id"); $msg="✅ Rol actualizado"; $msg_t='ok'; }
    }
}

// Stats
$total_p = db()->query("SELECT COUNT(*) t FROM productos")->fetch_assoc()['t'];
$total_u = db()->query("SELECT COUNT(*) t FROM usuarios")->fetch_assoc()['t'];
$total_v = db()->query("SELECT COUNT(*) t FROM ventas")->fetch_assoc()['t'];
$ingreso = db()->query("SELECT COALESCE(SUM(total),0) t FROM ventas")->fetch_assoc()['t'];
$bajo    = db()->query("SELECT COUNT(*) t FROM productos WHERE stock<10")->fetch_assoc()['t'];
$agotado = db()->query("SELECT COUNT(*) t FROM productos WHERE stock=0")->fetch_assoc()['t'];

$productos  = db()->query("SELECT p.*,c.nombre AS cn,c.icono AS ci FROM productos p LEFT JOIN categorias c ON c.id=p.categoria_id ORDER BY p.id DESC");
$usuarios   = db()->query("SELECT u.*,(SELECT COUNT(*) FROM ventas WHERE usuario_id=u.id) AS pedidos FROM usuarios u ORDER BY u.created_at DESC");
$ventas     = db()->query("SELECT v.*,u.nombre AS un FROM ventas v LEFT JOIN usuarios u ON u.id=v.usuario_id ORDER BY v.fecha DESC LIMIT 30");
$categorias = db()->query("SELECT * FROM categorias ORDER BY nombre");

// Producto a editar (si se abre modal por GET)
$edit_id = (int)($_GET['edit'] ?? 0);
$edit_p  = $edit_id ? db()->query("SELECT * FROM productos WHERE id=$edit_id")->fetch_assoc() : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?= $pageTitle ?></title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,900;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../css/style.css">
</head>
<body>

<!-- Topbar -->
<div style="background:var(--ink);color:var(--cream);padding:13px 28px;display:flex;justify-content:space-between;align-items:center;position:sticky;top:0;z-index:200;border-bottom:2px solid var(--amber)">
  <div style="font-family:var(--fd);font-size:1.1rem;font-weight:700">✒️ Panel Admin · El Rincón del Saber</div>
  <div style="display:flex;gap:16px;align-items:center">
    <span style="font-size:.8rem;color:rgba(250,247,242,.6)">👤 <?= h(userName()) ?></span>
    <a href="../logout.php" style="font-size:.8rem;color:var(--amber-lt);text-decoration:none">Cerrar sesión</a>
    <a href="../index.php"  style="font-size:.8rem;color:rgba(250,247,242,.5);text-decoration:none">← Ver sitio</a>
  </div>
</div>

<div class="admin-wrap">
  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="sidebar-lbl">Panel</div>
    <a href="#stats">📊 Dashboard</a>
    <a href="#productos">📦 Productos</a>
    <a href="#usuarios">👥 Usuarios</a>
    <a href="#ventas">🧾 Ventas</a>
    <br>
    <div class="sidebar-lbl">Acciones rápidas</div>
    <a href="#" onclick="abrirAdd();return false">➕ Nuevo producto</a>
    <a href="../index.php">🏠 Ver sitio</a>
    <a href="../logout.php" style="color:rgba(196,85,58,.8)!important">🚪 Salir</a>
  </aside>

  <!-- Contenido -->
  <main class="admin-main">

    <?php if($msg): ?>
    <div class="alert alert-<?= $msg_t==='ok'?'ok':'info' ?>"><?= $msg ?></div>
    <?php endif; ?>

    <!-- STATS -->
    <section id="stats">
      <h2 class="admin-title">Dashboard</h2>
      <div class="stats-g">
        <div class="stat"><div class="stat-ico">📦</div><div class="stat-val"><?= $total_p ?></div><div class="stat-lbl">Productos totales</div></div>
        <div class="stat"><div class="stat-ico">👥</div><div class="stat-val"><?= $total_u ?></div><div class="stat-lbl">Usuarios registrados</div></div>
        <div class="stat"><div class="stat-ico">🧾</div><div class="stat-val"><?= $total_v ?></div><div class="stat-lbl">Ventas realizadas</div></div>
        <div class="stat"><div class="stat-ico">💰</div><div class="stat-val">$<?= number_format($ingreso,0) ?></div><div class="stat-lbl">Ingresos totales</div></div>
        <div class="stat" style="--amber:var(--terra)"><div class="stat-ico">⚠️</div><div class="stat-val"><?= $bajo ?></div><div class="stat-lbl">Stock bajo (&lt;10)</div></div>
        <div class="stat" style="--amber:#b91c1c"><div class="stat-ico">❌</div><div class="stat-val"><?= $agotado ?></div><div class="stat-lbl">Sin stock</div></div>
      </div>
    </section>

    <!-- PRODUCTOS -->
    <section id="productos" style="margin-top:44px">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;flex-wrap:wrap;gap:12px">
        <h2 class="admin-title" style="margin:0">Productos <span style="font-size:.8rem;font-family:var(--fb);color:var(--brown);font-weight:400">(<?= $total_p ?>)</span></h2>
        <button class="btn btn-amber btn-sm" onclick="abrirAdd()">➕ Nuevo producto</button>
      </div>

      <!-- Búsqueda rápida en tabla -->
      <div class="search-wrap" style="margin-bottom:14px;max-width:300px">
        <span class="s-icon">🔍</span>
        <input type="text" placeholder="Filtrar tabla..." oninput="filtrarTabla(this.value,'tbl-prods')">
      </div>

      <div style="overflow-x:auto">
        <table class="data-tbl" id="tbl-prods">
          <thead><tr><th>ID</th><th>Producto</th><th>Categoría</th><th>Precio</th><th>Stock</th><th>Dest.</th><th style="text-align:right">Acciones</th></tr></thead>
          <tbody>
          <?php $productos->data_seek(0); while($p=$productos->fetch_assoc()): ?>
          <tr data-search="<?= strtolower(h($p['nombre']).' '.$p['cn']) ?>">
            <td style="color:var(--brown);font-size:.74rem">#<?= $p['id'] ?></td>
            <td>
              <a href="../pages/producto.php?id=<?= $p['id'] ?>" target="_blank" style="font-weight:600;color:var(--ink);text-decoration:none"><?= h($p['nombre']) ?></a>
            </td>
            <td><?= ($p['ci']??'').' '.h($p['cn']??'—') ?></td>
            <td><strong>$<?= number_format($p['precio'],2) ?></strong></td>
            <td>
              <form method="POST" style="display:inline-flex;gap:5px;align-items:center">
                <input type="hidden" name="action" value="update_stock">
                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                <input type="number" name="stock" value="<?= $p['stock'] ?>" min="0" class="qty-inp" style="width:60px">
                <button type="submit" style="background:var(--sage);color:#fff;border:none;padding:4px 9px;border-radius:4px;cursor:pointer;font-size:.7rem">✓</button>
              </form>
              <?php if($p['stock']==0): ?><small style="color:var(--terra);font-weight:600;font-size:.7rem"> ❌</small>
              <?php elseif($p['stock']<10): ?><small class="stock-low"> ⚠️</small><?php endif; ?>
            </td>
            <td>
              <form method="POST" style="display:inline">
                <input type="hidden" name="action" value="toggle_dest">
                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                <input type="hidden" name="val" value="<?= $p['destacado']?0:1 ?>">
                <button type="submit" style="background:none;border:none;cursor:pointer;font-size:1.1rem" title="<?= $p['destacado']?'Quitar destacado':'Marcar destacado' ?>">
                  <?= $p['destacado']?'⭐':'☆' ?>
                </button>
              </form>
            </td>
            <td style="text-align:right">
              <button class="btn btn-outline btn-sm" onclick='abrirEdit(<?= json_encode($p) ?>)' style="margin-right:6px">✏️ Editar</button>
              <form method="POST" style="display:inline" onsubmit="return confirm('¿Eliminar este producto?')">
                <input type="hidden" name="action" value="delete_product">
                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                <button type="submit" class="btn btn-danger btn-sm">🗑️</button>
              </form>
            </td>
          </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </section>

    <!-- USUARIOS -->
    <section id="usuarios" style="margin-top:44px">
      <h2 class="admin-title">Usuarios <span style="font-size:.8rem;font-family:var(--fb);color:var(--brown);font-weight:400">(<?= $total_u ?>)</span></h2>

      <div class="search-wrap" style="margin-bottom:14px;max-width:300px">
        <span class="s-icon">🔍</span>
        <input type="text" placeholder="Filtrar usuarios..." oninput="filtrarTabla(this.value,'tbl-users')">
      </div>

      <div style="overflow-x:auto">
        <table class="data-tbl" id="tbl-users">
          <thead><tr><th>ID</th><th>Nombre</th><th>Email</th><th>Rol</th><th>Pedidos</th><th>Registro</th><th>Acciones</th></tr></thead>
          <tbody>
          <?php while($u=$usuarios->fetch_assoc()): ?>
          <tr data-search="<?= strtolower(h($u['nombre']).' '.$u['email']) ?>">
            <td style="color:var(--brown);font-size:.74rem">#<?= $u['id'] ?></td>
            <td><strong><?= h($u['nombre']) ?></strong></td>
            <td style="font-size:.83rem"><?= h($u['email']) ?></td>
            <td>
              <?php if($u['id']!==userId()): ?>
              <form method="POST" style="display:inline-flex;gap:5px;align-items:center">
                <input type="hidden" name="action" value="change_rol">
                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                <select name="rol" onchange="this.form.submit()" style="font-size:.78rem;padding:4px 8px;border:2px solid var(--border);border-radius:var(--r);background:var(--white);cursor:pointer">
                  <option value="cliente" <?= $u['rol']==='cliente'?'selected':'' ?>>👤 Cliente</option>
                  <option value="admin"   <?= $u['rol']==='admin'  ?'selected':'' ?>>⚙️ Admin</option>
                </select>
              </form>
              <?php else: ?>
              <span style="background:var(--amber);color:var(--ink);padding:3px 10px;border-radius:100px;font-size:.72rem;font-weight:700">⚙️ Admin (tú)</span>
              <?php endif; ?>
            </td>
            <td style="text-align:center"><strong><?= $u['pedidos'] ?></strong></td>
            <td style="font-size:.78rem;color:var(--brown)"><?= date('d/m/Y',strtotime($u['created_at'])) ?></td>
            <td>
              <?php if($u['id']!==userId()): ?>
              <form method="POST" onsubmit="return confirm('¿Eliminar usuario? Sus pedidos quedarán sin asignar.')">
                <input type="hidden" name="action" value="delete_user">
                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                <button type="submit" class="btn btn-danger btn-sm">🗑️</button>
              </form>
              <?php else: ?><span style="font-size:.74rem;color:var(--brown)">—</span><?php endif; ?>
            </td>
          </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </section>

    <!-- VENTAS -->
    <section id="ventas" style="margin-top:44px;padding-bottom:60px">
      <h2 class="admin-title">Ventas recientes <span style="font-size:.8rem;font-family:var(--fb);color:var(--brown);font-weight:400">(últimas 30)</span></h2>
      <div style="overflow-x:auto">
        <table class="data-tbl">
          <thead><tr><th>ID</th><th>Cliente</th><th>Total</th><th>Fecha</th><th>Detalle</th></tr></thead>
          <tbody>
          <?php if(!$ventas->num_rows): ?>
          <tr><td colspan="5" style="text-align:center;padding:32px;color:var(--brown)">Sin ventas registradas aún</td></tr>
          <?php else: while($v=$ventas->fetch_assoc()):
            $det = db()->query("SELECT dv.cantidad,dv.precio_unitario,p.nombre FROM detalle_ventas dv LEFT JOIN productos p ON p.id=dv.producto_id WHERE dv.venta_id={$v['id']}");
          ?>
          <tr>
            <td style="font-size:.76rem;color:var(--brown)">#<?= $v['id'] ?></td>
            <td><strong><?= h($v['un']??'Anónimo') ?></strong></td>
            <td><strong style="color:var(--sage)">$<?= number_format($v['total'],2) ?></strong></td>
            <td style="font-size:.8rem;color:var(--brown)"><?= date('d/m/Y H:i',strtotime($v['fecha'])) ?></td>
            <td>
              <button class="btn btn-outline btn-sm" onclick="this.nextElementSibling.style.display=this.nextElementSibling.style.display==='none'?'block':'none'">Ver items</button>
              <div style="display:none;margin-top:8px;font-size:.78rem;line-height:1.8;background:var(--paper);padding:8px 12px;border-radius:var(--r)">
                <?php while($it=$det->fetch_assoc()): ?>
                • <?= h($it['nombre']??'Eliminado') ?> ×<?= $it['cantidad'] ?> — $<?= number_format($it['precio_unitario'],2) ?><br>
                <?php endwhile; ?>
              </div>
            </td>
          </tr>
          <?php endwhile; endif; ?>
          </tbody>
        </table>
      </div>
    </section>
  </main>
</div>

<!-- MODAL Agregar producto -->
<div class="overlay" id="modal-add">
  <div class="modal">
    <div class="modal-head">
      <h2>➕ Nuevo producto</h2>
      <button class="modal-close" onclick="closeModal('modal-add')">✕</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="add_product">
      <div class="fg"><label>Nombre *</label><input type="text" name="nombre" required placeholder="Ej: Pluma BIC Azul"></div>
      <div class="fg"><label>Descripción</label><textarea name="desc" rows="3" placeholder="Descripción breve del producto..."></textarea></div>
      <div class="form-row">
        <div class="fg"><label>Precio (MXN) *</label><input type="number" name="precio" required min="0.01" step="0.01" placeholder="0.00"></div>
        <div class="fg"><label>Stock *</label><input type="number" name="stock" required min="0" value="0"></div>
      </div>
      <div class="fg"><label>Categoría *</label>
        <select name="cat" required>
          <option value="">Selecciona...</option>
          <?php $categorias->data_seek(0); while($c=$categorias->fetch_assoc()): ?>
          <option value="<?= $c['id'] ?>"><?= $c['icono'].' '.h($c['nombre']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="fg" style="display:flex;align-items:center;gap:10px">
        <input type="checkbox" name="dest" id="add-dest" style="width:17px;height:17px">
        <label for="add-dest" style="margin:0;cursor:pointer;font-size:.88rem">⭐ Marcar como destacado</label>
      </div>
      <button type="submit" class="btn btn-amber" style="width:100%;justify-content:center;margin-top:6px">Guardar producto →</button>
    </form>
  </div>
</div>

<!-- MODAL Editar producto -->
<div class="overlay" id="modal-edit">
  <div class="modal">
    <div class="modal-head">
      <h2>✏️ Editar producto</h2>
      <button class="modal-close" onclick="closeModal('modal-edit')">✕</button>
    </div>
    <form method="POST" id="form-edit">
      <input type="hidden" name="action" value="edit_product">
      <input type="hidden" name="id" id="edit-id">
      <div class="fg"><label>Nombre *</label><input type="text" name="nombre" id="edit-nombre" required></div>
      <div class="fg"><label>Descripción</label><textarea name="desc" id="edit-desc" rows="3"></textarea></div>
      <div class="form-row">
        <div class="fg"><label>Precio (MXN) *</label><input type="number" name="precio" id="edit-precio" required min="0.01" step="0.01"></div>
        <div class="fg"><label>Stock *</label><input type="number" name="stock" id="edit-stock" required min="0"></div>
      </div>
      <div class="fg"><label>Categoría *</label>
        <select name="cat" id="edit-cat" required>
          <option value="">Selecciona...</option>
          <?php $categorias->data_seek(0); while($c=$categorias->fetch_assoc()): ?>
          <option value="<?= $c['id'] ?>"><?= $c['icono'].' '.h($c['nombre']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="fg" style="display:flex;align-items:center;gap:10px">
        <input type="checkbox" name="dest" id="edit-dest" style="width:17px;height:17px">
        <label for="edit-dest" style="margin:0;cursor:pointer;font-size:.88rem">⭐ Marcar como destacado</label>
      </div>
      <button type="submit" class="btn btn-amber" style="width:100%;justify-content:center;margin-top:6px">Guardar cambios →</button>
    </form>
  </div>
</div>

<div id="toast" class="toast"></div>
<script src="../js/app.js"></script>
<script>
function abrirAdd() { openModal('modal-add'); }

function abrirEdit(p) {
  document.getElementById('edit-id').value     = p.id;
  document.getElementById('edit-nombre').value = p.nombre;
  document.getElementById('edit-desc').value   = p.descripcion || '';
  document.getElementById('edit-precio').value = p.precio;
  document.getElementById('edit-stock').value  = p.stock;
  document.getElementById('edit-cat').value    = p.categoria_id;
  document.getElementById('edit-dest').checked = p.destacado == 1;
  openModal('modal-edit');
}

// Filtrado rápido de tablas
function filtrarTabla(q, tblId) {
  const term = q.toLowerCase();
  document.querySelectorAll('#'+tblId+' tbody tr').forEach(tr => {
    const txt = tr.getAttribute('data-search') || tr.textContent.toLowerCase();
    tr.style.display = txt.includes(term) ? '' : 'none';
  });
}
</script>
</body>
</html>
