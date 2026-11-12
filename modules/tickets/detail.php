<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';

// TAM THOI: chua co Auth that, gia dinh user dang thao tac la id = 1 (staff)
// Khi lam xong Auth (B1) se thay bang $_SESSION['user_id']
$current_user_id = 1;

$ticket_id = (int)($_GET['id'] ?? 0);
if ($ticket_id <= 0) {
    set_flash('error', 'Ticket khong hop le.');
    redirect('list.php');
}

// ---- Lay thong tin ticket, hien TEN thay vi ID ----
$sql = "SELECT t.*, c.name AS category_name, u.name AS requester_name, u.email AS requester_email
        FROM tickets t
        JOIN ticket_categories c ON c.id = t.category_id
        JOIN users u ON u.id = t.requester_id
        WHERE t.id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $ticket_id);
mysqli_stmt_execute($stmt);
$ticket = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$ticket) {
    set_flash('error', 'Khong tim thay ticket.');
    redirect('list.php');
}

// ---- Assignment dang active (neu co) ----
$asgSql = "SELECT a.id, a.assigned_at, u.name AS staff_name
           FROM ticket_assignments a
           JOIN users u ON u.id = a.staff_id
           WHERE a.ticket_id = ? AND a.is_active = 1
           LIMIT 1";
$asgStmt = mysqli_prepare($conn, $asgSql);
mysqli_stmt_bind_param($asgStmt, 'i', $ticket_id);
mysqli_stmt_execute($asgStmt);
$activeAssignment = mysqli_fetch_assoc(mysqli_stmt_get_result($asgStmt));
mysqli_stmt_close($asgStmt);

// ---- Danh sach staff de chon phan cong ----
$staffList = mysqli_query($conn, "SELECT id, name FROM users WHERE role = 'staff' ORDER BY name");

// ---- Danh sach comment, hien TEN nguoi viet ----
$cmtSql = "SELECT cm.content, cm.created_at, u.name AS author_name
           FROM ticket_comments cm
           JOIN users u ON u.id = cm.user_id
           WHERE cm.ticket_id = ?
           ORDER BY cm.created_at ASC";
$cmtStmt = mysqli_prepare($conn, $cmtSql);
mysqli_stmt_bind_param($cmtStmt, 'i', $ticket_id);
mysqli_stmt_execute($cmtStmt);
$comments = mysqli_stmt_get_result($cmtStmt);

// ---- Lich su trang thai (bang bat bien, chi xem) ----
$logSql = "SELECT l.old_status, l.new_status, l.changed_at, u.name AS changed_by_name
           FROM ticket_status_logs l
           JOIN users u ON u.id = l.changed_by
           WHERE l.ticket_id = ?
           ORDER BY l.changed_at ASC";
$logStmt = mysqli_prepare($conn, $logSql);
mysqli_stmt_bind_param($logStmt, 'i', $ticket_id);
mysqli_stmt_execute($logStmt);
$logs = mysqli_stmt_get_result($logStmt);

$statusOptions = ['open', 'assigned', 'in_progress', 'resolved', 'closed', 'cancelled'];

require_once __DIR__ . '/../../includes/header.php';
?>

<h1>Ticket #<?= (int)$ticket['id'] ?>: <?= e($ticket['title']) ?></h1>

<table>
    <tr><th style="width:180px;">Danh muc</th><td><?= e($ticket['category_name']) ?></td></tr>
    <tr><th>Nguoi gui</th><td><?= e($ticket['requester_name']) ?> (<?= e($ticket['requester_email']) ?>)</td></tr>
    <tr><th>Mo ta</th><td><?= nl2br(e($ticket['description'])) ?></td></tr>
    <tr><th>Trang thai hien tai</th>
        <td><span class="status status-<?= e($ticket['status']) ?>"><?= e($ticket['status']) ?></span></td></tr>
    <tr><th>Ngay tao</th><td><?= e($ticket['created_at']) ?></td></tr>
    <tr><th>Nguoi phu trach</th>
        <td><?= $activeAssignment ? e($activeAssignment['staff_name']) : '(chua phan cong)' ?></td></tr>
</table>

<p style="margin-top:12px;">
    <a href="edit.php?id=<?= (int)$ticket['id'] ?>">Sua thong tin ticket</a> |
    <form method="POST" action="delete.php" style="display:inline"
          onsubmit="return confirm('Xoa ticket nay? Chi cho phep neu con moi tao, chua co hoat dong.');">
        <input type="hidden" name="id" value="<?= (int)$ticket['id'] ?>">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <button type="submit" class="btn btn-danger" style="padding:2px 8px;font-size:12px;">Xoa ticket</button>
    </form>
</p>

<hr>

<h2>Doi trang thai</h2>
<form method="POST" action="/helpdesk/modules/tickets/update_status.php">
    <input type="hidden" name="ticket_id" value="<?= (int)$ticket['id'] ?>">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    <div class="form-group" style="max-width:300px;">
        <select name="new_status">
            <?php foreach ($statusOptions as $opt): ?>
                <option value="<?= e($opt) ?>" <?= $opt === $ticket['status'] ? 'selected' : '' ?>>
                    <?= e($opt) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit" class="btn">Cap nhat trang thai</button>
</form>

<hr>

<h2>Phan cong nhan vien xu ly</h2>
<form method="POST" action="/helpdesk/modules/assignments/assign.php">
    <input type="hidden" name="ticket_id" value="<?= (int)$ticket['id'] ?>">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    <div class="form-group" style="max-width:300px;">
        <select name="staff_id" required>
            <option value="">-- Chon nhan vien --</option>
            <?php while ($s = mysqli_fetch_assoc($staffList)): ?>
                <option value="<?= (int)$s['id'] ?>"><?= e($s['name']) ?></option>
            <?php endwhile; ?>
        </select>
    </div>
    <button type="submit" class="btn">
        <?= $activeAssignment ? 'Phan cong lai' : 'Phan cong' ?>
    </button>
</form>

<hr>

<h2>Lich su trang thai (khong the sua/xoa)</h2>
<table>
    <tr><th>Tu</th><th>Sang</th><th>Boi</th><th>Luc</th></tr>
    <?php while ($log = mysqli_fetch_assoc($logs)): ?>
    <tr>
        <td><?= e($log['old_status'] ?? '(moi tao)') ?></td>
        <td><?= e($log['new_status']) ?></td>
        <td><?= e($log['changed_by_name']) ?></td>
        <td><?= e($log['changed_at']) ?></td>
    </tr>
    <?php endwhile; ?>
</table>

<hr>

<h2>Trao doi / Binh luan</h2>
<?php while ($cmt = mysqli_fetch_assoc($comments)): ?>
    <div style="border:1px solid #ddd;background:#fff;padding:10px;margin-bottom:8px;border-radius:4px;">
        <strong><?= e($cmt['author_name']) ?></strong>
        <span style="color:#888;font-size:12px;"> - <?= e($cmt['created_at']) ?></span>
        <p style="margin:6px 0 0;"><?= nl2br(e($cmt['content'])) ?></p>
    </div>
<?php endwhile; ?>

<form method="POST" action="/helpdesk/modules/comments/add.php">
    <input type="hidden" name="ticket_id" value="<?= (int)$ticket['id'] ?>">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    <div class="form-group">
        <textarea name="content" rows="3" placeholder="Nhap noi dung trao doi..." required></textarea>
    </div>
    <button type="submit" class="btn">Gui binh luan</button>
</form>

<p style="margin-top:16px;"><a href="list.php">&larr; Quay lai danh sach ticket</a></p>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
