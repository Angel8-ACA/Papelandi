<?php
require_once '../includes/config.php';
$pageTitle = 'Catálogo — ' . SITE_NAME;

$cat_id = (int)($_GET['cat'] ?? 0);
$cats   = db()->query("SELECT * FROM categorias ORDER BY nombre");

$where = $cat_id ? "WHERE p.categoria_id=$cat_id" : '';
$prods = db()->query("SELECT p.*,c.nombre AS cat_n,c.icono AS cat_ico FROM productos p LEFT JOIN categorias c ON c.id=p.categoria_id $where ORDER BY p.destacado DESC,p.nombre ASC");

// Todos los productos en JSON para filtrado client-side
$all_prods = db()->query("SELECT p.*,c.nombre AS cat_n,c.icono AS cat_ico FROM productos p LEFT JOIN categorias c ON c.id=p.categoria_id ORDER BY p.destacado DESC,p.nombre ASC");
$all_json  = json_encode($all_prods->fetch_all(MYSQLI_ASSOC));

include '../includes/header.php';
?>

<div class="container" style="padding-bottom:60px">
  <div class="breadcrumb">
    <a href="../index.php">Inicio</a><span>›</span><span>Catálogo</span>
  </div>

  <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:14px;margin:24px 0 20px">
    <h1 style="font-family:var(--fd);font-size:1.9rem;font-weight:900">Catálogo de productos</h1>

    <!-- Buscador AJAX -->
    <div style="position:relative">
      <div class="search-wrap">
        <span class="s-icon">🔍</span>
        <input type="text" id="searchInput" placeholder="Buscar en tiempo real..."
               autocomplete="off" style="min-width:270px" oninput="onSearch(this.value)">
        <span id="searchSpin" style="display:none;position:absolute;right:14px;top:50%;transform:translateY(-50%);font-size:.75rem;color:var(--brown)">⏳</span>
      </div>
      <div id="searchDrop" style="display:none;position:absolute;top:calc(100%+4px);left:0;right:0;background:var(--white);border:2px solid var(--ink);border-radius:var(--rl);box-shadow:0 12px 32px rgba(26,18,8,.16);z-index:500;max-height:300px;overflow-y:auto"></div>
    </div>
  </div>

  <!-- Filtros categoría -->
  <div class="filters" id="catFiltros">
    <button class="chip <?= !$cat_id?'active':'' ?>" onclick="filtrarCat(0,this)">📦 Todos</button>
    <?php $cats->data_seek(0); while($c=$cats->fetch_assoc()): ?>
    <button class="chip <?= $cat_id==$c['id']?'active':'' ?>" onclick="filtrarCat(<?= $c['id'] ?>,this)">
      <?= $c['icono'].' '.h($c['nombre']) ?>
    </button>
    <?php endwhile; ?>
  </div>

  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;flex-wrap:wrap;gap:10px">
    <p id="prodCount" style="font-size:.82rem;color:var(--brown)"><?= $prods->num_rows ?> productos encontrados</p>
    <select class="chip" style="border-radius:var(--r);padding:7px 12px;cursor:pointer" onchange="ordenar(this.value)">
      <option value="">Ordenar por...</option>
      <option value="precio_asc">Precio: menor a mayor</option>
      <option value="precio_desc">Precio: mayor a menor</option>
      <option value="nombre">Nombre A–Z</option>
      <option value="dest">Destacados primero</option>
    </select>
  </div>

  <div class="prod-grid" id="prodGrid">
    <?php while($p=$prods->fetch_assoc()): ?>
    <div class="prod-card fade-in" data-id="<?= $p['id'] ?>">
      <a href="producto.php?id=<?= $p['id'] ?>" style="text-decoration:none;color:inherit">
        <div class="prod-img">
          <span><?= $p['cat_ico']??'📦' ?></span>
          <?php if($p['destacado']): ?><span class="badge badge-star">⭐ Destacado</span><?php endif; ?>
          <?php if(!$p['stock']): ?><span class="badge badge-out">Agotado</span><?php endif; ?>
        </div>
      </a>
      <div class="prod-body">
        <div class="prod-cat"><?= ($p['cat_ico']??'').' '.h($p['cat_n']??'') ?></div>
        <a href="producto.php?id=<?= $p['id'] ?>" style="text-decoration:none;color:inherit">
          <h3 class="prod-name"><?= h($p['nombre']) ?></h3>
        </a>
        <p class="prod-desc"><?= h(mb_substr($p['descripcion']??'',0,85)) ?>...</p>
        <div class="prod-footer">
          <div class="prod-price"><small>$</small><?= number_format($p['precio'],2) ?></div>
          <?php if($p['stock']>0): ?>
            <?php if(isLogged()): ?>
              <button class="btn-add" onclick="addToCart(<?= $p['id'] ?>,'<?= addslashes($p['nombre']) ?>',<?= $p['precio'] ?>,'<?= addslashes($p['cat_n']??'') ?>')">+ Agregar</button>
            <?php else: ?>
              <a href="../login.php" class="btn-add" style="text-decoration:none;display:inline-flex">🔑 Login</a>
            <?php endif; ?>
          <?php else: ?>
            <button class="btn-add" disabled>Agotado</button>
          <?php endif; ?>
        </div>
        <div class="<?= $p['stock']<10&&$p['stock']>0?'stock-low':'stock-ok' ?>">
          <?= $p['stock']==0?'❌ Sin stock':($p['stock']<10?'⚠️ Últimas '.$p['stock'].' uds':'✓ En stock') ?>
        </div>
      </div>
    </div>
    <?php endwhile; ?>
  </div>

  <div id="sinResultados" style="display:none" class="empty">
    <div class="empty-ico">🔍</div>
    <h3>Sin resultados</h3>
    <p>Prueba con otra búsqueda o categoría</p>
  </div>
</div>

<script>
const TODOS = <?= $all_json ?>;
const LOGGED = <?= isLogged()?'true':'false' ?>;
let catActiva = <?= $cat_id ?>;
let mostrados = TODOS.filter(p => !catActiva || p.categoria_id == catActiva);

function filtrarCat(id, btn) {
  catActiva = id;
  document.querySelectorAll('#catFiltros .chip').forEach(b=>b.classList.remove('active'));
  if(btn) btn.classList.add('active');
  const q = document.getElementById('searchInput').value.trim().toLowerCase();
  aplicarFiltros(q);
  cerrarDrop();
}

function aplicarFiltros(q) {
  mostrados = TODOS.filter(p =>
    (!catActiva || p.categoria_id == catActiva) &&
    (!q || p.nombre.toLowerCase().includes(q) || (p.descripcion||'').toLowerCase().includes(q))
  );
  renderGrid(mostrados);
}

function ordenar(val) {
  const arr = [...mostrados];
  if(val==='precio_asc')  arr.sort((a,b)=>a.precio-b.precio);
  if(val==='precio_desc') arr.sort((a,b)=>b.precio-a.precio);
  if(val==='nombre')      arr.sort((a,b)=>a.nombre.localeCompare(b.nombre,'es'));
  if(val==='dest')        arr.sort((a,b)=>b.destacado-a.destacado);
  renderGrid(arr);
}

let debT;
function onSearch(q) {
  aplicarFiltros(q.trim().toLowerCase());
  clearTimeout(debT);
  if(q.trim().length < 2) { cerrarDrop(); return; }
  document.getElementById('searchSpin').style.display='block';
  debT = setTimeout(()=>{
    fetch(`../api/buscar.php?q=${encodeURIComponent(q)}&cat=${catActiva}`)
      .then(r=>r.json())
      .then(items=>{
        document.getElementById('searchSpin').style.display='none';
        if(!items.length){cerrarDrop();return;}
        const drop=document.getElementById('searchDrop');
        drop.style.display='block';
        drop.innerHTML=items.slice(0,7).map(p=>`
          <a href="producto.php?id=${p.id}" style="display:flex;align-items:center;gap:12px;padding:11px 15px;text-decoration:none;color:var(--ink);border-bottom:1px solid var(--border)" onmouseover="this.style.background='var(--paper)'" onmouseout="this.style.background=''">
            <span style="font-size:1.5rem">${p.cat_ico||'📦'}</span>
            <div style="flex:1;min-width:0">
              <div style="font-weight:600;font-size:.87rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${esc(p.nombre)}</div>
              <div style="font-size:.73rem;color:var(--brown)">${esc(p.cat_n||'')} · $${parseFloat(p.precio).toFixed(2)}</div>
            </div>
            <span style="font-family:var(--fd);font-weight:900;color:var(--amber);white-space:nowrap">$${parseFloat(p.precio).toFixed(2)}</span>
          </a>`).join('')+
          (items.length>7?`<div style="padding:9px 15px;font-size:.75rem;color:var(--brown);text-align:center">+${items.length-7} más...</div>`:'');
      }).catch(()=>{document.getElementById('searchSpin').style.display='none';});
  },300);
}

function cerrarDrop(){document.getElementById('searchDrop').style.display='none';}
document.addEventListener('click',e=>{if(!e.target.closest('.search-wrap')&&!e.target.closest('#searchDrop'))cerrarDrop();});

function renderGrid(prods) {
  const grid=document.getElementById('prodGrid');
  const sin=document.getElementById('sinResultados');
  document.getElementById('prodCount').textContent=prods.length+' producto'+(prods.length!==1?'s':'')+' encontrado'+(prods.length!==1?'s':'');
  if(!prods.length){grid.style.display='none';sin.style.display='';return;}
  grid.style.display='';sin.style.display='none';
  grid.innerHTML=prods.map(p=>`
    <div class="prod-card fade-in">
      <a href="producto.php?id=${p.id}" style="text-decoration:none;color:inherit">
        <div class="prod-img">
          <span>${p.cat_ico||'📦'}</span>
          ${p.destacado=='1'?'<span class="badge badge-star">⭐ Destacado</span>':''}
          ${p.stock==0?'<span class="badge badge-out">Agotado</span>':''}
        </div>
      </a>
      <div class="prod-body">
        <div class="prod-cat">${p.cat_ico||''} ${esc(p.cat_n||'')}</div>
        <a href="producto.php?id=${p.id}" style="text-decoration:none;color:inherit">
          <h3 class="prod-name">${esc(p.nombre)}</h3>
        </a>
        <p class="prod-desc">${esc((p.descripcion||'').substring(0,85))}...</p>
        <div class="prod-footer">
          <div class="prod-price"><small>$</small>${parseFloat(p.precio).toFixed(2)}</div>
          ${p.stock>0
            ? LOGGED
              ? `<button class="btn-add" onclick="addToCart(${p.id},'${escJs(p.nombre)}',${p.precio},'${escJs(p.cat_n||'')}')">+ Agregar</button>`
              : `<a href="../login.php" class="btn-add" style="text-decoration:none;display:inline-flex">🔑 Login</a>`
            : `<button class="btn-add" disabled>Agotado</button>`
          }
        </div>
        <div class="${p.stock<10&&p.stock>0?'stock-low':'stock-ok'}">
          ${p.stock==0?'❌ Sin stock':p.stock<10?`⚠️ Últimas ${p.stock} uds`:'✓ En stock'}
        </div>
      </div>
    </div>`).join('');
}

function esc(s){return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}
function escJs(s){return String(s).replace(/\\/g,'\\\\').replace(/'/g,"\\\'");}
</script>

<?php include '../includes/footer.php'; ?>
