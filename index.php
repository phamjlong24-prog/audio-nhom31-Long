<?php
require_once __DIR__ . '/includes/functions.php';

if (!empty($_SESSION['user_id'])) {
    redirect(app_url('modules/tickets/list.php'));
} else {
    redirect(app_url('modules/auth/login.php'));
}
