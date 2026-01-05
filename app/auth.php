<?php
function current_user(PDO $db): ?array {
  $uid = $_SESSION['user_id'] ?? null;
  if (!$uid) return null;
  $st = $db->prepare("SELECT id, email, name, is_blocked FROM users WHERE id = :id");
  $st->execute([':id' => $uid]);
  $u = $st->fetch();
  return $u ?: null;
}

function require_auth(PDO $db): array {
  $u = current_user($db);
  if (!$u) redirect('/login');
  if ((int)$u['is_blocked'] === 1) {
    session_destroy();
    redirect('/login?blocked=1');
  }
  if (empty($_SESSION['two_factor_ok'])) {
    redirect('/verify');
  }
  return $u;
}

function login_attempt_is_locked(PDO $db, string $email): array {
  $st = $db->prepare("SELECT id, failed_attempts, lockout_until, is_blocked FROM users WHERE email = :e");
  $st->execute([':e' => $email]);
  $u = $st->fetch();
  if (!$u) return [false, null];
  if ((int)$u['is_blocked'] === 1) return [true, 'User is blocked'];
  if (!empty($u['lockout_until']) && strtotime($u['lockout_until']) > time()) {
    return [true, 'Too many attempts. Try again later.'];
  }
  return [false, null];
}

function record_failed_login(PDO $db, array $config, int $userId): void {
  $max = (int)$config['app']['lockout']['max_attempts'];
  $mins = (int)$config['app']['lockout']['minutes'];
  $st = $db->prepare("SELECT failed_attempts FROM users WHERE id = :id");
  $st->execute([':id' => $userId]);
  $n = (int)$st->fetchColumn();
  $n++;
  $lockoutUntil = null;
  if ($n >= $max) {
    $lockoutUntil = date('Y-m-d H:i:s', time() + $mins * 60);
    $n = 0; // reset counter after lockout triggers
  }
  $st = $db->prepare("UPDATE users SET failed_attempts = :n, lockout_until = :lu WHERE id = :id");
  $st->execute([':n' => $n, ':lu' => $lockoutUntil, ':id' => $userId]);
}

function clear_failed_login(PDO $db, int $userId): void {
  $st = $db->prepare("UPDATE users SET failed_attempts = 0, lockout_until = NULL WHERE id = :id");
  $st->execute([':id' => $userId]);
}

function create_two_factor_code(PDO $db, array $config, int $userId): string {
  $code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
  $expires = date('Y-m-d H:i:s', time() + ((int)$config['app']['two_factor']['code_minutes']) * 60);
  $hash = password_hash($code, PASSWORD_DEFAULT);
  $st = $db->prepare("INSERT INTO two_factor_codes (user_id, code_hash, expires_at, used_at) VALUES (:uid,:h,:ex,NULL)");
  $st->execute([':uid'=>$userId, ':h'=>$hash, ':ex'=>$expires]);
  return $code;
}

function verify_two_factor_code(PDO $db, int $userId, string $code): bool {
  $st = $db->prepare("SELECT id, code_hash FROM two_factor_codes
                      WHERE user_id = :uid AND used_at IS NULL AND expires_at > NOW()
                      ORDER BY id DESC LIMIT 1");
  $st->execute([':uid'=>$userId]);
  $row = $st->fetch();
  if (!$row) return false;
  if (!password_verify($code, $row['code_hash'])) return false;
  $up = $db->prepare("UPDATE two_factor_codes SET used_at = NOW() WHERE id = :id");
  $up->execute([':id'=>$row['id']]);
  return true;
}
