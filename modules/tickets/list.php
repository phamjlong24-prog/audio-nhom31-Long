<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth_check.php';
require_login();

// JOIN de hien thi TEN thay vi ID (yeu cau bat buoc trong barem A5)
$sql = "SELECT t.id, t.title, t.status, t.created_at,
               c.name AS category_name,
               u.name AS requester_name
        FROM tickets t
        JOIN ticket_categories c ON c.id = t.category_id
        JOIN users u ON u.id = t.requester_id
        ORDER BY t.created_at DESC";

$result = mysqli_query($conn, $sql);

require_once __DIR__ . '/../../includes/header.php';
?>

<h1>Danh sach ticket</h1>
<p><a class="btn" href="create.php">+ Tao ticket moi</a></p>

<table>
    <tr>
        <th>#</th>
        <th>Tieu de</th>
        <th>Danh muc</th>
        <th>Nguoi gui</th>
        <th>Trang thai</th>
        <th>Ngay tao</th>
        <th>Thao tac</th>
    </tr>
    <?php while ($row = mysqli_fetch_assoc($result)): ?>
    <tr>
        <td><?= e($row['id']) ?></td>
        <td><?= e($row['title']) ?></td>
        <td><?= e($row['category_name']) ?></td>
        <td><?= e($row['requester_name']) ?></td>
        <td><span class="status status-<?= e($row['status']) ?>"><?= e($row['status']) ?></span></td>
        <td><?= e($row['created_at']) ?></td>
        <td><a href="detail.php?id=<?= (int)$row['id'] ?>">Xem chi tiet</a></td>
    </tr>
    <?php endwhile; ?>
</table>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
