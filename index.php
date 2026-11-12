<?php
require_once __DIR__ . '/includes/functions.php';

if (!empty($_SESSION['user_id'])) {
    redirect('/helpdesk/modules/tickets/list.php');
} else {
    redirect('/helpdesk/modules/auth/login.php');
}
