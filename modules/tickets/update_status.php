<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('list.php');
}
csrf_check();

// TAM THOI: chua co Auth that
$current_user_id = 1;

$ticket_id = (int)($_POST['ticket_id'] ?? 0);
$new_status = trim($_POST['new_status'] ?? '');
$validStatuses = ['open', 'assigned', 'in_progress', 'resolved', 'closed', 'cancelled'];

if ($ticket_id <= 0 || !in_array($new_status, $validStatuses, true)) {
    set_flash('error', 'Du lieu doi trang thai khong hop le.');
    redirect("detail.php?id={$ticket_id}");
}

// Lay trang thai hien tai truoc khi doi (de ghi log old_status)
$stmt = mysqli_prepare($conn, "SELECT status FROM tickets WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $ticket_id);
mysqli_stmt_execute($stmt);
$ticket = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$ticket) {
    set_flash('error', 'Ticket khong ton tai.');
    redirect('list.php');
}

$old_status = $ticket['status'];

if ($old_status === $new_status) {
    set_flash('error', 'Trang thai khong thay doi.');
    redirect("detail.php?id={$ticket_id}");
}

// Dung transaction: update ticket VA ghi log phai xay ra cung luc
mysqli_begin_transaction($conn);
try {
    $upd = mysqli_prepare($conn, "UPDATE tickets SET status = ? WHERE id = ?");
    mysqli_stmt_bind_param($upd, 'si', $new_status, $ticket_id);
    mysqli_stmt_execute($upd);
    mysqli_stmt_close($upd);

    // ticket_status_logs la bang BAT BIEN - chi duoc INSERT, khong duoc UPDATE/DELETE
    $log = mysqli_prepare($conn, "INSERT INTO ticket_status_logs (ticket_id, old_status, new_status, changed_by) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($log, 'issi', $ticket_id, $old_status, $new_status, $current_user_id);
    mysqli_stmt_execute($log);
    mysqli_stmt_close($log);

    mysqli_commit($conn);
    set_flash('success', "Da doi trang thai tu '{$old_status}' sang '{$new_status}'.");
} catch (Exception $e) {
    mysqli_rollback($conn);
    set_flash('error', 'Loi khi doi trang thai: ' . $e->getMessage());
}

redirect("detail.php?id={$ticket_id}");
