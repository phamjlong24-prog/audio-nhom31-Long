<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('list.php');
}

csrf_check();

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    set_flash('error', 'SLA khong hop le.');
    redirect('list.php');
}

// sla_rules khong bi bang nao khac tham chieu toi (khong ai FK vao no) nen xoa truc tiep an toan
$stmt = mysqli_prepare($conn, "DELETE FROM sla_rules WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);

if (mysqli_stmt_execute($stmt)) {
    set_flash('success', 'Da xoa SLA.');
} else {
    set_flash('error', 'Loi khi xoa: ' . mysqli_error($conn));
}
mysqli_stmt_close($stmt);

redirect('list.php');
