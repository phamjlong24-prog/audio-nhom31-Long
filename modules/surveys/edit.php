<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
if ($id <= 0) {
    set_flash('error', 'Khao sat khong hop le.');
    redirect('list.php');
}

$sql = "SELECT sv.*, t.title AS ticket_title
        FROM satisfaction_surveys sv
        JOIN tickets t ON t.id = sv.ticket_id
        WHERE sv.id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$survey = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$survey) {
    set_flash('error', 'Khong tim thay khao sat.');
    redirect('list.php');
}

$errors = [];
$rating = $survey['rating'];
$feedback = $survey['feedback'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $rating = (int)($_POST['rating'] ?? 0);
    $feedback = trim($_POST['feedback'] ?? '');

    if ($rating < 1 || $rating > 5) {
        $errors[] = 'Danh gia phai tu 1 den 5 sao.';
    }

    if (empty($errors)) {
        $upd = mysqli_prepare($conn, "UPDATE satisfaction_surveys SET rating = ?, feedback = ? WHERE id = ?");
        mysqli_stmt_bind_param($upd, 'isi', $rating, $feedback, $id);

        if (mysqli_stmt_execute($upd)) {
            set_flash('success', 'Cap nhat khao sat thanh cong.');
            redirect('list.php');
        } else {
            $errors[] = 'Loi khi cap nhat: ' . mysqli_error($conn);
        }
        mysqli_stmt_close($upd);
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<h1>Sua khao sat - Ticket: <?= e($survey['ticket_title']) ?></h1>

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
        <label for="rating">Danh gia (1-5 sao) *</label>
        <input type="number" id="rating" name="rating" min="1" max="5"
               value="<?= e($rating) ?>" required>
    </div>

    <div class="form-group">
        <label for="feedback">Nhan xet</label>
        <textarea id="feedback" name="feedback" rows="3"><?= e($feedback) ?></textarea>
    </div>

    <button type="submit" class="btn">Cap nhat</button>
    <a href="list.php">Huy</a>
</form>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
