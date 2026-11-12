<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('list.php');
}

csrf_check();

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    set_flash('error', 'Khao sat khong hop le.');
    redirect('list.php');
}

$stmt = mysqli_prepare($conn, "DELETE FROM satisfaction_surveys WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);

if (mysqli_stmt_execute($stmt)) {
    set_flash('success', 'Da xoa khao sat.');
} else {
    set_flash('error', 'Loi khi xoa: ' . mysqli_error($conn));
}
mysqli_stmt_close($stmt);

redirect('list.php');
