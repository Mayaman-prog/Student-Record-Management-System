<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';

$u = current_user();
if (!$u) redirect('login.php');

if ($u['role'] === 'admin') redirect('admin/dashboard.php');
if ($u['role'] === 'staff') redirect('staff/dashboard.php');
redirect('student/dashboard.php');
?>