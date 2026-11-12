<?php
require_once __DIR__ . '/../../includes/functions.php';

// Xoa toan bo session - dang xuat that su, khong chi an giao dien
$_SESSION = [];
session_destroy();

session_start();
set_flash('success', 'Ban da dang xuat.');
redirect('/helpdesk/modules/auth/login.php');
