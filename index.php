<?php
require_once __DIR__ . '/config.php';
session_start();

$app = new App();
$app->run();
