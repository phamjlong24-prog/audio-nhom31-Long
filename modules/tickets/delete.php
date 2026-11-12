<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('list.php');
}
csrf_check();

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    set_flash('error', 'Ticket khong hop le.');
    redirect('list.php');
}

$stmt = mysqli_prepare($conn, "SELECT status FROM tickets WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$ticket = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$ticket) {
    set_flash('error', 'Ticket khong ton tai.');
    redirect('list.php');
}

// XOA AN TOAN: chi cho xoa cung khi ticket con o trang thai 'open'
// (tuc la chua co ai xu ly, chua co assignment/comment nao dang ke)
// Neu da co hoat dong that, KHONG cho xoa cung - chi doi status thanh 'cancelled'
if ($ticket['status'] !== 'open') {
    // Chuyen sang cancelled thay vi xoa that, giu lai lich su
    mysqli_begin_transaction($conn);
    try {
        $upd = mysqli_prepare($conn, "UPDATE tickets SET status = 'cancelled' WHERE id = ?");
        mysqli_stmt_bind_param($upd, 'i', $id);
        mysqli_stmt_execute($upd);
        mysqli_stmt_close($upd);

        $log = mysqli_prepare($conn, "INSERT INTO ticket_status_logs (ticket_id, old_status, new_status, changed_by) VALUES (?, ?, 'cancelled', 1)");
        mysqli_stmt_bind_param($log, 'is', $id, $ticket['status']);
        mysqli_stmt_execute($log);
        mysqli_stmt_close($log);

        mysqli_commit($conn);
        set_flash('success', 'Ticket da co hoat dong nen khong xoa cung - da chuyen sang trang thai cancelled thay vi xoa.');
    } catch (Exception $e) {
        mysqli_rollback($conn);
        set_flash('error', 'Loi: ' . $e->getMessage());
    }
} else {
    // An toan de xoa cung vi con 'open', chua ai dong cham
    $del = mysqli_prepare($conn, "DELETE FROM tickets WHERE id = ?");
    mysqli_stmt_bind_param($del, 'i', $id);
    if (mysqli_stmt_execute($del)) {
        set_flash('success', 'Da xoa ticket.');
    } else {
        set_flash('error', 'Loi khi xoa: ' . mysqli_error($conn));
    }
    mysqli_stmt_close($del);
}

redirect('list.php');
