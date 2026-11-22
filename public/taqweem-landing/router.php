<?php
/** Local preview-only static server router. Handles byte-range requests for MP4 seeking.
 * This file is NOT part of the Laravel backend.
 */
$root = realpath(__DIR__);
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$path = rawurldecode($path ?: '/');
$path = $path === '/' ? '/index.html' : $path;
$real = realpath($root . $path);
if (!$real || !str_starts_with($real, $root . DIRECTORY_SEPARATOR) || !is_file($real)) {
    http_response_code(404); echo "Not found"; return true;
}
if (!preg_match('/\.(mp4|webm)$/i', $real)) return false;
$size = filesize($real);
$start = 0; $end = $size - 1;
header('Content-Type: video/mp4');
header('Accept-Ranges: bytes');
header('Cache-Control: public, max-age=3600');
if (isset($_SERVER['HTTP_RANGE'])) {
    $range = trim($_SERVER['HTTP_RANGE']);
    if (!preg_match('/^bytes=(\d*)-(\d*)$/', $range, $m)) {
        http_response_code(416); header("Content-Range: bytes */$size"); exit;
    }
    if ($m[1] === '' && $m[2] !== '') { $length = (int)$m[2]; $start = max(0,$size-$length); }
    else { $start = (int)$m[1]; if ($m[2] !== '') $end = min($end,(int)$m[2]); }
    if ($start >= $size || $end < $start) { http_response_code(416); header("Content-Range: bytes */$size"); exit; }
    http_response_code(206);
    header("Content-Range: bytes $start-$end/$size");
}
header('Content-Length: ' . ($end-$start+1));
if ($_SERVER['REQUEST_METHOD'] === 'HEAD') exit;
$fp = fopen($real, 'rb'); fseek($fp,$start); $left=$end-$start+1;
while ($left > 0 && !feof($fp) && connection_status() === CONNECTION_NORMAL) {
    $buf=fread($fp,min(65536,$left));$n=strlen($buf);if($n===0)break;echo $buf;$left-=$n;
}
fclose($fp);exit;
