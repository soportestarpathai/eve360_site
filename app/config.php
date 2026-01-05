<?php
return [
  'app' => [
    'name' => 'EVE360 Notarios',
    'base_url' => '/eve360',
    'public_url' => 'https://www.adsoft.mx',
    'session_name' => 'EVESESSID',
    'csrf_key' => '5e8a3d9f2b1c7e4a0d9b8c7f6e5d4a3b2c1f0e9d8c7b6a5f4e3d2c1b0a9f8e7d',
    'lockout' => [
      'max_attempts' => 5,
      'minutes' => 15,
    ],
    'two_factor' => [
      'code_minutes' => 10,
    ],
    'password_reset' => [
      'token_minutes' => 60,
    ],
    'notifications' => [
      'doc_expiry_days' => 30,
    ],
  ],
  'db' => [
    'dsn' => 'mysql:host=70.35.200.34;dbname=eve_app_12;charset=utf8mb4',
    'host' => '70.35.200.34',
    'name' => 'eve_app_12',
    'user' => 'listaspeps',
    'pass' => 'Adsoft@2016',
    'options' => [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_EMULATE_PREPARES => false,
    ],    
  ],
  'email' => [
    'smtp' => [
      'host' => 'smtp.ionos.mx',
      'port' => 587,
      'secure' => 'tls', // 'tls' or 'ssl' or ''
      'username' => 'no-reply@adsoft.mx',
      'password' => 'Ex1t0@2026'
    ],
    
    // Simple mail() sender
    'from' => 'no-reply@adsoft.mx',
    'from_name' => 'EVE360',
    // If your server needs additional headers, set them here:
    'extra_headers' => '',
  ],
];
