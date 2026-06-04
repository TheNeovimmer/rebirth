<?php
define('BASE_PATH', __DIR__);
define('APP_PATH', BASE_PATH . '/app');
define('VIEWS_PATH', APP_PATH . '/views');

define('DB_HOST', 'db');
define('DB_PORT', '3306');
define('DB_NAME', 'rebirth');
define('DB_USER', 'db');
define('DB_PASS', 'db');

spl_autoload_register(function ($class) {
  $paths = [
    APP_PATH . '/core/' . $class . '.php',
    APP_PATH . '/controllers/' . $class . '.php',
    APP_PATH . '/models/' . $class . '.php',
  ];
  foreach ($paths as $file) {
    if (file_exists($file)) { require_once $file; return; }
  }
});
