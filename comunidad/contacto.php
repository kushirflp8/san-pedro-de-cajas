<?php
define('SPC_COMUNIDAD', 1);
require __DIR__ . '/lib.php';

function volver($q) {
    header('Location: ../index.html?' . $q . '#contacto', true, 303);
    exit;
}

const SPC_ASUNTOS = ['Turismo', 'Hospedajes', 'Publicidad', 'Radio San Pedro', 'Otro'];

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { volver('contacto=error'); }
if (!empty($_POST['sitio_web'])) { volver('contacto=ok'); }
$age = spc_form_age($_POST['t'] ?? '');
if ($age < 3 || $age > 86400) { volver('contacto=rapido'); }
if (!spc_rate_limit('humano', 10, 3600, false)) { volver('contacto=limite'); }
if (!spc_human_ok($_POST['t'] ?? '', $_POST['humano'] ?? '')) {
    volver(spc_rate_limit('humano', 10, 3600) ? 'contacto=humano' : 'contacto=limite');
}

$nombre = spc_clean($_POST['nombre'] ?? '', 80);
$email = filter_var(trim((string) ($_POST['email'] ?? '')), FILTER_VALIDATE_EMAIL);
$asunto = (string) ($_POST['asunto'] ?? '');
$mensaje = spc_clean($_POST['mensaje'] ?? '', 4001);

if ($nombre === '' || !$email || strlen($email) > 120 || !in_array($asunto, SPC_ASUNTOS, true) || mb_strlen($mensaje) < 10 || mb_strlen($mensaje) > 4000) {
    volver('contacto=datos');
}
if (!spc_rate_limit('contacto', 5, 3600)) { volver('contacto=limite'); }

$body = "Mensaje desde el formulario de contacto de sanpedrodecajas.com\n\n"
    . "Nombre: $nombre\nCorreo: $email\nAsunto: $asunto\n\n$mensaje\n";
$ok = spc_mail('Contacto web: ' . $asunto . ' - ' . mb_substr($nombre, 0, 60), $body, $email);
volver($ok ? 'contacto=ok' : 'contacto=error');
