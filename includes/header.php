<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Helpdesk CNTT - TLU</title>
    <link rel="stylesheet" href="/helpdesk/public/assets/css/style.css">
</head>
<body>
<header class="topbar">
    <a href="/helpdesk/index.php" class="brand">Helpdesk CNTT - TLU</a>
    <nav>
        <a href="/helpdesk/modules/tickets/list.php">Tickets</a>
        <?php if (in_array($_SESSION['user_role'] ?? '', ['staff', 'manager'], true)): ?>
        <a href="/helpdesk/modules/categories/list.php">Danh muc</a>
        <a href="/helpdesk/modules/sla/list.php">SLA</a>
        <a href="/helpdesk/modules/surveys/list.php">Khao sat</a>
        <?php endif; ?>
        <?php if (!empty($_SESSION['user_id'])): ?>
            <span style="margin-left:16px;">
                Xin chao, <b><?= e($_SESSION['user_name']) ?></b>
                (<?= e($_SESSION['user_role']) ?>)
            </span>
            <a href="/helpdesk/modules/auth/logout.php">Dang xuat</a>
        <?php else: ?>
            <a href="/helpdesk/modules/auth/login.php">Dang nhap</a>
        <?php endif; ?>
    </nav>
</header>
<main class="container">
<?php
$flash = get_flash();
if ($flash): ?>
    <div class="flash flash-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
<?php endif; ?>
