<?php
function audit(PDO $db, ?int $userId, string $action, string $entityType = '', ?int $entityId = null, array $payload = []): void {
  $ip = $_SERVER['REMOTE_ADDR'] ?? '';
  $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
  $db->prepare("INSERT INTO audit_logs (user_id, action, entity_type, entity_id, ip, user_agent, payload_json, created_at)
                VALUES (:u,:a,:t,:eid,:ip,:ua,:p,NOW())")
     ->execute([
       ':u'=>$userId,
       ':a'=>$action,
       ':t'=>$entityType,
       ':eid'=>$entityId,
       ':ip'=>$ip,
       ':ua'=>$ua,
       ':p'=>json_encode($payload, JSON_UNESCAPED_UNICODE),
     ]);
}
