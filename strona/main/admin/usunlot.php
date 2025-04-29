<?php
require_once '../../config.php';
session_start();

// Sprawdź, czy administrator jest zalogowany
if (!isset($_SESSION['admin_id'])) {
    header('Location: adminlogin.php');
    exit();
}

// Sprawdź, czy ID lotu zostało przekazane
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Nie podano identyfikatora lotu.";
    header('Location: adminpage.php');
    exit();
}

$id_lotu = intval($_GET['id']);

try {
    // Najpierw usuń wszystkie rezerwacje związane z tym lotem
    $query_delete_reservations = "DELETE FROM rezerwacje WHERE id_lotu = :id_lotu";
    $stmt_reservations = $db->prepare($query_delete_reservations);
    $stmt_reservations->bindParam(':id_lotu', $id_lotu, PDO::PARAM_INT);
    $stmt_reservations->execute();
    
    // Następnie usuń lot
    $query_delete_flight = "DELETE FROM loty WHERE id = :id_lotu";
    $stmt_flight = $db->prepare($query_delete_flight);
    $stmt_flight->bindParam(':id_lotu', $id_lotu, PDO::PARAM_INT);
    $stmt_flight->execute();
    
    $_SESSION['success'] = "Lot został pomyślnie usunięty.";
} catch (PDOException $e) {
    $_SESSION['error'] = "Wystąpił błąd podczas usuwania lotu: " . $e->getMessage();
}

// Przekieruj z powrotem do strony administratora
header('Location: adminpage.php');
exit();
?>