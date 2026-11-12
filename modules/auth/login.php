<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';

// Neu da dang nhap roi thi khong can vao trang login nua
if (!empty($_SESSION['user_id'])) {
    redirect('/helpdesk/modules/tickets/list.php');
}

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $errors[] = 'Vui long nhap day du email va mat khau.';
    } else {
        $stmt = mysqli_prepare($conn, "SELECT id, name, password_hash, role FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        // Dung password_verify - KHONG bao gio so sanh chuoi truc tiep voi mat khau tho
        if (!$user || !password_verify($password, $user['password_hash'])) {
            $errors[] = 'Email hoac mat khau khong dung.';
        } else {
            // Dang nhap thanh cong - luu thong tin can thiet vao session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];

            $redirect = $_SESSION['redirect_after_login'] ?? '/helpdesk/modules/tickets/list.php';
            unset($_SESSION['redirect_after_login']);

            set_flash('success', 'Dang nhap thanh cong. Xin chao ' . $user['name'] . '!');
            redirect($redirect);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Dang nhap - Helpdesk CNTT</title>
    <link rel="stylesheet" href="/helpdesk/public/assets/css/style.css">
</head>
<body>
<main class="container" style="max-width:400px;margin-top:60px;">
    <h1>Dang nhap</h1>

    <?php if (!empty($errors)): ?>
        <div class="flash flash-error">
            <ul style="margin:0;padding-left:18px;">
                <?php foreach ($errors as $err): ?>
                    <li><?= e($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= e($email) ?>" required>
        </div>

        <div class="form-group">
            <label for="password">Mat khau</label>
            <input type="password" id="password" name="password" required>
        </div>

        <button type="submit" class="btn">Dang nhap</button>
    </form>

    <p style="margin-top:16px;font-size:13px;color:#666;">
        Tai khoan demo (mat khau chung: <code>123456</code>):<br>
        long@tlu.edu.vn (staff) - binh@tlu.edu.vn (staff)<br>
        mai@tlu.edu.vn (requester) - huong@tlu.edu.vn (manager)
    </p>
</main>
</body>
</html>
