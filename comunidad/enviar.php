<?php
define('SPC_COMUNIDAD', 1);
require __DIR__ . '/lib.php';

function volver($q) {
    header('Location: ../comunidad.html?' . $q . '#enviar', true, 303);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { volver('error=metodo'); }

// Honeypot and minimum fill time to stop bots.
if (!empty($_POST['sitio_web'])) { volver('enviado=1'); }
$age = spc_form_age($_POST['t'] ?? '');
if ($age < 5 || $age > 86400) { volver('error=rapido'); }
if (!spc_human_ok($_POST['t'] ?? '', $_POST['humano'] ?? '')) {
    volver(spc_rate_limit('humano', 10, 3600) ? 'error=humano' : 'error=limite');
}

$nombre = spc_clean($_POST['nombre'] ?? '', 80);
$contacto = spc_clean($_POST['contacto'] ?? '', 120);
$lugar = spc_clean($_POST['lugar'] ?? '', 60);
$titulo = spc_clean($_POST['titulo'] ?? '', 140);
$texto = spc_clean($_POST['texto'] ?? '', 5000);

if ($nombre === '' || $contacto === '' || $titulo === '' || mb_strlen($texto) < 40
    || !in_array($lugar, SPC_LUGARES, true) || empty($_POST['acepto'])) {
    volver('error=datos');
}
if (!spc_rate_limit('enviar', 5, 3600)) { volver('error=limite'); }

$id = date('Ymd-His') . '-' . bin2hex(random_bytes(4));
$foto = false;
$uploadErr = $_FILES['foto']['error'] ?? UPLOAD_ERR_NO_FILE;
if ($uploadErr !== UPLOAD_ERR_NO_FILE) {
    if ($uploadErr !== UPLOAD_ERR_OK || !is_uploaded_file($_FILES['foto']['tmp_name'])
        || $_FILES['foto']['size'] > 8 * 1024 * 1024) { volver('error=foto'); }
    $foto = spc_save_image($_FILES['foto']['tmp_name'], spc_dir('fotos-pendientes') . '/' . $id . '.jpg');
    if (!$foto) { volver('error=foto'); }
}

$saved = spc_write_json(spc_dir('pendientes') . '/' . $id . '.json', [
    'id' => $id,
    'fecha' => date('c'),
    'nombre' => $nombre,
    'contacto' => $contacto,
    'lugar' => $lugar,
    'titulo' => $titulo,
    'texto' => $texto,
    'foto' => $foto,
    'ip' => hash('sha256', spc_ip()),
]);
if (!$saved) { volver('error=guardar'); }

$panel = 'https://sanpedrodecajas.com/comunidad/panel.php';
$body = "Llegó una noticia nueva para revisar.\n\n"
    . "Título: $titulo\nLugar: $lugar\nEnviada por: $nombre ($contacto)\n"
    . 'Foto: ' . ($foto ? 'sí' : 'no') . "\n\n$texto\n\n"
    . "Apruébala o recházala en el panel:\n$panel\n";
spc_mail('Noticia de la comunidad: ' . mb_substr($titulo, 0, 80), $body);

volver('enviado=1');
