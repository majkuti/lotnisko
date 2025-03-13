<?php
require_once '../config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../logowanie/logowanie.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['lot_id'])) {
    $lot_id = $_POST['lot_id'];
    $user_id = $_SESSION['user_id'];
    $data_rezerwacji = date('Y-m-d H:i:s');
    $status = 'aktywna';

    try {
        // Check if flight exists and is active
        $check_flight = $db->prepare("SELECT status_lotu FROM loty WHERE id = ?");
        $check_flight->execute([$lot_id]);
        $flight = $check_flight->fetch();

        if ($flight && $flight['status_lotu'] == 'aktywny') {
            // Check if user already has this reservation
            $check_reservation = $db->prepare("SELECT id FROM rezerwacje WHERE id_lotu = ? AND id_uzytkownika = ?");
            $check_reservation->execute([$lot_id, $user_id]);
            
            if (!$check_reservation->fetch()) {
                $stmt = $db->prepare("INSERT INTO rezerwacje (id_lotu, id_uzytkownika, data_rezerwacji, status) VALUES (?, ?, ?, ?)");
                $stmt->execute([$lot_id, $user_id, $data_rezerwacji, $status]);
                
                $_SESSION['success'] = "Rezerwacja została pomyślnie utworzona!";
            } else {
                $_SESSION['error'] = "Już posiadasz rezerwację na ten lot!";
            }
        } else {
            $_SESSION['error'] = "Ten lot nie jest już dostępny.";
        }
    } catch(PDOException $e) {
        $_SESSION['error'] = "Wystąpił błąd podczas tworzenia rezerwacji.";
    }
    
    header("Location: loginuser.php");
    exit();
}
