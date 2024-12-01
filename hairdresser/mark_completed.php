<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); 
require_once 'database.php';

// Проверка авторизации и роли
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 2) {
    $_SESSION['error'] = 'Доступ запрещен.';
    header('Location: login.php');
    exit();
}

// Проверка на наличие ID записи
if (!isset($_GET['appointment_id'])) {
    $_SESSION['error'] = 'Запись не найдена.';
    header('Location: specialist_dashboard.php');
    exit();
}

$appointment_id = intval($_GET['appointment_id']);
$user_id = $_SESSION['user_id'];

// Получение ID статуса "Завершена" из таблицы statuses
$status_name = "Завершена";
$status_query = $conn->prepare("SELECT status_id FROM statuses WHERE status_name = ?");
$status_query->bind_param("s", $status_name);
$status_query->execute();
$status_query->bind_result($status_completed_id);
$status_query->fetch();
$status_query->close();

if (!$status_completed_id) {
    $_SESSION['error'] = 'Статус "Завершена" не найден в базе данных.';
    header('Location: specialist_dashboard.php');
    exit();
}

// Обновление статуса записи
$update_query = $conn->prepare("UPDATE appointments SET status_id = ? WHERE appointment_id = ? AND specialist_id = ?");
$update_query->bind_param("iii", $status_completed_id, $appointment_id, $user_id);

if ($update_query->execute()) {
    $_SESSION['success'] = 'Запись отмечена как завершенная.';
} else {
    $_SESSION['error'] = 'Ошибка при обновлении записи.';
}

$update_query->close();
header('Location: specialist_dashboard.php');
exit();
