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
    imagedestroy($src);
    if ($info[2] === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
        $o = (int) ((@exif_read_data($tmp) ?: [])['Orientation'] ?? 1);
        if (in_array($o, [2, 5, 7], true)) { imageflip($out, IMG_FLIP_HORIZONTAL); }
        if ($o === 4) { imageflip($out, IMG_FLIP_VERTICAL); }
        $angle = [3 => 180, 5 => 90, 6 => -90, 7 => -90, 8 => 90][$o] ?? 0;
        if ($angle) {
            $rot = imagerotate($out, $angle, 0);
            if ($rot) { imagedestroy($out); $out = $rot; }
        }
    }
    $ok = imagejpeg($out, $dest, 82);
    imagedestroy($out);
    return $ok;
}

function spc_mail($subject, $body, $replyTo = null) {
    $cfg = spc_config();
    if (empty($cfg['email'])) { return false; }
    $headers = "From: San Pedro de Cajas <noreply@sanpedrodecajas.com>\r\n"
        . ($replyTo ? "Reply-To: $replyTo\r\n" : '')
        . "MIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n";
    $subject = mb_encode_mimeheader($subject, 'UTF-8', 'B', "\r\n");
    return @mail($cfg['email'], $subject, chunk_split(base64_encode($body)), $headers, '-fnoreply@sanpedrodecajas.com');
}

function spc_form_key() {
    return hash('sha256', 'form|' . (spc_config()['password_hash'] ?? __FILE__));
}

function spc_form_token() {
    $p = time() . '.' . bin2hex(random_bytes(4));
    return $p . '.' . hash_hmac('sha256', $p, spc_form_key());
}

function spc_token_parts($token) {
    $p = explode('.', (string) $token);
    if (count($p) !== 3 || !ctype_digit($p[0]) || !ctype_xdigit($p[1])
        || !hash_equals(hash_hmac('sha256', $p[0] . '.' . $p[1], spc_form_key()), $p[2])) { return null; }
    return $p;
}

/** Seconds since the server issued the token, or -1 if it is invalid. */
function spc_form_age($token) {
    $p = spc_token_parts($token);
    return $p ? time() - (int) $p[0] : -1;
}

/** The two numbers of the "are you human" sum tied to a token. */
function spc_human_numbers($token) {
    $p = spc_token_parts($token);
    if (!$p) { return null; }
    $h = hash_hmac('sha256', 'suma|' . $p[0] . '.' . $p[1], spc_form_key());
    return [2 + hexdec(substr($h, 0, 2)) % 18, 1 + hexdec(substr($h, 2, 2)) % 9];
}

function spc_human_ok($token, $answer) {
    $n = spc_human_numbers($token);
    $answer = trim((string) $answer);
    return $n && ctype_digit($answer) && (int) $answer === $n[0] + $n[1];
}

function spc_publicadas() {
    return spc_read_json(spc_dir() . '/publicadas.json');
}
