<?php
// Ket noi CSDL dung chung cho toan bo project
// Level 1: bat buoc dung mysqli_prepare + bind_param, KHONG noi chuoi SQL

$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'helpdesk';

$conn = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if (!$conn) {
    die('Loi ket noi CSDL: ' . mysqli_connect_error());
}

mysqli_set_charset($conn, 'utf8mb4');
