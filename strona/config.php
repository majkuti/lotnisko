<?php
$host = 'localhost';
$dbname = 'lotnisko';
$username = 'root';
$password = '';

// Change $user to $username to match the variable defined above
$conn = mysqli_connect($host, $username, $password, $dbname);

try {
    $db = new PDO('mysql:host=localhost;dbname=lotnisko', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Brak polaczenia: " . $e->getMessage();
}
?>
