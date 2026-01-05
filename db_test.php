<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);

$config = require __DIR__ . '/app/config.php';

try {
  $pdo = new PDO(
    $config['db']['dsn'],
    $config['db']['user'],
    $config['db']['pass'],
    $config['db']['options'] ?? []
  );
  echo "DB OK";
} catch (Throwable $e) {
  echo "DB FAIL: " . htmlspecialchars($e->getMessage());
}
