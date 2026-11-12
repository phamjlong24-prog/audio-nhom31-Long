<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';

$errors = [];
$name = '';
$description = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    // ---- Validation backend (bat buoc, khong chi dua vao frontend) ----
    if ($name === '') {
        $errors[] = 'Ten danh muc khong duoc de trong.';
    } elseif (mb_strlen($name) > 100) {
        $errors[] = 'Ten danh muc toi da 100 ky tu.';
    }

    // Kiem tra trung ten (UNIQUE) truoc khi insert de bao loi than thien
    if (empty($errors)) {
        $checkSql = "SELECT id FROM ticket_categories WHERE name = ?";
        $stmt = mysqli_prepare($conn, $checkSql);
        mysqli_stmt_bind_param($stmt, 's', $name);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $errors[] = 'Ten danh muc nay da ton tai.';
        }
        mysqli_stmt_close($stmt);
    }

    if (empty($errors)) {
        $sql = "INSERT INTO ticket_categories (name, description) VALUES (?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'ss', $name, $description);

        if (mysqli_stmt_execute($stmt)) {
            set_flash('success', 'Them danh muc thanh cong.');
            redirect('list.php');
        } else {
            $errors[] = 'Loi khi luu du lieu: ' . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<h1>Them danh muc yeu cau</h1>

<?php if (!empty($errors)): ?>
    <div class="flash flash-error">
        <ul style="margin:0;padding-left:18px;">
            <?php foreach ($errors as $err): ?>
                <li><?= e($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="POST" action="create.php">
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

    <button type="submit" class="btn">Luu danh muc</button>
    <a href="list.php">Huy</a>
</form>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
