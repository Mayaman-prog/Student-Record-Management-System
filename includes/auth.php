<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

function current_user(): ?array {
  return $_SESSION['user'] ?? null;
}
function require_login(string $loginPath): void {
  if (!current_user()) redirect($loginPath);
}
function require_role(array $roles, string $loginPath): void {
  require_login($loginPath);
  $r = (string)(current_user()['role'] ?? '');
  if (!in_array($r, $roles, true)) {
    http_response_code(403);
    echo "Access denied.";
    exit;
  }
}
?>