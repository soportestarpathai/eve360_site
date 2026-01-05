<?php
// app/bootstrap.php
$config = require __DIR__ . '/config.php';

session_name($config['app']['session_name']);
session_start();

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/rbac.php';
require_once __DIR__ . '/mail.php';
require_once __DIR__ . '/audit.php';
require_once __DIR__ . '/trusted_devices.php';

$db = db($config);

/**
 * Build an app link that works on shared hosting:
 * /eve360/index.php?path=/route
 *
 * - Supports querystrings properly (won’t break routing)
 * - If $absolute=true and app.base_full_url exists, returns full URL (for emails)
 */
function app_url(string $path, bool $absolute = false): string {
  global $config;

  $base = rtrim($config['app']['base_url'] ?? '', '/'); // e.g. /eve360
  $path = '/' . ltrim($path, '/');                      // ensure leading slash

  // Split path + query so router receives clean "/login" and PHP gets $_GET normally
  $parts = parse_url($path);
  $route = $parts['path'] ?? '/';
  $query = $parts['query'] ?? '';

  $url = $base . '/index.php?path=' . urlencode($route);

  // Append querystring as normal query parameters (NOT encoded into path)
  if ($query !== '') {
    $url .= '&' . $query;
  }

  if (!$absolute) return $url;

  // For email links: define in config.php:
  // $config['app']['base_full_url'] = 'https://www.adsoft.mx/eve360';
  $full = rtrim($config['app']['base_full_url'] ?? '', '/');
  if ($full !== '') return $full . $url;

  // Fallback: return relative if base_full_url not configured
  return $url;
}
