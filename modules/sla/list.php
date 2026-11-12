<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';

$sql = "SELECT s.id, s.response_hours, s.resolve_hours, c.id AS category_id, c.name AS category_name
        FROM sla_rules s
        JOIN ticket_categories c ON c.id = s.category_id
        ORDER BY c.name ASC";
$result = mysqli_query($conn, $sql);

require_once __DIR__ . '/../../includes/header.php';
?>

<h1>Quy tac SLA (thoi han xu ly)</h1>
<p><a class="btn" href="create.php">+ Them SLA</a></p>

<table>
    <tr>
        <th>#</th>
        <th>Danh muc</th>
        <th>Han phan hoi (gio)</th>
        <th>Han xu ly xong (gio)</th>
        <th>Thao tac</th>
    </tr>
    <?php while ($row = mysqli_fetch_assoc($result)): ?>
    <tr>
        <td><?= e($row['id']) ?></td>
        <td><?= e($row['category_name']) ?></td>
        <td><?= e($row['response_hours']) ?></td>
        <td><?= e($row['resolve_hours']) ?></td>
        <td>
            <a href="edit.php?id=<?= (int)$row['id'] ?>">Sua</a> |
            <form method="POST" action="delete.php" style="display:inline"
                  onsubmit="return confirm('Xoa quy tac SLA nay?');">
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
