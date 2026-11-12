<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth_check.php';
require_role(['staff', 'manager']);

// Lay danh sach category kem so ticket dang su dung (JOIN don gian)
$sql = "SELECT c.id, c.name, c.description,
               COUNT(t.id) AS total_tickets
        FROM ticket_categories c
        LEFT JOIN tickets t ON t.category_id = c.id
        GROUP BY c.id, c.name, c.description
        ORDER BY c.id ASC";

$result = mysqli_query($conn, $sql);

require_once __DIR__ . '/../../includes/header.php';
?>

<h1>Danh muc yeu cau (Ticket categories)</h1>
<p><a class="btn" href="create.php">+ Them danh muc</a></p>

<table>
    <tr>
        <th>#</th>
        <th>Ten danh muc</th>
        <th>Mo ta</th>
        <th>So ticket</th>
        <th>Thao tac</th>
    </tr>
    <?php while ($row = mysqli_fetch_assoc($result)): ?>
    <tr>
        <td><?= e($row['id']) ?></td>
        <td><?= e($row['name']) ?></td>
        <td><?= e($row['description']) ?></td>
        <td><?= e($row['total_tickets']) ?></td>
        <td>
            <a href="edit.php?id=<?= (int)$row['id'] ?>">Sua</a> |
            <form method="POST" action="delete.php" style="display:inline"
                  onsubmit="return confirm('Xoa danh muc nay?');">
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
