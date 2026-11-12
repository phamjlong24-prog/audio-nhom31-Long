<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';

$errors = [];
$category_id = '';
$response_hours = 4;
$resolve_hours = 24;

// Chi lay nhung category CHUA co SLA - dam bao quan he 1-1
$availableSql = "SELECT c.id, c.name
                  FROM ticket_categories c
                  LEFT JOIN sla_rules s ON s.category_id = c.id
                  WHERE s.id IS NULL
                  ORDER BY c.name";
$available = mysqli_query($conn, $availableSql);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $category_id = (int)($_POST['category_id'] ?? 0);
    $response_hours = (int)($_POST['response_hours'] ?? 0);
    $resolve_hours = (int)($_POST['resolve_hours'] ?? 0);

    if ($category_id <= 0) {
        $errors[] = 'Vui long chon danh muc.';
    }
    if ($response_hours <= 0) {
        $errors[] = 'Han phan hoi phai lon hon 0.';
    }
    if ($resolve_hours <= 0) {
        $errors[] = 'Han xu ly xong phai lon hon 0.';
    }
    if ($response_hours > 0 && $resolve_hours > 0 && $resolve_hours < $response_hours) {
        $errors[] = 'Han xu ly xong phai >= han phan hoi.';
    }

    // Kiem tra lai category chua co SLA (phong truong hop submit 2 lan)
    if (empty($errors)) {
        $chk = mysqli_prepare($conn, "SELECT id FROM sla_rules WHERE category_id = ?");
        mysqli_stmt_bind_param($chk, 'i', $category_id);
        mysqli_stmt_execute($chk);
        mysqli_stmt_store_result($chk);
        if (mysqli_stmt_num_rows($chk) > 0) {
            $errors[] = 'Danh muc nay da co SLA roi, khong the tao them (quan he 1-1).';
        }
        mysqli_stmt_close($chk);
    }

    if (empty($errors)) {
        $sql = "INSERT INTO sla_rules (category_id, response_hours, resolve_hours) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'iii', $category_id, $response_hours, $resolve_hours);

        if (mysqli_stmt_execute($stmt)) {
            set_flash('success', 'Them SLA thanh cong.');
            redirect('list.php');
        } else {
            $errors[] = 'Loi khi luu: ' . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<h1>Them quy tac SLA</h1>

<?php if (!empty($errors)): ?>
    <div class="flash flash-error">
        <ul style="margin:0;padding-left:18px;">
            <?php foreach ($errors as $err): ?>
                <li><?= e($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if (mysqli_num_rows($available) === 0 && empty($errors)): ?>
    <p>Tat ca danh muc da co SLA roi. Khong con danh muc nao de tao them.</p>
    <a href="list.php">&larr; Quay lai danh sach</a>
<?php else: ?>
<form method="POST" action="create.php">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

    <div class="form-group">
        <label for="category_id">Danh muc * (chi hien danh muc chua co SLA)</label>
        <select id="category_id" name="category_id" required>
            <option value="">-- Chon danh muc --</option>
            <?php while ($cat = mysqli_fetch_assoc($available)): ?>
                <option value="<?= (int)$cat['id'] ?>"
                    <?= ((int)$category_id === (int)$cat['id']) ? 'selected' : '' ?>>
                    <?= e($cat['name']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>

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

    <button type="submit" class="btn">Luu SLA</button>
    <a href="list.php">Huy</a>
</form>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
