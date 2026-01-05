<?php
function db(array $config): PDO {
  $db = $config['db'] ?? [];

  // If DSN is provided, use it
  if (!empty($db['dsn'])) {
    $dsn = $db['dsn'];
  } else {
    // Otherwise build DSN from host/name/port
    $host = $db['host'] ?? '127.0.0.1';
    $name = $db['name'] ?? '';
    $port = $db['port'] ?? null;
    $charset = $db['charset'] ?? 'utf8mb4';

    $dsn = "mysql:host={$host};" . ($port ? "port={$port};" : "") . "dbname={$name};charset={$charset}";
  }

  $user = $db['user'] ?? '';
  $pass = $db['pass'] ?? '';

  $options = $db['options'] ?? [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
  ];

  return new PDO($dsn, $user, $pass, $options);
}
