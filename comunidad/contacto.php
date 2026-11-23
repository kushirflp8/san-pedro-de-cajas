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

$nombre = spc_clean($_POST['nombre'] ?? '', 80);
$email = filter_var(trim((string) ($_POST['email'] ?? '')), FILTER_VALIDATE_EMAIL);
$asunto = (string) ($_POST['asunto'] ?? '');
$mensaje = spc_clean($_POST['mensaje'] ?? '', 4000);

if ($nombre === '' || !$email || strlen($email) > 120 || !in_array($asunto, SPC_ASUNTOS, true) || mb_strlen($mensaje) < 10) {
    volver('contacto=datos');
}
if (!spc_rate_limit('contacto', 5, 3600)) { volver('contacto=limite'); }

$cfg = spc_config();
if (empty($cfg['email'])) { volver('contacto=error'); }
$body = "Mensaje desde el formulario de contacto de sanpedrodecajas.com\n\n"
    . "Nombre: $nombre\nCorreo: $email\nAsunto: $asunto\n\n$mensaje\n";
$subject = '=?UTF-8?B?' . base64_encode('Contacto web: ' . $asunto . ' - ' . mb_substr($nombre, 0, 60)) . '?=';
$headers = "From: San Pedro de Cajas <noreply@sanpedrodecajas.com>\r\n"
    . "Reply-To: $email\r\n"
    . "Content-Type: text/plain; charset=UTF-8\r\nMIME-Version: 1.0\r\n";
$ok = @mail($cfg['email'], $subject, $body, $headers, '-fnoreply@sanpedrodecajas.com');
volver($ok ? 'contacto=ok' : 'contacto=error');
