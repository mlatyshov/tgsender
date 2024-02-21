<?php

// разрешение на рассылку
$TGSenderEnabled = false;
$EMSenderEnabled = false;
// тема письма для 
$subjectPDF = 'вам письмо';

if (!file_exists('includes/setup.php'))
    exit('Необходима инициализация. Отсутствует файл includes/setup.php. Переименуйте setup_default.php в setup.php и настройте параметры.');

include('includes/setup.php');


// Создание подключения
$conn = new \mysqli($host, $username, $password, $database);

// Проверка подключения
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

$uploadDir = 'uploads/';
session_start();


/**
 * Количество получателей 
 */
function recipients_get_count(): int {
    global $conn;

    $sql = "SELECT count(username) as cnt FROM users order by user_id desc";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return (int) $row['cnt'];

}



?>