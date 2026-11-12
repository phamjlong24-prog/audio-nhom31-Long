<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth_check.php';
require_role(['staff', 'manager']);

$errors = [];
$ticket_id = '';
$rating = 5;
$feedback = '';

// RANG BUOC NGHIEP VU QUAN TRONG:
// Chi lay nhung ticket da 'closed' VA chua co survey nao
// (khong the ep bang SQL constraint don thuan, phai loc o day)
$availableSql = "SELECT t.id, t.title
                  FROM tickets t
                  LEFT JOIN satisfaction_surveys sv ON sv.ticket_id = t.id
                  WHERE t.status = 'closed' AND sv.id IS NULL
                  ORDER BY t.id DESC";
$available = mysqli_query($conn, $availableSql);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $ticket_id = (int)($_POST['ticket_id'] ?? 0);
    $rating = (int)($_POST['rating'] ?? 0);
    $feedback = trim($_POST['feedback'] ?? '');

    if ($ticket_id <= 0) {
        $errors[] = 'Vui long chon ticket.';
    }
    if ($rating < 1 || $rating > 5) {
        $errors[] = 'Danh gia phai tu 1 den 5 sao.';
    }

    // Kiem tra lai NGAY TRUOC KHI INSERT: ticket phai closed va chua co survey
    if (empty($errors)) {
        $chk = mysqli_prepare($conn, "SELECT status FROM tickets WHERE id = ?");
        mysqli_stmt_bind_param($chk, 'i', $ticket_id);
        mysqli_stmt_execute($chk);
        $chkResult = mysqli_stmt_get_result($chk);
        $ticket = mysqli_fetch_assoc($chkResult);
        mysqli_stmt_close($chk);

        if (!$ticket) {
            $errors[] = 'Ticket khong ton tai.';
        } elseif ($ticket['status'] !== 'closed') {
            $errors[] = 'Chi duoc tao khao sat khi ticket da o trang thai closed.';
        }
    }

    if (empty($errors)) {
        $chk2 = mysqli_prepare($conn, "SELECT id FROM satisfaction_surveys WHERE ticket_id = ?");
        mysqli_stmt_bind_param($chk2, 'i', $ticket_id);
        mysqli_stmt_execute($chk2);
        mysqli_stmt_store_result($chk2);
        if (mysqli_stmt_num_rows($chk2) > 0) {
            $errors[] = 'Ticket nay da co khao sat roi.';
        }
        mysqli_stmt_close($chk2);
    }

    if (empty($errors)) {
        $sql = "INSERT INTO satisfaction_surveys (ticket_id, rating, feedback) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'iis', $ticket_id, $rating, $feedback);

        if (mysqli_stmt_execute($stmt)) {
            set_flash('success', 'Them khao sat thanh cong.');
            redirect('list.php');
        } else {
            $errors[] = 'Loi khi luu: ' . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<h1>Them khao sat su hai long</h1>

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
    <p>Hien khong co ticket nao dang 'closed' ma chua duoc khao sat.</p>
    <a href="list.php">&larr; Quay lai danh sach</a>
<?php else: ?>
<form method="POST" action="create.php">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

    <div class="form-group">
        <label for="ticket_id">Ticket * (chi hien ticket da closed, chua khao sat)</label>
        <select id="ticket_id" name="ticket_id" required>
            <option value="">-- Chon ticket --</option>
            <?php while ($t = mysqli_fetch_assoc($available)): ?>
                <option value="<?= (int)$t['id'] ?>"
                    <?= ((int)$ticket_id === (int)$t['id']) ? 'selected' : '' ?>>
                    #<?= (int)$t['id'] ?> - <?= e($t['title']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="rating">Danh gia (1-5 sao) *</label>
        <input type="number" id="rating" name="rating" min="1" max="5"
               value="<?= e($rating) ?>" required>
    </div>

    <div class="form-group">
        <label for="feedback">Nhan xet</label>
        <textarea id="feedback" name="feedback" rows="3"><?= e($feedback) ?></textarea>
    </div>

    <button type="submit" class="btn">Luu khao sat</button>
    <a href="list.php">Huy</a>
</form>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
