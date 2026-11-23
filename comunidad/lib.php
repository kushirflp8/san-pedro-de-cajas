<?php
if (!defined('SPC_COMUNIDAD')) { http_response_code(404); exit; }

define('SPC_DATA', dirname(__DIR__, 2) . '/spc_noticias');
define('SPC_PUBLIC_FOTOS', __DIR__ . '/fotos');
define('SPC_LUGARES', [
    'San Pedro de Cajas', 'Condorín', 'Otro centro poblado de San Pedro de Cajas',
    'Tarma', 'Junín', 'La Oroya', 'Acobamba', 'Otro lugar',
]);

function spc_config() {
    static $cfg = null;
    if ($cfg === null) {
        $file = SPC_DATA . '/config.php';
        $cfg = is_file($file) ? require $file : [];
    }
    return $cfg;
}

function spc_dir($sub = '') {
    $dir = SPC_DATA . ($sub !== '' ? '/' . $sub : '');
    if (!is_dir($dir)) { mkdir($dir, 0750, true); }
    return $dir;
}

function spc_read_json($file, $default = []) {
    if (!is_file($file)) { return $default; }
    $data = json_decode((string) file_get_contents($file), true);
    return is_array($data) ? $data : $default;
}

function spc_write_json($file, $data) {
    $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    $tmp = $file . '.' . bin2hex(random_bytes(4)) . '.tmp';
    if ($json === false || file_put_contents($tmp, $json, LOCK_EX) !== strlen($json) || !rename($tmp, $file)) {
        @unlink($tmp);
        return false;
    }
    return true;
}

function spc_with_lock($name, callable $fn) {
    $fp = fopen(spc_dir() . '/' . $name . '.lock', 'c');
    flock($fp, LOCK_EX);
    try { return $fn(); } finally { flock($fp, LOCK_UN); fclose($fp); }
}

function spc_ip() {
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/** Returns true if the action is allowed, recording the hit. */
function spc_rate_limit($bucket, $max, $window) {
    return spc_with_lock('rate', function () use ($bucket, $max, $window) {
        $file = spc_dir() . '/rate.json';
        $now = time();
        $all = spc_read_json($file);
        foreach ($all as $k => $hits) {
            $all[$k] = array_values(array_filter($hits, function ($t) use ($now) { return $t > $now - 86400; }));
            if (!$all[$k]) { unset($all[$k]); }
        }
        $key = $bucket . ':' . hash('sha256', spc_ip());
        $recent = array_filter($all[$key] ?? [], function ($t) use ($now, $window) { return $t > $now - $window; });
        if (count($recent) >= $max) { spc_write_json($file, $all); return false; }
        $all[$key][] = $now;
        spc_write_json($file, $all);
        return true;
    });
}

function spc_clean($value, $max) {
    $value = trim(str_replace(["\r\n", "\r"], "\n", (string) $value));
    $value = preg_replace('/[^\P{C}\n]/u', '', $value) ?? '';
    return mb_substr($value, 0, $max);
}

function h($s) {
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}

/** Re-encodes an uploaded image as JPEG (max 1600 px). Returns false if invalid. */
function spc_save_image($tmp, $dest) {
    $info = @getimagesize($tmp);
    if (!$info || !in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) { return false; }
    if ($info[0] * $info[1] > 40000000) { return false; }
    switch ($info[2]) {
        case IMAGETYPE_JPEG: $src = @imagecreatefromjpeg($tmp); break;
        case IMAGETYPE_PNG: $src = @imagecreatefrompng($tmp); break;
        default: $src = @imagecreatefromwebp($tmp);
    }
    if (!$src) { return false; }
    $w = imagesx($src); $h = imagesy($src);
    $scale = min(1, 1600 / max($w, $h));
    $nw = max(1, (int) round($w * $scale)); $nh = max(1, (int) round($h * $scale));
    $out = imagecreatetruecolor($nw, $nh);
    imagefill($out, 0, 0, imagecolorallocate($out, 255, 255, 255));
    imagecopyresampled($out, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
    $ok = imagejpeg($out, $dest, 82);
    imagedestroy($src); imagedestroy($out);
    return $ok;
}

function spc_form_key() {
    return hash('sha256', 'form|' . (spc_config()['password_hash'] ?? __FILE__));
}

function spc_form_token() {
    $t = (string) time();
    return $t . '.' . hash_hmac('sha256', $t, spc_form_key());
}

/** Seconds since the server issued the token, or -1 if it is invalid. */
function spc_form_age($token) {
    $p = explode('.', (string) $token, 2);
    if (count($p) !== 2 || !ctype_digit($p[0]) || !hash_equals(hash_hmac('sha256', $p[0], spc_form_key()), $p[1])) { return -1; }
    return time() - (int) $p[0];
}

function spc_publicadas() {
    return spc_read_json(spc_dir() . '/publicadas.json');
}
