<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth_check.php';
require_role(['staff', 'manager']);

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
if ($id <= 0) {
    set_flash('error', 'Danh muc khong hop le.');
    redirect('list.php');
}

// Lay du lieu hien tai
$stmt = mysqli_prepare($conn, "SELECT * FROM ticket_categories WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$category = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$category) {
    set_flash('error', 'Khong tim thay danh muc.');
    redirect('list.php');
}

$errors = [];
$name = $category['name'];
$description = $category['description'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($name === '') {
        $errors[] = 'Ten danh muc khong duoc de trong.';
    } elseif (mb_strlen($name) > 100) {
        $errors[] = 'Ten danh muc toi da 100 ky tu.';
    }

    // Kiem tra trung ten VOI danh muc KHAC (khong tinh chinh no)
    if (empty($errors)) {
        $checkSql = "SELECT id FROM ticket_categories WHERE name = ? AND id != ?";
        $chk = mysqli_prepare($conn, $checkSql);
        mysqli_stmt_bind_param($chk, 'si', $name, $id);
        mysqli_stmt_execute($chk);
        mysqli_stmt_store_result($chk);
        if (mysqli_stmt_num_rows($chk) > 0) {
            $errors[] = 'Ten danh muc nay da ton tai o mot danh muc khac.';
        }
        mysqli_stmt_close($chk);
    }

    if (empty($errors)) {
        $sql = "UPDATE ticket_categories SET name = ?, description = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'ssi', $name, $description, $id);

        if (mysqli_stmt_execute($stmt)) {
            set_flash('success', 'Cap nhat danh muc thanh cong.');
            redirect('list.php');
        } else {
            $errors[] = 'Loi khi cap nhat: ' . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<h1>Sua danh muc</h1>

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

    <div class="form-group">
        <label for="name">Ten danh muc *</label>
        <input type="text" id="name" name="name" maxlength="100"
               value="<?= e($name) ?>" required>
    </div>

    <div class="form-group">
        <label for="description">Mo ta</label>
        <textarea id="description" name="description" rows="3"><?= e($description) ?></textarea>
    </div>

    <button type="submit" class="btn">Cap nhat</button>
    <a href="list.php">Huy</a>
</form>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
