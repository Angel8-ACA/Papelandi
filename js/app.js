// ── CART (localStorage) ───────────────────────────────────
const CART_KEY = 'rincon_cart';

function getCart()   { return JSON.parse(localStorage.getItem(CART_KEY) || '[]'); }
function saveCart(c) { localStorage.setItem(CART_KEY, JSON.stringify(c)); updateBadge(); }

function updateBadge() {
  const n = getCart().reduce((s, i) => s + i.qty, 0);
  document.querySelectorAll('#cart-count').forEach(el => el.textContent = n);
}

function addToCart(id, nombre, precio, cat) {
  const cart = getCart();
  const idx  = cart.findIndex(x => x.id === id);
  if (idx >= 0) cart[idx].qty += 1;
  else cart.push({ id, nombre, precio: parseFloat(precio), cat, qty: 1 });
  saveCart(cart);
  toast('✅ "' + nombre + '" agregado al carrito');
}

function removeFromCart(id) {
  saveCart(getCart().filter(x => x.id !== id));
  renderCart();
}

function setQty(id, qty) {
  const cart = getCart();
  const idx  = cart.findIndex(x => x.id === id);
  if (idx < 0) return;
  const n = parseInt(qty);
  if (isNaN(n) || n <= 0) cart.splice(idx, 1);
  else cart[idx].qty = n;
  saveCart(cart);
  renderCart();
}

function clearCart() { localStorage.removeItem(CART_KEY); updateBadge(); renderCart(); }

// ── RENDER CART ───────────────────────────────────────────
function renderCart() {
  const tbody = document.getElementById('cart-body');
  const sumEl = document.getElementById('cart-sum');
  if (!tbody) return;
  const cart = getCart();

  if (!cart.length) {
    tbody.innerHTML = `<tr><td colspan="5">
      <div class="empty">
        <div class="empty-ico">🛒</div>
        <h3>Tu carrito está vacío</h3>
        <p>¡Agrega productos del catálogo!</p>
        <a href="../pages/catalogo.php" class="btn btn-dark" style="margin-top:16px">Ver catálogo</a>
      </div></td></tr>`;
    if (sumEl) sumEl.innerHTML = '<p style="color:var(--brown);text-align:center;font-size:.88rem">Sin productos</p>';
    return;
  }

  let sub = 0;
  tbody.innerHTML = cart.map(item => {
    const s = item.precio * item.qty; sub += s;
    return `<tr>
      <td><div style="display:flex;align-items:center;gap:10px">
        <span style="font-size:1.7rem">📦</span>
        <div><strong>${item.nombre}</strong><br><small style="color:var(--brown)">${item.cat||''}</small></div>
      </div></td>
      <td>$${Number(item.precio).toFixed(2)}</td>
      <td><input type="number" class="qty-inp" value="${item.qty}" min="0" max="99" onchange="setQty(${item.id},this.value)"></td>
      <td><strong>$${s.toFixed(2)}</strong></td>
      <td><button class="btn-rm" onclick="removeFromCart(${item.id})">🗑️</button></td>
    </tr>`;
  }).join('');

  const env = sub >= 500 ? 0 : 50;
  const tot = sub + env;

  if (sumEl) sumEl.innerHTML = `
    <div class="sum-row"><span>Subtotal</span><span>$${sub.toFixed(2)}</span></div>
    <div class="sum-row"><span>Envío</span><span>${env === 0 ? '<span style="color:var(--sage)">¡Gratis!</span>' : '$' + env.toFixed(2)}</span></div>
    ${sub < 500 ? '<small style="color:var(--brown);font-size:.72rem">Envío gratis en compras &gt;$500</small>' : ''}
    <div class="sum-total"><span>Total</span><span>$${tot.toFixed(2)}</span></div>
    <button class="btn btn-amber" style="width:100%;justify-content:center;margin-top:16px" onclick="checkout()">Finalizar compra →</button>
    <button class="btn btn-outline" style="width:100%;justify-content:center;margin-top:8px" onclick="clearCart()">Vaciar carrito</button>`;
}

// ── CHECKOUT ─────────────────────────────────────────────
function checkout() {
  const cart = getCart();
  if (!cart.length) return;
  fetch('../api/venta.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ items: cart })
  })
  .then(r => r.json())
  .then(d => {
    if (d.success) {
      clearCart();
      toast('🎉 ¡Compra realizada con éxito!');
      setTimeout(() => { window.location.href = '../pages/mis-pedidos.php'; }, 1200);
    } else {
      toast('❌ ' + (d.error || 'Error al procesar'));
    }
  })
  .catch(() => toast('❌ Error de conexión'));
}

// ── TOAST ─────────────────────────────────────────────────
function toast(msg) {
  const el = document.getElementById('toast');
  if (!el) return;
  el.textContent = msg; el.classList.add('show');
  clearTimeout(el._t);
  el._t = setTimeout(() => el.classList.remove('show'), 3000);
}

// ── HAMBURGER ─────────────────────────────────────────────
function toggleMenu() { document.getElementById('mobileNav')?.classList.toggle('open'); }

// ── USER DROPDOWN ─────────────────────────────────────────
function toggleUserMenu() { document.getElementById('userDrop')?.classList.toggle('open'); }

document.addEventListener('click', e => {
  const drop = document.getElementById('userDrop');
  if (drop && !e.target.closest('.user-menu')) drop.classList.remove('open');
});

// ── MODAL ─────────────────────────────────────────────────
function openModal(id)  { document.getElementById(id)?.classList.add('open'); }
function closeModal(id) { document.getElementById(id)?.classList.remove('open'); }
document.addEventListener('click', e => {
  if (e.target.classList.contains('overlay')) e.target.classList.remove('open');
});

// ── INIT ──────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  updateBadge();
  renderCart();
});
