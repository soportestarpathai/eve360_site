<?php
// app/trusted_devices.php

function _td_base_cookie_path(): string {
  global $config;
  $basePath = parse_url($config['app']['base_url'] ?? '', PHP_URL_PATH) ?: '/';
  $basePath = '/' . trim($basePath, '/');
  return ($basePath === '//') ? '/' : $basePath;
}

function trusted_device_cookie_name(): string {
  return 'eve360_td';
}

function trusted_device_fingerprint(): array {
  $ua = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');
  $ip = (string)($_SERVER['REMOTE_ADDR'] ?? '');
  return [
    'ua_hash' => hash('sha256', $ua),
    'ip_hash' => $ip ? hash('sha256', $ip) : null,
  ];
}

function trusted_device_issue(PDO $db, int $userId, int $days = 30): void {
  $selector = bin2hex(random_bytes(12)); // 24 chars
  $token    = bin2hex(random_bytes(32)); // 64 chars

  $fp = trusted_device_fingerprint();
  $tokenHash = hash('sha256', $token);

  $expires = new DateTime('now');
  $expires->modify('+' . max(1, (int)$days) . ' days');
  $expiresAt = $expires->format('Y-m-d H:i:s');

  $st = $db->prepare("INSERT INTO trusted_devices (user_id, selector, token_hash, user_agent_hash, ip_hash, expires_at)
                      VALUES (:uid, :sel, :th, :uah, :iph, :exp)");
  $st->execute([
    ':uid' => $userId,
    ':sel' => $selector,
    ':th'  => $tokenHash,
    ':uah' => $fp['ua_hash'],
    ':iph' => $fp['ip_hash'],
    ':exp' => $expiresAt,
  ]);

  $cookieVal = $selector . '.' . $token;

  $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
  setcookie(trusted_device_cookie_name(), $cookieVal, [
    'expires'  => $expires->getTimestamp(),
    'path'     => _td_base_cookie_path(),
    'secure'   => $isHttps,
    'httponly' => true,
    'samesite' => 'Lax',
  ]);
}

function trusted_device_clear(): void {
  $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
  setcookie(trusted_device_cookie_name(), '', [
    'expires'  => time() - 3600,
    'path'     => _td_base_cookie_path(),
    'secure'   => $isHttps,
    'httponly' => true,
    'samesite' => 'Lax',
  ]);
}

function trusted_device_is_valid(PDO $db, int $userId): bool {
  $raw = (string)($_COOKIE[trusted_device_cookie_name()] ?? '');
  if (!$raw || strpos($raw, '.') === false) return false;

  [$selector, $token] = explode('.', $raw, 2);
  if (!$selector || !$token) return false;

  $st = $db->prepare("SELECT id, token_hash, user_agent_hash, expires_at, revoked_at
                      FROM trusted_devices
                      WHERE selector = :sel AND user_id = :uid
                      LIMIT 1");
  $st->execute([':sel' => $selector, ':uid' => $userId]);
  $row = $st->fetch(PDO::FETCH_ASSOC);
  if (!$row) return false;
  if (!empty($row['revoked_at'])) return false;

  // expired?
  if (strtotime($row['expires_at']) < time()) return false;

  // bind to user-agent (prevents cookie copied to different browser)
  $fp = trusted_device_fingerprint();
  if (!hash_equals((string)$row['user_agent_hash'], (string)$fp['ua_hash'])) return false;

  // token match
  $calc = hash('sha256', $token);
  if (!hash_equals((string)$row['token_hash'], $calc)) return false;

  // update last_used_at (best effort)
  try {
    $up = $db->prepare("UPDATE trusted_devices SET last_used_at = NOW() WHERE id = :id");
    $up->execute([':id' => (int)$row['id']]);
  } catch (Throwable $e) {}

  return true;
}
