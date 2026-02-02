<?php
declare(strict_types=1);

function e(string $v): string {
  return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}
function redirect(string $path): void {
  header("Location: {$path}");
  exit;
}
function is_post(): bool {
  return ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
}
function get(string $k): string {
  return trim((string)($_GET[$k] ?? ''));
}
function post(string $k): string {
  return trim((string)($_POST[$k] ?? ''));
}
function post_int(string $k, int $d=0): int {
  $v = $_POST[$k] ?? null;
  if ($v === null || $v === '') return $d;
  return (int)$v;
}
function post_float(string $k, float $d=0.0): float {
  $v = $_POST[$k] ?? null;
  if ($v === null || $v === '') return $d;
  return (float)$v;
}
?>