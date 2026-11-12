<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Helpdesk CNTT - TLU</title>
    <link rel="stylesheet" href="<?= e(app_url('public/assets/css/style.css')) ?>">
</head>
<body>
<header class="topbar">
    <a href="<?= e(app_url('index.php')) ?>" class="brand">Helpdesk CNTT - TLU</a>
    <nav>
        <a href="<?= e(app_url('modules/tickets/list.php')) ?>">Tickets</a>
        <?php if (in_array($_SESSION['user_role'] ?? '', ['staff', 'manager'], true)): ?>
        <a href="<?= e(app_url('modules/categories/list.php')) ?>">Danh muc</a>
        <a href="<?= e(app_url('modules/sla/list.php')) ?>">SLA</a>
        <a href="<?= e(app_url('modules/surveys/list.php')) ?>">Khao sat</a>
        <?php endif; ?>
        <?php if (!empty($_SESSION['user_id'])): ?>
            <span style="margin-left:16px;">
                Xin chao, <b><?= e($_SESSION['user_name']) ?></b>
                (<?= e($_SESSION['user_role']) ?>)
            </span>
            <a href="<?= e(app_url('modules/auth/logout.php')) ?>">Dang xuat</a>
        <?php else: ?>
            <a href="<?= e(app_url('modules/auth/login.php')) ?>">Dang nhap</a>
        <?php endif; ?>
    </nav>
</header>
<main class="container">
<?php
$flash = get_flash();
if ($flash): ?>
    <div class="flash flash-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
<?php endif; ?>
