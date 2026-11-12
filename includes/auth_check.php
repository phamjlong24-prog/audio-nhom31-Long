<?php
// Middleware dung chung cho ca he thong.
// Goi require_login() dau moi trang can dang nhap.
// Goi require_role(['staff','manager']) o trang can gioi han theo vai tro.

require_once __DIR__ . '/functions.php'; // da co session_start() ben trong

function current_user() {
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    return [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'],
        'role' => $_SESSION['user_role'],
    ];
}

function require_login() {
    if (empty($_SESSION['user_id'])) {
        set_flash('error', 'Vui long dang nhap de tiep tuc.');
        // Luu lai trang dinh vao de sau khi login quay lai
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        redirect('/helpdesk/modules/auth/login.php');
    }
}

// Chan neu user KHONG thuoc mot trong cac role duoc phep.
// PHAI goi sau require_login().
function require_role(array $allowedRoles) {
    require_login();
    if (!in_array($_SESSION['user_role'], $allowedRoles, true)) {
        set_flash('error', 'Ban khong co quyen truy cap chuc nang nay.');
        redirect('/helpdesk/modules/tickets/list.php');
    }
}
