<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/helpdesk/modules/tickets/list.php');
}
csrf_check();

// TAM THOI: chua co Auth that
$current_user_id = 1;

$ticket_id = (int)($_POST['ticket_id'] ?? 0);
$content = trim($_POST['content'] ?? '');

if ($ticket_id <= 0) {
    set_flash('error', 'Ticket khong hop le.');
    redirect('/helpdesk/modules/tickets/list.php');
}

if ($content === '') {
    set_flash('error', 'Noi dung binh luan khong duoc de trong.');
    redirect("/helpdesk/modules/tickets/detail.php?id={$ticket_id}");
}

$stmt = mysqli_prepare($conn, "INSERT INTO ticket_comments (ticket_id, user_id, content) VALUES (?, ?, ?)");
mysqli_stmt_bind_param($stmt, 'iis', $ticket_id, $current_user_id, $content);

if (mysqli_stmt_execute($stmt)) {
    set_flash('success', 'Da gui binh luan.');
} else {
    set_flash('error', 'Loi khi gui binh luan: ' . mysqli_error($conn));
}
mysqli_stmt_close($stmt);

redirect("/helpdesk/modules/tickets/detail.php?id={$ticket_id}");
