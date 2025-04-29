<?php
require_once '../config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../logowanie/logowanie.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user's reservations with flight details
$query = "SELECT r.*, 
          l.numer_lotu, l.data_start, l.data_koniec, l.status_lotu,
          ls1.nazwa as lotnisko_start, ls1.kod_IATA as kod_start,
          ls2.nazwa as lotnisko_koniec, ls2.kod_IATA as kod_koniec,
          ll.nazwa as linia_lotnicza
          FROM rezerwacje r
          JOIN loty l ON r.id_lotu = l.id
          JOIN lotniska ls1 ON l.id_lotniska_start = ls1.id
          JOIN lotniska ls2 ON l.id_lotniska_koniec = ls2.id
          JOIN linie_lotnicze ll ON l.id_linii_lotniczych = ll.id
          WHERE r.id_pasazera = ?
          ORDER BY r.data_rezerwacji DESC";

$stmt = $db->prepare($query);
$stmt->execute([$user_id]);
$rezerwacje = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle reservation cancellation
if (isset($_GET['action']) && $_GET['action'] == 'cancel' && isset($_GET['id'])) {
    $reservation_id = $_GET['id'];
    
    try {
        // Start transaction
        $db->beginTransaction();
        
        // Get reservation details to update flight seats
        $query_get = "SELECT id_lotu, liczba_miejsc FROM rezerwacje WHERE id = ? AND id_pasazera = ? AND status_rezerwacji != 'anulowana'";
        $stmt_get = $db->prepare($query_get);
        $stmt_get->execute([$reservation_id, $user_id]);
        $reservation = $stmt_get->fetch(PDO::FETCH_ASSOC);
        
        if ($reservation) {
            // Update reservation status
            $query_update_res = "UPDATE rezerwacje SET status_rezerwacji = 'anulowana' WHERE id = ?";
            $stmt_update_res = $db->prepare($query_update_res);
            $stmt_update_res->execute([$reservation_id]);
            
            // Return seats to flight
            $query_update_flight = "UPDATE loty SET dostepne_miejsca = dostepne_miejsca + ? WHERE id = ?";
            $stmt_update_flight = $db->prepare($query_update_flight);
            $stmt_update_flight->execute([$reservation['liczba_miejsc'], $reservation['id_lotu']]);
            
            $db->commit();
            $_SESSION['success'] = "Rezerwacja została anulowana pomyślnie.";
        } else {
            throw new Exception("Nie można anulować tej rezerwacji.");
        }
    } catch (Exception $e) {
        $db->rollBack();
        $_SESSION['error'] = $e->getMessage();
    }
    
    // Redirect to refresh the page
    header('Location: moje_rezerwacje.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MosinAIR - Moje rezerwacje</title>
    <link rel="stylesheet" href="../dodatki/style.css">
    <link rel="icon" type="image/jpeg" sizes="64x64" href="../zdjecia/logo/logo_mosinair.jpeg">
    <style>
        .reservations-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .reservation-card {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            overflow: hidden;
        }
        
        .reservation-header {
            background-color: #f5f5f5;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #eee;
        }
        
        .reservation-header h3 {
            margin: 0;
            color: #333;
        }
        
        .reservation-status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: bold;
        }
        
        .status-potwierdzona {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        
        .status-anulowana {
            background-color: #ffebee;
            color: #c62828;
        }
        
        .status-zrealizowana {
            background-color: #e3f2fd;
            color: #1565c0;
        }
        
        .reservation-body {
            padding: 20px;
        }
        
        .flight-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        
        .flight-route {
            flex: 2;
        }
        
        .flight-details {
            flex: 1;
            text-align: right;
        }
        
        .airport-code {
            font-size: 1.5em;
            font-weight: bold;
            color: #333;
        }
        
        .flight-path {
            display: flex;
            align-items: center;
            margin: 10px 0;
        }
        
        .flight-path hr {
            flex-grow: 1;
            margin: 0 10px;
            border: none;
            border-top: 2px dashed #ccc;
        }
        
        .airport-name {
            font-size: 0.9em;
            color: #666;
        }
        
        .flight-time {
            font-size: 1.1em;
            font-weight: bold;
            margin-top: 5px;
        }
        
        .flight-date {
            font-size: 0.9em;
            color: #666;
        }
        
        .reservation-footer {
            background-color: #f9f9f9;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #eee;
        }
        
        .price-info {
            font-weight: bold;
            color: #333;
        }
        
        .action-buttons a {
            display: inline-block;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            margin-left: 10px;
            font-weight: bold;
        }
        
        .cancel-btn {
            background-color: #f44336;
            color: white;
        }
        
        .cancel-btn:hover {
            background-color: #d32f2f;
        }
        
        .view-btn {
            background-color: #2196f3;
            color: white;
        }
        
        .view-btn:hover {
            background-color: #1976d2;
        }
        
        .no-reservations {
            text-align: center;
            padding: 50px 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .no-reservations h3 {
            color: #666;
            margin-bottom: 20px;
        }
        
        .no-reservations a {
            display: inline-block;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        
        .no-reservations a:hover {
            background-color: #45a049;
        }
        
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .success {
            background-color: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }
        
        .error {
            background-color: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <a href="loginuser.php">
                    <img src="../zdjecia/logo/logo_mosinair.jpeg" alt="MosinAIR Logo" width="50" height="50">
                </a>
            </div>
            <div class="nav-links">
                <a href="loginuser.php">Strona główna</a>
                <a href="profil.php">Mój profil</a>
                <a href="../main/logout.php">Wyloguj się</a>
            </div>
        </nav>
    </header>
    <main>
        <div class="reservations-container">
            <h1>Moje rezerwacje</h1>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="message success">
                    <?php 
                        echo $_SESSION['success']; 
                        unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="message error">
                    <?php 
                        echo $_SESSION['error']; 
                        unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>
            
            <?php if (empty($rezerwacje)): ?>
                <div class="no-reservations">
                    <h3>Nie masz jeszcze żadnych rezerwacji</h3>
                    <p>Zarezerwuj swój pierwszy lot już teraz!</p>
                    <a href="loginuser.php">Przeglądaj dostępne loty</a>
                </div>
            <?php else: ?>
                <?php foreach ($rezerwacje as $rezerwacja): ?>
                    <div class="reservation-card">
                        <div class="reservation-header">
                            <h3>Rezerwacja #<?php echo $rezerwacja['id']; ?></h3>
                            <span class="reservation-status status-<?php echo htmlspecialchars($rezerwacja['status_rezerwacji']); ?>">
                                <?php echo ucfirst(htmlspecialchars($rezerwacja['status_rezerwacji'])); ?>
                            </span>
                        </div>
                        
                        <div class="reservation-body">
                            <div class="flight-info">
                                <div class="flight-route">
                                    <div class="flight-airline"><?php echo htmlspecialchars($rezerwacja['linia_lotnicza']); ?></div>
                                    <div class="flight-number">Lot <?php echo htmlspecialchars($rezerwacja['numer_lotu']); ?></div>
                                    
                                    <div class="airport-code"><?php echo htmlspecialchars($rezerwacja['kod_start']); ?></div>
                                    <div class="airport-name"><?php echo htmlspecialchars($rezerwacja['lotnisko_start']); ?></div>
                                    <div class="flight-time"><?php echo date('H:i', strtotime($rezerwacja['data_start'])); ?></div>
                                    <div class="flight-date"><?php echo date('d.m.Y', strtotime($rezerwacja['data_start'])); ?></div>
                                    
                                    <div class="flight-path">
                                        <hr>
                                        <i>✈</i>
                                        <hr>
                                    </div>
                                    
                                    <div class="airport-code"><?php echo htmlspecialchars($rezerwacja['kod_koniec']); ?></div>
                                    <div class="airport-name"><?php echo htmlspecialchars($rezerwacja['lotnisko_koniec']); ?></div>
                                    <div class="flight-time"><?php echo date('H:i', strtotime($rezerwacja['data_koniec'])); ?></div>
                                    <div class="flight-date"><?php echo date('d.m.Y', strtotime($rezerwacja['data_koniec'])); ?></div>
                                </div>
                                
                                <div class="flight-details">
                                    <p><strong>Status lotu:</strong><br><?php echo htmlspecialchars($rezerwacja['status_lotu']); ?></p>
                                    <p><strong>Numer miejsca:</strong><br><?php echo htmlspecialchars($rezerwacja['numer_miejsca']); ?></p>
                                    <p><strong>Klasa podróży:</strong><br><?php echo htmlspecialchars($rezerwacja['klasa_podrozy']); ?></p>
                                    <p><strong>Data rezerwacji:</strong><br><?php echo date('d.m.Y H:i', strtotime($rezerwacja['data_rezerwacji'])); ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="reservation-footer">
                            <div class="price-info">
                                Cena: <?php echo number_format($rezerwacja['cena'], 2); ?> PLN
                            </div>
                            <div class="action-buttons">
                                <?php if ($rezerwacja['status_rezerwacji'] != 'anulowana' && strtotime($rezerwacja['data_start']) > time()): ?>
                                    <a href="moje_rezerwacje.php?action=cancel&id=<?php echo $rezerwacja['id']; ?>" class="cancel-btn" onclick="return confirm('Czy na pewno chcesz anulować tę rezerwację?')">Anuluj rezerwację</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
    <footer>
        <p>&copy; <?php echo date('Y'); ?> MosinAIR. Wszelkie prawa zastrzeżone.</p>
    </footer>
</body>
</html>
