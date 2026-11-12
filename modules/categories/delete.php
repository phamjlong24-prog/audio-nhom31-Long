<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth_check.php';
require_role(['staff', 'manager']);

// Chi cho phep xoa bang POST, khong cho GET (yeu cau bat buoc trong barem)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('list.php');
}

csrf_check();

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    set_flash('error', 'Danh muc khong hop le.');
    redirect('list.php');
}

// Xoa an toan: kiem tra con ticket nao dang dung danh muc nay khong
$checkSql = "SELECT COUNT(*) AS total FROM tickets WHERE category_id = ?";
$chk = mysqli_prepare($conn, $checkSql);
mysqli_stmt_bind_param($chk, 'i', $id);
mysqli_stmt_execute($chk);
$res = mysqli_stmt_get_result($chk);
$row = mysqli_fetch_assoc($res);
mysqli_stmt_close($chk);

if ($row['total'] > 0) {
    set_flash('error', "Khong the xoa: con {$row['total']} ticket dang thuoc danh muc nay.");
    redirect('list.php');
}

// An toan roi, tien hanh xoa (sla_rules se tu dong xoa theo vi co ON DELETE CASCADE)
$stmt = mysqli_prepare($conn, "DELETE FROM ticket_categories WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);

if (mysqli_stmt_execute($stmt)) {
    set_flash('success', 'Da xoa danh muc.');
} else {
    set_flash('error', 'Loi khi xoa: ' . mysqli_error($conn));
}
mysqli_stmt_close($stmt);

redirect('list.php');
