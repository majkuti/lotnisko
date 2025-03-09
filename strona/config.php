<?php
$host = 'localhost';
$dbname = 'lotnisko';
$username = 'root';
$password = '';

try {
    $db = new PDO('mysql:host=localhost;dbname=lotnisko', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Brak polaczenia: " . $e->getMessage();
}
?>