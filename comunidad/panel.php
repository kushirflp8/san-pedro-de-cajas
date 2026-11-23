<?php
define('SPC_COMUNIDAD', 1);
require __DIR__ . '/lib.php';

session_set_cookie_params(['lifetime' => 0, 'path' => '/comunidad/', 'secure' => true, 'httponly' => true, 'samesite' => 'Strict']);
session_name('spc_panel');
session_start();
header('X-Robots-Tag: noindex, nofollow');
header('X-Frame-Options: DENY');

$cfg = spc_config();
$logged = !empty($_SESSION['ok']);
$msg = '';

if (empty($_SESSION['csrf'])) { $_SESSION['csrf'] = bin2hex(random_bytes(16)); }
function csrf_ok() { return hash_equals($_SESSION['csrf'], (string) ($_POST['csrf'] ?? '')); }
function pend_file($id) { return preg_match('/^[0-9]{8}-[0-9]{6}-[0-9a-f]{8}$/', $id) ? spc_dir('pendientes') . '/' . $id . '.json' : null; }

if (isset($_GET['salir'])) { $_SESSION = []; session_destroy(); header('Location: panel.php'); exit; }

if (!$logged && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['clave'])) {
    if (!spc_rate_limit('login', 5, 900)) {
        $msg = 'Demasiados intentos. Espera 15 minutos.';
    } elseif (!empty($cfg['password_hash']) && password_verify((string) $_POST['clave'], $cfg['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['ok'] = 1;
        header('Location: panel.php'); exit;
    } else {
        $msg = 'Contraseña incorrecta.';
    }
}

if ($logged && isset($_GET['foto'])) {
    $f = pend_file((string) $_GET['foto']);
    $img = $f ? spc_dir('fotos-pendientes') . '/' . $_GET['foto'] . '.jpg' : '';
    if ($img && is_file($img)) { header('Content-Type: image/jpeg'); readfile($img); exit; }
    http_response_code(404); exit;
}

if ($logged && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && csrf_ok()) {
    $accion = $_POST['accion'] ?? '';
    $id = (string) ($_POST['id'] ?? '');
    $file = pend_file($id);
    if ($file && is_file($file) && in_array($accion, ['aprobar', 'rechazar'], true)) {
        $n = spc_read_json($file);
        $fotoPend = spc_dir('fotos-pendientes') . '/' . $id . '.jpg';
        if ($accion === 'aprobar') {
            $n['titulo'] = spc_clean($_POST['titulo'] ?? $n['titulo'], 140) ?: $n['titulo'];
            $n['texto'] = spc_clean($_POST['texto'] ?? $n['texto'], 5000) ?: $n['texto'];
            if ($n['foto'] && is_file($fotoPend)) {
                if (!is_dir(SPC_PUBLIC_FOTOS)) { mkdir(SPC_PUBLIC_FOTOS, 0755, true); }
                rename($fotoPend, SPC_PUBLIC_FOTOS . '/' . $id . '.jpg');
            } else {
                $n['foto'] = false;
            }
            spc_with_lock('publicadas', function () use ($n) {
                $pub = spc_publicadas();
                array_unshift($pub, ['id' => $n['id'], 'fecha' => date('c'), 'nombre' => $n['nombre'], 'lugar' => $n['lugar'],
                    'titulo' => $n['titulo'], 'texto' => $n['texto'], 'foto' => (bool) $n['foto']]);
                spc_write_json(spc_dir() . '/publicadas.json', $pub);
            });
            $msg = 'Noticia publicada.';
        } else {
            if (is_file($fotoPend)) { unlink($fotoPend); }
            $msg = 'Noticia rechazada y borrada.';
        }
        unlink($file);
    } elseif ($accion === 'retirar' && preg_match('/^[0-9]{8}-[0-9]{6}-[0-9a-f]{8}$/', $id)) {
        spc_with_lock('publicadas', function () use ($id) {
            $pub = array_values(array_filter(spc_publicadas(), function ($n) use ($id) { return $n['id'] !== $id; }));
            spc_write_json(spc_dir() . '/publicadas.json', $pub);
        });
        $f = SPC_PUBLIC_FOTOS . '/' . $id . '.jpg';
        if (is_file($f)) { unlink($f); }
        $msg = 'Noticia retirada del sitio.';
    }
}
$pendientes = [];
if ($logged) {
    foreach (glob(spc_dir('pendientes') . '/*.json') ?: [] as $f) { $pendientes[] = spc_read_json($f); }
    usort($pendientes, function ($a, $b) { return strcmp($b['id'], $a['id']); });
}
?><!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title>Panel de noticias de la comunidad</title>
<link rel="stylesheet" href="../styles.css">
</head>
<body>
<main class="section section--alt news-page">
<div class="container container--narrow panel">
    <header class="section__head">
        <p class="section__eyebrow">Panel privado</p>
        <h1>Noticias de la comunidad</h1>
        <?php if ($logged): ?><p><a href="../comunidad.html" target="_blank" rel="noopener">Ver la sección pública</a> · <a href="panel.php?salir=1">Cerrar sesión</a></p><?php endif; ?>
    </header>
    <?php if ($msg): ?><p class="panel__msg" role="status"><?= h($msg) ?></p><?php endif; ?>

<?php if (!$logged): ?>
    <form method="post" class="form panel__login">
        <label for="clave">Contraseña</label>
        <input id="clave" name="clave" type="password" required autocomplete="current-password">
        <button class="btn btn--primary" type="submit">Entrar</button>
    </form>
<?php else: ?>
    <h2 class="news-page__sub">Por revisar (<?= count($pendientes) ?>)</h2>
    <?php if (!$pendientes): ?><p>No hay noticias pendientes.</p><?php endif; ?>
    <?php foreach ($pendientes as $n): ?>
    <article class="article panel__item">
        <?php if ($n['foto']): ?><img class="article__img" src="panel.php?foto=<?= h($n['id']) ?>" alt=""><?php endif; ?>
        <form method="post" class="article__body">
            <div class="news__meta"><span class="news__tag"><?= h($n['lugar']) ?></span> <time><?= h(date('d/m/Y H:i', strtotime($n['fecha']))) ?></time></div>
            <p><strong>Enviada por:</strong> <?= h($n['nombre']) ?> · <strong>Contacto (privado):</strong> <?= h($n['contacto']) ?></p>
            <label>Título<input name="titulo" value="<?= h($n['titulo']) ?>" maxlength="140"></label>
            <label>Texto<textarea name="texto" rows="8" maxlength="5000"><?= h($n['texto']) ?></textarea></label>
            <input type="hidden" name="id" value="<?= h($n['id']) ?>">
            <input type="hidden" name="csrf" value="<?= h($_SESSION['csrf']) ?>">
            <div class="panel__actions">
                <button class="btn btn--primary" name="accion" value="aprobar">Aprobar y publicar</button>
                <button class="btn btn--ghost" name="accion" value="rechazar" onclick="return confirm('¿Rechazar y borrar esta noticia?')">Rechazar</button>
            </div>
        </form>
    </article>
    <?php endforeach; ?>

    <h2 class="news-page__sub">Publicadas (<?= count(spc_publicadas()) ?>)</h2>
    <ul class="panel__pub">
    <?php foreach (spc_publicadas() as $n): ?>
        <li>
            <span><?= h(date('d/m/Y', strtotime($n['fecha']))) ?> · <?= h($n['titulo']) ?></span>
            <form method="post">
                <input type="hidden" name="id" value="<?= h($n['id']) ?>">
                <input type="hidden" name="csrf" value="<?= h($_SESSION['csrf']) ?>">
                <button class="btn btn--small btn--ghost" name="accion" value="retirar" onclick="return confirm('¿Retirar esta noticia del sitio?')">Retirar</button>
            </form>
        </li>
    <?php endforeach; ?>
    </ul>
<?php endif; ?>
</div>
</main>
</body>
</html>
