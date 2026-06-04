<?php
if (php_sapi_name() === 'cli-server') {
  $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
  if (is_file(__DIR__ . $path)) return false;
}
$_GET['url'] = ltrim($_SERVER['REQUEST_URI'], '/');
require __DIR__ . '/index.php';
