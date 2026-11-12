<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth_check.php';
require_role(['staff', 'manager']);

$sql = "SELECT sv.id, sv.rating, sv.feedback, sv.created_at,
               t.id AS ticket_id, t.title AS ticket_title
        FROM satisfaction_surveys sv
        JOIN tickets t ON t.id = sv.ticket_id
        ORDER BY sv.created_at DESC";
$result = mysqli_query($conn, $sql);

require_once __DIR__ . '/../../includes/header.php';
?>

<h1>Khao sat su hai long</h1>
<p><a class="btn" href="create.php">+ Them khao sat</a></p>

<table>
    <tr>
        <th>#</th>
        <th>Ticket</th>
        <th>Danh gia</th>
        <th>Nhan xet</th>
        <th>Ngay</th>
        <th>Thao tac</th>
    </tr>
    <?php while ($row = mysqli_fetch_assoc($result)): ?>
    <tr>
        <td><?= e($row['id']) ?></td>
        <td>#<?= e($row['ticket_id']) ?> - <?= e($row['ticket_title']) ?></td>
        <td><?= str_repeat('*', (int)$row['rating']) ?> (<?= e($row['rating']) ?>/5)</td>
        <td><?= e($row['feedback']) ?></td>
        <td><?= e($row['created_at']) ?></td>
        <td>
            <a href="edit.php?id=<?= (int)$row['id'] ?>">Sua</a> |
            <form method="POST" action="delete.php" style="display:inline"
                  onsubmit="return confirm('Xoa khao sat nay?');">
                <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <button type="submit" class="btn btn-danger"
                        style="padding:2px 8px;font-size:12px;">Xoa</button>
            </form>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
