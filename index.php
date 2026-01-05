<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/app/bootstrap.php';
require_once __DIR__ . '/app/xml_fep.php';
require_once __DIR__ . '/app/trusted_devices.php';

// Shared hosting routing: prefer ?path=
$path = $_GET['path'] ?? '';
if ($path === '' || $path === null) {
  $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
}

// Normalize
$path = '/' . ltrim($path, '/');

// Strip base folder (/eve360) if present
if (strpos($path, '/eve360') === 0) {
  $path = substr($path, strlen('/eve360'));
  if ($path === '') $path = '/';
}

// Strip index.php if present
if (strpos($path, '/index.php') === 0) {
  $path = substr($path, strlen('/index.php'));
  if ($path === '') $path = '/';
}

// TEMP DEBUG:
if (isset($_GET['debug_path'])) { header('Content-Type:text/plain'); echo $path; exit; }

function render(string $view, array $vars = []) {
  global $config, $db;
  $user = null;
  if (!empty($_SESSION['user_id'])) {
    $user = current_user($db);
  }
  extract($vars);
  require __DIR__ . '/app/views/' . $view . '.php';
}

function json_out($data, int $code = 200): never {
  http_response_code($code);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($data, JSON_UNESCAPED_UNICODE);
  exit;
}

function get_setting(PDO $db, string $key, $default = null) {
  $st = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = :k");
  $st->execute([':k'=>$key]);
  $v = $st->fetchColumn();
  return ($v === false) ? $default : $v;
}

function app_secret(): string {
  global $config;
  $s = (string)($config['app']['secret'] ?? '');
  if ($s !== '') return $s;
  // Fallback (recommended: set a strong random secret in config.php)
  return hash('sha256', __FILE__ . '|' . php_uname());
}

/**
 * ---- Stronger KYC validation per tipo ----
 * Returns array of human-readable “missing” messages.
 */
function validate_client_kyc(array $c): array {
  $errors = [];
  $tipo = (string)($c['tipo'] ?? '');

  // Common
  if (trim((string)($c['nombre'] ?? '')) === '') $errors[] = "Nombre/razón social es obligatorio";
  if (trim((string)($c['rfc'] ?? '')) === '') $errors[] = "RFC es obligatorio";

  // Address (Annexes require full address)
  $addrReq = ['dom_calle','dom_num_ext','dom_colonia','dom_municipio','dom_ciudad','dom_estado','dom_cp'];
  foreach ($addrReq as $k) {
    if (trim((string)($c[$k] ?? '')) === '') $errors[] = "Domicilio incompleto: falta {$k}";
  }

  // Phone
  if (trim((string)($c['tel1'] ?? '')) === '') $errors[] = "Teléfono es obligatorio (tel1)";

  if ($tipo === 'FISICA') {
    foreach (['apellido_paterno','apellido_materno','nombres'] as $k) {
      if (trim((string)($c[$k] ?? '')) === '') $errors[] = "Falta {$k} (persona física)";
    }
    if (trim((string)($c['fecha_base'] ?? '')) === '') $errors[] = "Falta fecha de nacimiento";
    if (trim((string)($c['pais_nacionalidad'] ?? '')) === '') $errors[] = "Falta país de nacionalidad";
    if (trim((string)($c['pais_nacimiento'] ?? '')) === '') $errors[] = "Falta país de nacimiento";
    if (trim((string)($c['ocupacion'] ?? '')) === '' && trim((string)($c['actividad_economica'] ?? '')) === '') {
      $errors[] = "Falta actividad/ocupación/profesión";
    }
    if (trim((string)($c['firma_path'] ?? '')) === '') $errors[] = "Falta firma autógrafa (firma_path)";
  }

  if ($tipo === 'MORAL') {
    if (trim((string)($c['fecha_base'] ?? '')) === '') $errors[] = "Falta fecha de constitución";
    if (trim((string)($c['pais_nacionalidad'] ?? '')) === '') $errors[] = "Falta país de nacionalidad";
    if (trim((string)($c['actividad_economica'] ?? '')) === '' && trim((string)($c['objeto_social'] ?? '')) === '') {
      $errors[] = "Falta actividad/objeto social u objeto social preponderante";
    }

    foreach (['rep_nombre','rep_rfc','rep_nacionalidad','rep_fecha_nacimiento'] as $k) {
      if (trim((string)($c[$k] ?? '')) === '') $errors[] = "Falta {$k} (representante)";
    }
    if (trim((string)($c['rep_firma_path'] ?? '')) === '') $errors[] = "Falta firma representante (rep_firma_path)";
  }

  if ($tipo === 'FIDEICOMISO') {
    if (trim((string)($c['fideicomiso_identificador'] ?? '')) === '') $errors[] = "Falta identificador de fideicomiso";
    if (trim((string)($c['fiduciario_rfc'] ?? '')) === '') $errors[] = "Falta RFC fiduciario";
    if (trim((string)($c['fiduciario_nombre'] ?? '')) === '') $errors[] = "Falta nombre/razón social fiduciario";
  }

  return $errors;
}

/**
 * Normalize POST to a row shape for DB + KYC validation.
 */
function client_post_to_row(array $post): array {
  $tipo = (string)($post['tipo'] ?? 'FISICA');
  if (!in_array($tipo, ['FISICA','MORAL','FIDEICOMISO'], true)) $tipo = 'FISICA';

  $r = [
    'tipo' => $tipo,
    'nombre' => trim((string)($post['nombre'] ?? '')),
    'nombres' => trim((string)($post['nombres'] ?? '')),
    'apellido_paterno' => trim((string)($post['apellido_paterno'] ?? '')),
    'apellido_materno' => trim((string)($post['apellido_materno'] ?? '')),
    'rfc' => strtoupper(trim((string)($post['rfc'] ?? ''))),
    'curp' => strtoupper(trim((string)($post['curp'] ?? ''))),
    'email' => trim((string)($post['email'] ?? '')),
    'pais_nacionalidad' => trim((string)($post['pais_nacionalidad'] ?? '')),
    'pais_nacimiento' => trim((string)($post['pais_nacimiento'] ?? '')),
    'actividad_economica' => trim((string)($post['actividad_economica'] ?? '')),
    'ocupacion' => trim((string)($post['ocupacion'] ?? '')),
    'extranjero' => (int)($post['extranjero'] ?? 0) === 1 ? 1 : 0,
    'giro_mercantil' => trim((string)($post['giro_mercantil'] ?? '')),
    'fecha_base' => trim((string)($post['fecha_base'] ?? '')) ?: null,

    'dom_calle' => trim((string)($post['dom_calle'] ?? '')),
    'dom_num_ext' => trim((string)($post['dom_num_ext'] ?? '')),
    'dom_num_int' => trim((string)($post['dom_num_int'] ?? '')),
    'dom_colonia' => trim((string)($post['dom_colonia'] ?? '')),
    'dom_municipio' => trim((string)($post['dom_municipio'] ?? '')),
    'dom_ciudad' => trim((string)($post['dom_ciudad'] ?? '')),
    'dom_estado' => trim((string)($post['dom_estado'] ?? '')),
    'dom_cp' => trim((string)($post['dom_cp'] ?? '')),
    'tel1' => trim((string)($post['tel1'] ?? '')),
    'tel1_ext' => trim((string)($post['tel1_ext'] ?? '')),
    'tel2' => trim((string)($post['tel2'] ?? '')),
    'tel2_ext' => trim((string)($post['tel2_ext'] ?? '')),

    'firma_path' => trim((string)($post['firma_path'] ?? '')),
    'objeto_social' => (string)($post['objeto_social'] ?? ''),

    'rep_nombre' => trim((string)($post['rep_nombre'] ?? '')),
    'rep_rfc' => strtoupper(trim((string)($post['rep_rfc'] ?? ''))),
    'rep_curp' => strtoupper(trim((string)($post['rep_curp'] ?? ''))),
    'rep_nacionalidad' => trim((string)($post['rep_nacionalidad'] ?? '')),
    'rep_fecha_nacimiento' => trim((string)($post['rep_fecha_nacimiento'] ?? '')) ?: null,
    'rep_firma_path' => trim((string)($post['rep_firma_path'] ?? '')),

    'fideicomiso_identificador' => trim((string)($post['fideicomiso_identificador'] ?? '')),
    'fiduciario_rfc' => strtoupper(trim((string)($post['fiduciario_rfc'] ?? ''))),
    'fiduciario_nombre' => trim((string)($post['fiduciario_nombre'] ?? '')),
  ];

  foreach ([
    'nombres','apellido_paterno','apellido_materno','curp','email','pais_nacionalidad','pais_nacimiento','actividad_economica','ocupacion','giro_mercantil',
    'dom_num_int','tel1_ext','tel2','tel2_ext','firma_path','objeto_social','rep_nombre','rep_rfc','rep_curp','rep_nacionalidad','rep_firma_path',
    'fideicomiso_identificador','fiduciario_rfc','fiduciario_nombre'
  ] as $k) {
    if ($r[$k] === '') $r[$k] = null;
  }

  return $r;
}

// Routing
switch ($path) {
  case '/':
    if (!empty($_SESSION['user_id'])) redirect('/dashboard');
    redirect('/login');

  case '/login':
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
      render('login', ['error' => $_GET['err'] ?? null]);
      break;
    }
    csrf_verify($config);
    $email = strtolower(trim($_POST['email'] ?? ''));
    $pass = $_POST['password'] ?? '';

    [$locked, $msg] = login_attempt_is_locked($db, $email);
    if ($locked) redirect('/login?err=' . urlencode($msg));

    $st = $db->prepare("SELECT id, password_hash, is_blocked FROM users WHERE email = :e");
    $st->execute([':e' => $email]);
    $u = $st->fetch();
    if (!$u || (int)$u['is_blocked'] === 1 || !password_verify($pass, $u['password_hash'])) {
      if ($u) record_failed_login($db, $config, (int)$u['id']);
      redirect('/login?err=' . urlencode('Credenciales incorrectas.'));
    }

    clear_failed_login($db, (int)$u['id']);
    $_SESSION['user_id'] = (int)$u['id'];
    $_SESSION['two_factor_ok'] = 0;

    // Skip 2FA if this browser is trusted
    if (trusted_device_is_valid($db, (int)$u['id'])) {
      $_SESSION['two_factor_ok'] = 1;
      redirect('/dashboard');
    }

    $code = create_two_factor_code($db, $config, (int)$u['id']);
    send_email($config, $email, 'Código de verificación', "Tu código es: $code\nVence en " . $config['app']['two_factor']['code_minutes'] . " minutos.");

    redirect('/verify');

  case '/verify':
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
      if (empty($_SESSION['user_id'])) redirect('/login');
      render('verify');
      break;
    }
    csrf_verify($config);
    if (empty($_SESSION['user_id'])) redirect('/login');
    $code = preg_replace('/\D+/', '', $_POST['code'] ?? '');
    if (strlen($code) !== 6 || !verify_two_factor_code($db, (int)$_SESSION['user_id'], $code)) {
      render('verify', ['error' => 'Código inválido o vencido.']);
      break;
    }
    $_SESSION['two_factor_ok'] = 1;

    // Trust this device for 30 days (optional)
    $trust = (int)($_POST['trust_device'] ?? 0) === 1;
    if ($trust) {
      trusted_device_issue($db, (int)$_SESSION['user_id'], 30);
    }

    redirect('/dashboard');

  case '/resend-code':
    if (empty($_SESSION['user_id'])) redirect('/login');
    $uid = (int)$_SESSION['user_id'];
    $st = $db->prepare("SELECT email FROM users WHERE id = :id");
    $st->execute([':id'=>$uid]);
    $email = (string)$st->fetchColumn();
    $code = create_two_factor_code($db, $config, $uid);
    send_email($config, $email, 'Código de verificación', "Tu código es: $code\nVence en " . $config['app']['two_factor']['code_minutes'] . " minutos.");
    redirect('/verify');

  case '/forgot':
    if ($_SERVER['REQUEST_METHOD'] === 'GET') { render('forgot'); break; }
    csrf_verify($config);
    $email = strtolower(trim($_POST['email'] ?? ''));
    $st = $db->prepare("SELECT id FROM users WHERE email = :e AND is_blocked = 0");
    $st->execute([':e'=>$email]);
    $uid = $st->fetchColumn();
    // Always show success message to avoid user enumeration
    if ($uid) {
      $token = bin2hex(random_bytes(32));
      $hash = hash('sha256', $token);
      $expires = date('Y-m-d H:i:s', time() + ((int)$config['app']['password_reset']['token_minutes']) * 60);
      $db->prepare("INSERT INTO password_resets (user_id, token_hash, expires_at, used_at) VALUES (:uid,:h,:ex,NULL)")
         ->execute([':uid'=>(int)$uid, ':h'=>$hash, ':ex'=>$expires]);
      $link = app_url('/reset?token=' . urlencode($token), true);
      send_email($config, $email, 'Restablecer contraseña', "Abre este enlace para restablecer tu contraseña:\n$link\nVence en " . $config['app']['password_reset']['token_minutes'] . " minutos.");
    }
    render('forgot', ['message' => 'Si el correo existe, te enviaremos un enlace.']);
    break;

  case '/reset':
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
      render('reset', ['token' => $_GET['token'] ?? '']);
      break;
    }
    csrf_verify($config);
    $token = $_POST['token'] ?? '';
    $pw1 = $_POST['password'] ?? '';
    $pw2 = $_POST['password2'] ?? '';
    if ($pw1 !== $pw2) { render('reset', ['error'=>'Las contraseñas no coinciden.', 'token'=>$token]); break; }
    if (strlen($pw1) < 8) { render('reset', ['error'=>'Mínimo 8 caracteres.', 'token'=>$token]); break; }

    $hash = hash('sha256', $token);
    $st = $db->prepare("SELECT id, user_id FROM password_resets WHERE token_hash = :h AND used_at IS NULL AND expires_at > NOW() ORDER BY id DESC LIMIT 1");
    $st->execute([':h'=>$hash]);
    $row = $st->fetch();
    if (!$row) { render('reset', ['error'=>'Token inválido o vencido.', 'token'=>$token]); break; }

    $db->prepare("UPDATE users SET password_hash = :ph WHERE id = :uid")
       ->execute([':ph'=>password_hash($pw1, PASSWORD_DEFAULT), ':uid'=>(int)$row['user_id']]);
    $db->prepare("UPDATE password_resets SET used_at = NOW() WHERE id = :id")
       ->execute([':id'=>(int)$row['id']]);

    redirect('/login?err=' . urlencode('Contraseña actualizada. Inicia sesión.'));

  case '/logout':
    trusted_device_clear();
    session_destroy();
    redirect('/login');

  case '/dashboard':
    $user = require_auth($db);
    render('dashboard', []);
    break;

  case '/audit':
    $user = require_auth($db);
    require_perm($db, 'audit.view');

    $filters = [
      'user_id' => $_GET['user_id'] ?? '',
      'action' => trim($_GET['action'] ?? ''),
      'entity_type' => trim($_GET['entity_type'] ?? ''),
      'entity_id' => trim($_GET['entity_id'] ?? ''),
      'date_from' => trim($_GET['date_from'] ?? ''),
      'date_to' => trim($_GET['date_to'] ?? ''),
    ];

    $where = [];
    $params = [];
    if ($filters['user_id'] !== '') { $where[] = "a.user_id = :uid"; $params[':uid'] = (int)$filters['user_id']; }
    if ($filters['action'] !== '') { $where[] = "a.action LIKE :act"; $params[':act'] = $filters['action'] . '%'; }
    if ($filters['entity_type'] !== '') { $where[] = "a.entity_type = :et"; $params[':et'] = $filters['entity_type']; }
    if ($filters['entity_id'] !== '') { $where[] = "a.entity_id = :eid"; $params[':eid'] = (int)$filters['entity_id']; }
    if ($filters['date_from'] !== '') { $where[] = "a.created_at >= :df"; $params[':df'] = $filters['date_from'] . " 00:00:00"; }
    if ($filters['date_to'] !== '') { $where[] = "a.created_at <= :dt"; $params[':dt'] = $filters['date_to'] . " 23:59:59"; }

    $sql = "SELECT a.*, u.name AS user_name, u.email AS user_email
            FROM audit_logs a
            LEFT JOIN users u ON u.id = a.user_id";
    if ($where) $sql .= " WHERE " . implode(" AND ", $where);
    $sql .= " ORDER BY a.id DESC LIMIT 200";

    $st = $db->prepare($sql);
    $st->execute($params);
    $rows = $st->fetchAll();

    $users = $db->query("SELECT id, name, email FROM users ORDER BY name")->fetchAll();
    render('audit', ['rows'=>$rows, 'users'=>$users, 'filters'=>$filters]);
    break;

  // Notifications API
  case '/api/notifications':
    header('Content-Type: application/json; charset=utf-8');
    try {
      $user = require_auth($db);

      $days = (int)($config['app']['notifications']['doc_expiry_days'] ?? 30);
      $override = get_setting($db, 'doc_expiry_days', null);
      if ($override !== null && $override !== '') $days = (int)$override;
      $days = max(0, (int)$days);

      $requiredDocsJson = get_setting($db, 'required_docs_json', '{}');
      $requiredAvisosJson = get_setting($db, 'required_avisos_json', '{}');
      $requiredDocs = json_decode($requiredDocsJson, true) ?: [];
      $requiredAvisos = json_decode($requiredAvisosJson, true) ?: [];

      // Expiring docs (avoid binding inside INTERVAL - shared hosting PDO/MySQL can be picky)
      $sql = "SELECT c.id AS client_id, c.nombre AS cliente, d.doc_tipo, d.vence
              FROM client_documents d
              JOIN clients c ON c.id = d.client_id
              WHERE d.vence <= DATE_ADD(CURDATE(), INTERVAL {$days} DAY)
              ORDER BY d.vence ASC
              LIMIT 50";
      $expiring = $db->query($sql)->fetchAll();

      // Strong KYC checks: pull needed columns
      $clients = $db->query("SELECT id, tipo, nombre, nombres, apellido_paterno, apellido_materno, rfc, curp, email, pais_nacionalidad, pais_nacimiento, actividad_economica, ocupacion, giro_mercantil, fecha_base, dom_calle, dom_num_ext, dom_num_int, dom_colonia, dom_municipio, dom_ciudad, dom_estado, dom_cp, tel1, tel1_ext, tel2, tel2_ext, firma_path, objeto_social, rep_nombre, rep_rfc, rep_curp, rep_nacionalidad, rep_fecha_nacimiento, rep_firma_path, fideicomiso_identificador, fiduciario_rfc, fiduciario_nombre FROM clients ORDER BY id DESC LIMIT 5000")->fetchAll();

      $missing = [];
      foreach ($clients as $c) {
        $miss = [];

        // Strong KYC rules per tipo
        $errs = validate_client_kyc($c);
        foreach ($errs as $e) $miss[] = $e;

        // Required documents per tipo (configurable)
        $reqDocs = $requiredDocs[$c['tipo']] ?? [];
        if (is_array($reqDocs) && $reqDocs) {
          $st = $db->prepare("SELECT doc_tipo FROM client_documents WHERE client_id = :id");
          $st->execute([':id'=>$c['id']]);
          $have = array_map(fn($r)=>mb_strtoupper(trim((string)$r['doc_tipo'])), $st->fetchAll());
          $haveSet = array_flip($have);
          $missingDocs = [];
          foreach ($reqDocs as $rd) {
            $key = mb_strtoupper(trim((string)$rd));
            if ($key === '') continue;
            if (!isset($haveSet[$key])) $missingDocs[] = (string)$rd;
          }
          if ($missingDocs) $miss[] = 'Faltan documentos: ' . implode(', ', $missingDocs);
        }

        if ($miss) {
          $missing[] = [
            'client_id' => (int)$c['id'],
            'cliente' => $c['nombre'] ?: '(sin nombre)',
            'tipo' => $c['tipo'],
            'missing' => $miss
          ];
        }
      }
      $missing = array_slice($missing, 0, 50);

      $drafts = $db->query("SELECT a.id, c.id AS client_id, c.nombre AS cliente, a.mes_reportado, a.referencia_aviso
                            FROM avisos a
                            JOIN clients c ON c.id = a.client_id
                            WHERE a.status = 'DRAFT'
                            ORDER BY a.updated_at DESC
                            LIMIT 50")->fetchAll();

      // Required aviso alerts (configurable conditions)
      $requiredAvisosList = [];
      $requireMonthly = (bool)($requiredAvisos['require_monthly'] ?? false);
      $monthOffset = (int)($requiredAvisos['month_offset'] ?? 0);
      $statusRequired = (string)($requiredAvisos['status_required'] ?? 'XML_GENERATED');

      if ($requireMonthly) {
        $target = new DateTime('first day of this month');
        if ($monthOffset !== 0) $target->modify(($monthOffset > 0 ? '+' : '') . $monthOffset . ' month');
        $mes = $target->format('Ym');

        // FIX: do NOT reuse :status twice (PDO named params can’t repeat -> HY093)
        $st = $db->prepare("SELECT c.id, c.nombre, c.rfc, c.tipo
                            FROM clients c
                            WHERE NOT EXISTS (
                              SELECT 1 FROM avisos a
                              WHERE a.client_id = c.id
                                AND a.mes_reportado = :mes
                                AND (:status1 = '' OR a.status = :status2)
                            )
                            ORDER BY c.id DESC
                            LIMIT 50");
        $st->execute([':mes'=>$mes, ':status1'=>$statusRequired, ':status2'=>$statusRequired]);
        $rows = $st->fetchAll();

        foreach ($rows as $r) {
          $requiredAvisosList[] = [
            'client_id' => (int)$r['id'],
            'cliente' => $r['nombre'],
            'tipo' => $r['tipo'],
            'mes_reportado' => $mes,
            'reason' => 'Falta aviso del mes ' . $mes
          ];
        }
      }

      json_out([
        'expiring_docs' => $expiring,
        'missing_clients' => $missing,
        'draft_avisos' => $drafts,
        'required_avisos' => $requiredAvisosList,
        'counts' => [
          'expiring_docs' => count($expiring),
          'missing_clients' => count($missing),
          'draft_avisos' => count($drafts),
          'required_avisos' => count($requiredAvisosList),
        ],
      ]);
    } catch (Throwable $e) {
      json_out(['error'=>'notifications_failed','message'=>$e->getMessage()], 500);
    }
    break;;

  // Clientes
  case '/clientes':
    $user = require_auth($db);
    require_perm($db, 'clientes.view');
    $q = trim($_GET['q'] ?? '');
    $params = [];
    $sql = "SELECT * FROM clients";
    if ($q !== '') {
      $sql .= " WHERE nombre LIKE :q OR rfc LIKE :q";
      $params[':q'] = '%' . $q . '%';
    }
    $sql .= " ORDER BY id DESC LIMIT 2000";
    $st = $db->prepare($sql);
    $st->execute($params);
    $clientes = $st->fetchAll();
    render('clientes_list', ['clientes'=>$clientes, 'q'=>$q]);
    break;

  case '/clientes/new':
    $user = require_auth($db);
    require_perm($db, 'clientes.edit');
    render('cliente_form', ['cliente'=>null, 'docs'=>[]]);
    break;

  case '/clientes/edit':
    $user = require_auth($db);
    require_perm($db, 'clientes.edit');
    $id = (int)($_GET['id'] ?? 0);
    $st = $db->prepare("SELECT * FROM clients WHERE id = :id");
    $st->execute([':id'=>$id]);
    $cliente = $st->fetch();
    $docs = [];
    if ($cliente) {
      $d = $db->prepare("SELECT * FROM client_documents WHERE client_id = :id ORDER BY vence ASC");
      $d->execute([':id'=>$id]);
      $docs = $d->fetchAll();
    }
    render('cliente_form', ['cliente'=>$cliente, 'docs'=>$docs]);
    break;

case '/clientes/create':
    $user = require_auth($db);
    require_perm($db, 'clientes.edit');
    csrf_verify($config);

    $row = client_post_to_row($_POST);
    $errs = validate_client_kyc($row);
    if ($errs) {
      render('cliente_form', ['cliente'=>null, 'docs'=>[], 'error'=>implode(" | ", $errs)]);
      break;
    }

    $sql = "INSERT INTO clients (
              tipo, nombre, nombres, apellido_paterno, apellido_materno,
              rfc, curp, email,
              pais_nacionalidad, pais_nacimiento,
              actividad_economica, ocupacion, extranjero,
              giro_mercantil, fecha_base,
              dom_calle, dom_num_ext, dom_num_int, dom_colonia, dom_municipio, dom_ciudad, dom_estado, dom_cp,
              tel1, tel1_ext, tel2, tel2_ext,
              firma_path,
              objeto_social,
              rep_nombre, rep_rfc, rep_curp, rep_nacionalidad, rep_fecha_nacimiento, rep_firma_path,
              fideicomiso_identificador, fiduciario_rfc, fiduciario_nombre,
              created_at, updated_at
            ) VALUES (
              :tipo, :nombre, :nombres, :ap, :am,
              :rfc, :curp, :email,
              :pais_nac, :pais_nacim,
              :act, :ocup, :extr,
              :giro, :fecha,
              :calle, :numext, :numint, :col, :mun, :ciu, :edo, :cp,
              :tel1, :tel1ext, :tel2, :tel2ext,
              :firma,
              :obj,
              :repnom, :reprfc, :repcurp, :repnac, :repfn, :repfirma,
              :fid, :fidrfc, :fidnom,
              NOW(), NOW()
            )";
    $db->prepare($sql)->execute([
      ':tipo'=>$row['tipo'],
      ':nombre'=>$row['nombre'],
      ':nombres'=>$row['nombres'],
      ':ap'=>$row['apellido_paterno'],
      ':am'=>$row['apellido_materno'],
      ':rfc'=>$row['rfc'],
      ':curp'=>$row['curp'],
      ':email'=>$row['email'],
      ':pais_nac'=>$row['pais_nacionalidad'],
      ':pais_nacim'=>$row['pais_nacimiento'],
      ':act'=>$row['actividad_economica'],
      ':ocup'=>$row['ocupacion'],
      ':extr'=>$row['extranjero'],
      ':giro'=>$row['giro_mercantil'],
      ':fecha'=>$row['fecha_base'],
      ':calle'=>$row['dom_calle'],
      ':numext'=>$row['dom_num_ext'],
      ':numint'=>$row['dom_num_int'],
      ':col'=>$row['dom_colonia'],
      ':mun'=>$row['dom_municipio'],
      ':ciu'=>$row['dom_ciudad'],
      ':edo'=>$row['dom_estado'],
      ':cp'=>$row['dom_cp'],
      ':tel1'=>$row['tel1'],
      ':tel1ext'=>$row['tel1_ext'],
      ':tel2'=>$row['tel2'],
      ':tel2ext'=>$row['tel2_ext'],
      ':firma'=>$row['firma_path'],
      ':obj'=>$row['objeto_social'],
      ':repnom'=>$row['rep_nombre'],
      ':reprfc'=>$row['rep_rfc'],
      ':repcurp'=>$row['rep_curp'],
      ':repnac'=>$row['rep_nacionalidad'],
      ':repfn'=>$row['rep_fecha_nacimiento'],
      ':repfirma'=>$row['rep_firma_path'],
      ':fid'=>$row['fideicomiso_identificador'],
      ':fidrfc'=>$row['fiduciario_rfc'],
      ':fidnom'=>$row['fiduciario_nombre'],
    ]);

    audit_log($db, (int)$user['id'], 'client.create', 'client', (int)$db->lastInsertId(), ['tipo'=>$row['tipo'], 'rfc'=>$row['rfc']]);
    redirect('/clientes');

case '/clientes/update':
    $user = require_auth($db);
    require_perm($db, 'clientes.edit');
    csrf_verify($config);

    $id = (int)($_POST['id'] ?? 0);
    if (!$id) { redirect('/clientes'); }

    $row = client_post_to_row($_POST);
    $errs = validate_client_kyc($row);
    if ($errs) {
      // Reload current docs for better UX
      $st = $db->prepare("SELECT * FROM clients WHERE id = :id");
      $st->execute([':id'=>$id]);
      $cliente = $st->fetch();
      $docs = [];
      if ($cliente) {
        $d = $db->prepare("SELECT * FROM client_documents WHERE client_id = :id ORDER BY vence ASC");
        $d->execute([':id'=>$id]);
        $docs = $d->fetchAll();
      }
      // Merge posted values so the form doesn't lose changes
      $cliente = array_merge((array)$cliente, $row);
      $cliente['id'] = $id;

      render('cliente_form', ['cliente'=>$cliente, 'docs'=>$docs, 'error'=>implode(" | ", $errs)]);
      break;
    }

    $sql = "UPDATE clients SET
              tipo = :tipo,
              nombre = :nombre,
              nombres = :nombres,
              apellido_paterno = :ap,
              apellido_materno = :am,
              rfc = :rfc,
              curp = :curp,
              email = :email,
              pais_nacionalidad = :pais_nac,
              pais_nacimiento = :pais_nacim,
              actividad_economica = :act,
              ocupacion = :ocup,
              extranjero = :extr,
              giro_mercantil = :giro,
              fecha_base = :fecha,
              dom_calle = :calle,
              dom_num_ext = :numext,
              dom_num_int = :numint,
              dom_colonia = :col,
              dom_municipio = :mun,
              dom_ciudad = :ciu,
              dom_estado = :edo,
              dom_cp = :cp,
              tel1 = :tel1,
              tel1_ext = :tel1ext,
              tel2 = :tel2,
              tel2_ext = :tel2ext,
              firma_path = :firma,
              objeto_social = :obj,
              rep_nombre = :repnom,
              rep_rfc = :reprfc,
              rep_curp = :repcurp,
              rep_nacionalidad = :repnac,
              rep_fecha_nacimiento = :repfn,
              rep_firma_path = :repfirma,
              fideicomiso_identificador = :fid,
              fiduciario_rfc = :fidrfc,
              fiduciario_nombre = :fidnom,
              updated_at = NOW()
            WHERE id = :id";
    $db->prepare($sql)->execute([
      ':tipo'=>$row['tipo'],
      ':nombre'=>$row['nombre'],
      ':nombres'=>$row['nombres'],
      ':ap'=>$row['apellido_paterno'],
      ':am'=>$row['apellido_materno'],
      ':rfc'=>$row['rfc'],
      ':curp'=>$row['curp'],
      ':email'=>$row['email'],
      ':pais_nac'=>$row['pais_nacionalidad'],
      ':pais_nacim'=>$row['pais_nacimiento'],
      ':act'=>$row['actividad_economica'],
      ':ocup'=>$row['ocupacion'],
      ':extr'=>$row['extranjero'],
      ':giro'=>$row['giro_mercantil'],
      ':fecha'=>$row['fecha_base'],
      ':calle'=>$row['dom_calle'],
      ':numext'=>$row['dom_num_ext'],
      ':numint'=>$row['dom_num_int'],
      ':col'=>$row['dom_colonia'],
      ':mun'=>$row['dom_municipio'],
      ':ciu'=>$row['dom_ciudad'],
      ':edo'=>$row['dom_estado'],
      ':cp'=>$row['dom_cp'],
      ':tel1'=>$row['tel1'],
      ':tel1ext'=>$row['tel1_ext'],
      ':tel2'=>$row['tel2'],
      ':tel2ext'=>$row['tel2_ext'],
      ':firma'=>$row['firma_path'],
      ':obj'=>$row['objeto_social'],
      ':repnom'=>$row['rep_nombre'],
      ':reprfc'=>$row['rep_rfc'],
      ':repcurp'=>$row['rep_curp'],
      ':repnac'=>$row['rep_nacionalidad'],
      ':repfn'=>$row['rep_fecha_nacimiento'],
      ':repfirma'=>$row['rep_firma_path'],
      ':fid'=>$row['fideicomiso_identificador'],
      ':fidrfc'=>$row['fiduciario_rfc'],
      ':fidnom'=>$row['fiduciario_nombre'],
      ':id'=>$id
    ]);

    audit_log($db, (int)$user['id'], 'client.update', 'client', $id, ['tipo'=>$row['tipo'], 'rfc'=>$row['rfc']]);
    redirect('/clientes/edit?id=' . $id);

  case '/clientes/docs/add':
    $user = require_auth($db);
    require_perm($db, 'clientes.edit');
    csrf_verify($config);
    $cid = (int)($_POST['cliente_id'] ?? 0);
    $docTipo = trim($_POST['doc_tipo'] ?? '');
    $vence = $_POST['vence'] ?? '';
    if (!$cid || !$docTipo || !$vence) redirect('/clientes/edit?id=' . $cid);
    if (empty($_FILES['archivo']['tmp_name'])) redirect('/clientes/edit?id=' . $cid);

    $dir = __DIR__ . '/storage/docs';
    if (!is_dir($dir)) mkdir($dir, 0775, true);
    $name = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $_FILES['archivo']['name']);
    $dest = $dir . '/' . $name;
    move_uploaded_file($_FILES['archivo']['tmp_name'], $dest);

    $publicPath = '/storage/docs/' . $name;
    $db->prepare("INSERT INTO client_documents (client_id, doc_tipo, file_path, vence, created_at) VALUES (:c,:t,:p,:v,NOW())")
       ->execute([':c'=>$cid, ':t'=>$docTipo, ':p'=>$publicPath, ':v'=>$vence]);
    audit_log($db, (int)$user['id'], 'client.doc.add', 'client', $cid, ['doc_tipo'=>$docTipo,'vence'=>$vence]);
    redirect('/clientes/edit?id=' . $cid);

  // Avisos
  case '/avisos':
    $user = require_auth($db);
    require_perm($db, 'avisos.view');
    $sql = "SELECT a.*, c.nombre AS cliente_nombre
            FROM avisos a JOIN clients c ON c.id = a.client_id
            ORDER BY a.updated_at DESC";
    $avisos = $db->query($sql)->fetchAll();
    render('avisos_list', ['avisos'=>$avisos]);
    break;

  case '/avisos/new':
    $user = require_auth($db);
    require_perm($db, 'avisos.edit');
    $clientes = $db->query("SELECT id, nombre, rfc FROM clients ORDER BY nombre")->fetchAll();
    render('aviso_form', ['aviso'=>null, 'clientes'=>$clientes]);
    break;

  case '/avisos/edit':
    $user = require_auth($db);
    require_perm($db, 'avisos.edit');
    $id = (int)($_GET['id'] ?? 0);
    $st = $db->prepare("SELECT * FROM avisos WHERE id = :id");
    $st->execute([':id'=>$id]);
    $aviso = $st->fetch();
    $clientes = $db->query("SELECT id, nombre, rfc FROM clients ORDER BY nombre")->fetchAll();
    render('aviso_form', ['aviso'=>$aviso, 'clientes'=>$clientes]);
    break;

  // ... keep the rest of your existing routes below unchanged ...
  default:
    http_response_code(404);
    echo "404 Not Found";
    break;
}
