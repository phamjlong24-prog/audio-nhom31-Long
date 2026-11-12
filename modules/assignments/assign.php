<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth_check.php';
require_role(['staff', 'manager']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(app_url('modules/tickets/list.php'));
}
csrf_check();

$ticket_id = (int)($_POST['ticket_id'] ?? 0);
$staff_id = (int)($_POST['staff_id'] ?? 0);

if ($ticket_id <= 0 || $staff_id <= 0) {
    set_flash('error', 'Du lieu phan cong khong hop le.');
    redirect(app_url("modules/tickets/detail.php?id={$ticket_id}"));
}

// Kiem tra staff_id thuc su co role = staff
$chk = mysqli_prepare($conn, "SELECT id FROM users WHERE id = ? AND role = 'staff'");
mysqli_stmt_bind_param($chk, 'i', $staff_id);
mysqli_stmt_execute($chk);
mysqli_stmt_store_result($chk);
if (mysqli_stmt_num_rows($chk) === 0) {
    mysqli_stmt_close($chk);
    set_flash('error', 'Nhan vien khong hop le.');
    redirect(app_url("modules/tickets/detail.php?id={$ticket_id}"));
}
mysqli_stmt_close($chk);

// RANG BUOC QUAN TRONG: dam bao chi 1 assignment active/ticket
// Buoc 1: tat het assignment dang active cu cua ticket nay (khong xoa, chi danh dau khong active)
// Buoc 2: tao assignment moi voi is_active = 1
mysqli_begin_transaction($conn);
try {
    $deactivate = mysqli_prepare($conn, "UPDATE ticket_assignments SET is_active = 0 WHERE ticket_id = ? AND is_active = 1");
    mysqli_stmt_bind_param($deactivate, 'i', $ticket_id);
    mysqli_stmt_execute($deactivate);
    mysqli_stmt_close($deactivate);

    $insert = mysqli_prepare($conn, "INSERT INTO ticket_assignments (ticket_id, staff_id, is_active) VALUES (?, ?, 1)");
    mysqli_stmt_bind_param($insert, 'ii', $ticket_id, $staff_id);
    mysqli_stmt_execute($insert);
    mysqli_stmt_close($insert);

    // Neu ticket dang 'open' thi tu dong chuyen sang 'assigned'
    $updStatus = mysqli_prepare($conn, "UPDATE tickets SET status = 'assigned' WHERE id = ? AND status = 'open'");
    mysqli_stmt_bind_param($updStatus, 'i', $ticket_id);
    mysqli_stmt_execute($updStatus);
    mysqli_stmt_close($updStatus);

    mysqli_commit($conn);
    set_flash('success', 'Phan cong thanh cong.');
} catch (Exception $e) {
    mysqli_rollback($conn);
    set_flash('error', 'Loi khi phan cong: ' . $e->getMessage());
}

redirect(app_url("modules/tickets/detail.php?id={$ticket_id}"));
