<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';

$errors = [];
$title = '';
$description = '';
$category_id = '';

// TAM THOI: chua co Auth that, gia dinh requester dang login la id = 3
// Khi lam B1 (Auth) se thay bang $_SESSION['user_id']
$current_user_id = 3;

// Lay danh sach category cho dropdown
$categories = mysqli_query($conn, "SELECT id, name FROM ticket_categories ORDER BY name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);

    // ---- Validation backend ----
    if ($title === '') {
        $errors[] = 'Tieu de khong duoc de trong.';
    } elseif (mb_strlen($title) > 200) {
        $errors[] = 'Tieu de toi da 200 ky tu.';
    }

    if ($description === '') {
        $errors[] = 'Mo ta khong duoc de trong.';
    }

    if ($category_id <= 0) {
        $errors[] = 'Vui long chon danh muc.';
    } else {
        // Kiem tra category_id ton tai that (chong FK loi)
        $chk = mysqli_prepare($conn, "SELECT id FROM ticket_categories WHERE id = ?");
        mysqli_stmt_bind_param($chk, 'i', $category_id);
        mysqli_stmt_execute($chk);
        mysqli_stmt_store_result($chk);
        if (mysqli_stmt_num_rows($chk) === 0) {
            $errors[] = 'Danh muc khong hop le.';
        }
        mysqli_stmt_close($chk);
    }

    if (empty($errors)) {
        mysqli_begin_transaction($conn);
        try {
            $sql = "INSERT INTO tickets (requester_id, category_id, title, description, status)
                    VALUES (?, ?, ?, ?, 'open')";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, 'iiss', $current_user_id, $category_id, $title, $description);
            mysqli_stmt_execute($stmt);
            $ticket_id = mysqli_insert_id($conn);
            mysqli_stmt_close($stmt);

            // Ghi log trang thai dau tien (bang bat bien, chi INSERT)
            $logSql = "INSERT INTO ticket_status_logs (ticket_id, old_status, new_status, changed_by)
                       VALUES (?, NULL, 'open', ?)";
            $logStmt = mysqli_prepare($conn, $logSql);
            mysqli_stmt_bind_param($logStmt, 'ii', $ticket_id, $current_user_id);
            mysqli_stmt_execute($logStmt);
            mysqli_stmt_close($logStmt);

            mysqli_commit($conn);
            set_flash('success', 'Tao ticket thanh cong.');
            redirect('list.php');
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $errors[] = 'Loi khi luu ticket: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<h1>Tao ticket moi</h1>

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
        <label for="title">Tieu de *</label>
        <input type="text" id="title" name="title" maxlength="200"
               value="<?= e($title) ?>" required>
    </div>

    <div class="form-group">
        <label for="category_id">Danh muc *</label>
        <select id="category_id" name="category_id" required>
            <option value="">-- Chon danh muc --</option>
            <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
                <option value="<?= (int)$cat['id'] ?>"
                    <?= ((int)$category_id === (int)$cat['id']) ? 'selected' : '' ?>>
                    <?= e($cat['name']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="description">Mo ta chi tiet *</label>
        <textarea id="description" name="description" rows="4" required><?= e($description) ?></textarea>
    </div>

    <button type="submit" class="btn">Gui ticket</button>
    <a href="list.php">Huy</a>
</form>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
