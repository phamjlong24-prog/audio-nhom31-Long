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
        <a href="/helpdesk/modules/categories/list.php">Danh muc</a>
        <a href="/helpdesk/modules/sla/list.php">SLA</a>
        <a href="/helpdesk/modules/surveys/list.php">Khao sat</a>
    </nav>
</header>
<main class="container">
<?php
$flash = get_flash();
if ($flash): ?>
    <div class="flash flash-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
<?php endif; ?>
