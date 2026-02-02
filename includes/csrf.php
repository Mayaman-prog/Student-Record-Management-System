<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

function csrf_token(): string {
  if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
  }
  return $_SESSION['csrf_token'];
}
function csrf_input(): string {
  return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}
function csrf_verify(): bool {
  $sent = (string)($_POST['csrf_token'] ?? '');
  $real = (string)($_SESSION['csrf_token'] ?? '');
  return $sent !== '' && $real !== '' && hash_equals($real, $sent);
}
?>