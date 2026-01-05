<?php
function user_has_perm(PDO $db, int $userId, string $perm): bool {
  $sql = "SELECT 1
          FROM user_roles ur
          JOIN role_permissions rp ON rp.role_id = ur.role_id
          JOIN permissions p ON p.id = rp.permission_id
          WHERE ur.user_id = :uid AND p.name = :perm
          LIMIT 1";
  $st = $db->prepare($sql);
  $st->execute([':uid' => $userId, ':perm' => $perm]);
  return (bool)$st->fetchColumn();
}

function require_perm(PDO $db, string $perm): void {
  $uid = $_SESSION['user_id'] ?? null;
  if (!$uid) redirect('/login');
  if (!user_has_perm($db, (int)$uid, $perm)) {
    http_response_code(403);
    exit('Forbidden');
  }
}
