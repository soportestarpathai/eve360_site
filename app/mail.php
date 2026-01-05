<?php
/**
 * SMTP email via PHPMailer (recommended).
 * Setup:
 *   composer install
 *   Configure SMTP in app/config.php under ['email']['smtp'].
 *
 * Fallback:
 *   If PHPMailer is not installed, falls back to PHP mail().
 */
function send_email(array $config, string $to, string $subject, string $body): bool {
  $smtp = $config['email']['smtp'] ?? null;

  $autoload = __DIR__ . '/../vendor/autoload.php';
  if ($smtp && file_exists($autoload)) {
    require_once $autoload;
    try {
      $mail = new PHPMailer\PHPMailer\PHPMailer(true);
      $mail->CharSet = 'UTF-8';
      $mail->isSMTP();
      $mail->Host = $smtp['host'];
      $mail->SMTPAuth = true;
      $mail->Username = $smtp['username'];
      $mail->Password = $smtp['password'];
      $mail->Port = (int)($smtp['port'] ?? 587);

      $secure = $smtp['secure'] ?? 'tls';
      if ($secure) $mail->SMTPSecure = $secure;

      $mail->setFrom($config['email']['from'], $config['email']['from_name'] ?? '');
      $mail->addAddress($to);
      $mail->Subject = $subject;
      $mail->isHTML(true);
      $mail->Body = nl2br(htmlspecialchars($body));
      $mail->AltBody = $body;
      return $mail->send();
    } catch (Throwable $e) {
      // fall back to mail()
    }
  }

  $from = $config['email']['from'];
  $fromName = $config['email']['from_name'] ?? '';
  $headers = [];
  $headers[] = 'MIME-Version: 1.0';
  $headers[] = 'Content-type: text/plain; charset=utf-8';
  $headers[] = 'From: ' . ($fromName ? "$fromName <$from>" : $from);
  if (!empty($config['email']['extra_headers'])) $headers[] = trim($config['email']['extra_headers']);
  return mail($to, $subject, $body, implode("\r\n", $headers));
}
