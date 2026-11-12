<?php
session_start();

// Escape output chong XSS - dung khi in du lieu ra HTML
function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

// Redirect va dung script ngay
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

// Tu dong lay duong dan goc cua project trong document root cua XAMPP.
// Vi du: C:/xampp/htdocs/helpdesk/CSE485-Nhom16 -> /helpdesk/CSE485-Nhom16
function app_url($path = '') {
    static $baseUrl = null;

    if ($baseUrl === null) {
        $documentRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: '');
        $projectRoot = str_replace('\\', '/', realpath(__DIR__ . '/..') ?: '');
        $baseUrl = ($documentRoot !== '' && str_starts_with($projectRoot, $documentRoot))
            ? substr($projectRoot, strlen($documentRoot))
            : '';
    }

    return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
}

// Flash message - luu 1 lan, hien thi xong thi xoa
function set_flash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// CSRF token don gian cho Level 1
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_check() {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        die('CSRF token khong hop le.');
    }
}
