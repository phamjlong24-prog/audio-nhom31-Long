<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
if ($id <= 0) {
    set_flash('error', 'SLA khong hop le.');
    redirect('list.php');
}

$sql = "SELECT s.*, c.name AS category_name
        FROM sla_rules s
        JOIN ticket_categories c ON c.id = s.category_id
        WHERE s.id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$sla = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$sla) {
    set_flash('error', 'Khong tim thay SLA.');
    redirect('list.php');
}

$errors = [];
$response_hours = $sla['response_hours'];
$resolve_hours = $sla['resolve_hours'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $response_hours = (int)($_POST['response_hours'] ?? 0);
    $resolve_hours = (int)($_POST['resolve_hours'] ?? 0);

    if ($response_hours <= 0) {
        $errors[] = 'Han phan hoi phai lon hon 0.';
    }
    if ($resolve_hours <= 0) {
        $errors[] = 'Han xu ly xong phai lon hon 0.';
    }
    if ($response_hours > 0 && $resolve_hours > 0 && $resolve_hours < $response_hours) {
        $errors[] = 'Han xu ly xong phai >= han phan hoi.';
    }

    if (empty($errors)) {
        $upd = mysqli_prepare($conn, "UPDATE sla_rules SET response_hours = ?, resolve_hours = ? WHERE id = ?");
        mysqli_stmt_bind_param($upd, 'iii', $response_hours, $resolve_hours, $id);

        if (mysqli_stmt_execute($upd)) {
            set_flash('success', 'Cap nhat SLA thanh cong.');
            redirect('list.php');
        } else {
            $errors[] = 'Loi khi cap nhat: ' . mysqli_error($conn);
        }
        mysqli_stmt_close($upd);
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<h1>Sua SLA cho danh muc: <?= e($sla['category_name']) ?></h1>

<?php if (!empty($errors)): ?>
    <div class="flash flash-error">
        <ul style="margin:0;padding-left:18px;">
            <?php foreach ($errors as $err): ?>
                <li><?= e($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="POST" action="edit.php?id=<?= (int)$id ?>">
    <input type="hidden" name="id" value="<?= (int)$id ?>">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

    <p><em>Danh muc: <?= e($sla['category_name']) ?> (khong the doi danh muc, chi sua thoi han)</em></p>

    <div class="form-group">
        <label for="response_hours">Han phan hoi (gio) *</label>
        <input type="number" id="response_hours" name="response_hours" min="1"
               value="<?= e($response_hours) ?>" required>
    </div>

    <div class="form-group">
        <label for="resolve_hours">Han xu ly xong (gio) *</label>
        <input type="number" id="resolve_hours" name="resolve_hours" min="1"
               value="<?= e($resolve_hours) ?>" required>
    </div>

    <button type="submit" class="btn">Cap nhat</button>
    <a href="list.php">Huy</a>
</form>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
