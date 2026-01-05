<?php
function csrf_token(array $config): string {
  if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = hash('sha256', $config['app']['csrf_key'] . random_bytes(32));
  }
  return $_SESSION['csrf'];
}

function csrf_verify(array $config): void {
  $t = $_POST['_csrf'] ?? '';
  if (!$t || !hash_equals($_SESSION['csrf'] ?? '', $t)) {
    http_response_code(403);
    exit('CSRF verification failed.');
  }
}

function e(string $s): string {
  return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void {
  header('Location: ' . app_url($path));
  exit;
}
