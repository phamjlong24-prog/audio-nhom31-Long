<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth_check.php';
require_role(['staff', 'manager']);

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
if ($id <= 0) {
    set_flash('error', 'Ticket khong hop le.');
    redirect('list.php');
}

$stmt = mysqli_prepare($conn, "SELECT * FROM tickets WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$ticket = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$ticket) {
    set_flash('error', 'Khong tim thay ticket.');
    redirect('list.php');
}

$categories = mysqli_query($conn, "SELECT id, name FROM ticket_categories ORDER BY name");

$errors = [];
$title = $ticket['title'];
$description = $ticket['description'];
$category_id = $ticket['category_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);

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
    }

    if (empty($errors)) {
        $upd = mysqli_prepare($conn, "UPDATE tickets SET title = ?, description = ?, category_id = ? WHERE id = ?");
        mysqli_stmt_bind_param($upd, 'ssii', $title, $description, $category_id, $id);

        if (mysqli_stmt_execute($upd)) {
            set_flash('success', 'Cap nhat ticket thanh cong.');
            redirect("detail.php?id={$id}");
        } else {
            $errors[] = 'Loi khi cap nhat: ' . mysqli_error($conn);
        }
        mysqli_stmt_close($upd);
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<h1>Sua ticket #<?= (int)$id ?></h1>

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
        <label for="title">Tieu de *</label>
        <input type="text" id="title" name="title" maxlength="200"
               value="<?= e($title) ?>" required>
    </div>

    <div class="form-group">
        <label for="category_id">Danh muc *</label>
        <select id="category_id" name="category_id" required>
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

    <button type="submit" class="btn">Cap nhat</button>
    <a href="detail.php?id=<?= (int)$id ?>">Huy</a>
</form>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
