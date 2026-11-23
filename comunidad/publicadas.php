<?php
define('SPC_COMUNIDAD', 1);
require __DIR__ . '/lib.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
$out = [];
foreach (spc_publicadas() as $n) {
    $out[] = [
        'id' => $n['id'],
        'fecha' => $n['fecha'],
        'nombre' => $n['nombre'],
        'lugar' => $n['lugar'],
        'titulo' => $n['titulo'],
        'texto' => $n['texto'],
        'foto' => $n['foto'] ? 'comunidad/fotos/' . $n['id'] . '.jpg' : null,
    ];
}
echo json_encode(['token' => spc_form_token(), 'items' => $out], JSON_UNESCAPED_UNICODE);
