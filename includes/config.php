<?php
// ── CONFIGURACIÓN ────────────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'papeleria_db');
define('SITE_NAME', 'El Rincón del Saber');

// ── Detectar URL base automáticamente ────────────────────
define('BASE_URL', rtrim(str_replace(
    str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']),
    '',
    str_replace('\\', '/', dirname(dirname(__FILE__)))
), '/') . '/');

// Iniciar sesión siempre
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── CONEXIÓN ─────────────────────────────────────────────
function db(): mysqli {
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $conn->set_charset('utf8mb4');
        if ($conn->connect_error) {
            die("
            <style>body{font-family:sans-serif;padding:40px;background:#fef2f2}
            .err{background:#fff;border:2px solid #f87171;border-radius:12px;padding:32px;max-width:560px;margin:auto}
            h2{color:#dc2626;margin-bottom:12px} code{background:#fef2f2;padding:4px 8px;border-radius:4px}
            ul{margin:12px 0 0 20px;line-height:2}</style>
            <div class='err'>
            <h2>❌ Error de conexión a MySQL</h2>
            <p>No se pudo conectar a la base de datos.</p>
            <ul>
              <li>¿Está <strong>MySQL iniciado</strong> en XAMPP?</li>
              <li>¿Importaste <code>papeleria.sql</code> en phpMyAdmin?</li>
              <li>Revisa usuario/contraseña en <code>includes/config.php</code></li>
            </ul>
            <p style='margin-top:16px;color:#6b7280;font-size:.9rem'>Error: " . $conn->connect_error . "</p>
            </div>");
        }
    }
    return $conn;
}

// ── HELPERS DE SESIÓN ────────────────────────────────────
function isLogged(): bool  { return isset($_SESSION['usuario_id']); }
function isAdmin():  bool  { return isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'; }
function userName(): string { return $_SESSION['nombre'] ?? 'Usuario'; }
function userId():   int    { return (int)($_SESSION['usuario_id'] ?? 0); }

function requireLogin(): void {
    if (!isLogged()) {
        header('Location: ' . BASE_URL . 'login.php');
        exit;
    }
}

function requireAdmin(): void {
    requireLogin();
    if (!isAdmin()) {
        header('Location: ' . BASE_URL . 'index.php');
        exit;
    }
}

// ── UTILIDADES ────────────────────────────────────────────
function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

function basePath(): string {
    return BASE_URL;
}

$CAT_ICON = [1=>'✏️', 2=>'📄', 3=>'🎨', 4=>'📎', 5=>'💾', 6=>'🎒'];
