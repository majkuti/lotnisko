<?php
require_once '../config.php';
session_start();
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../logowanie/logowanie.php');
    exit();
}

// Check if flight ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Nieprawidłowy identyfikator lotu.";
    header('Location: loginuser.php');
    exit();
}

$flight_id = intval($_GET['id']);

// Fetch flight details
$query = "SELECT l.*, 
          ls1.nazwa as lotnisko_start, ls1.miasto as miasto_start, ls1.kod_IATA as kod_start,
          ls2.nazwa as lotnisko_koniec, ls2.miasto as miasto_koniec, ls2.kod_IATA as kod_koniec,
          ll.nazwa as linia_lotnicza, s.model as model_samolotu, s.liczba_miejsc
          FROM loty l
          JOIN lotniska ls1 ON l.id_lotniska_start = ls1.id
          JOIN lotniska ls2 ON l.id_lotniska_koniec = ls2.id
          JOIN linie_lotnicze ll ON l.id_linii_lotniczych = ll.id
          JOIN samoloty s ON l.id_samolotu = s.id
          WHERE l.id = ?";

$stmt = $db->prepare($query);
$stmt->execute([$flight_id]);
$lot = $stmt->fetch(PDO::FETCH_ASSOC);

$departure = new DateTime($lot['data_start']);
$arrival = new DateTime($lot['data_koniec']);
$duration = $departure->diff($arrival);

if (!$lot) {
    $_SESSION['error'] = "Nie znaleziono lotu o podanym identyfikatorze.";
    header('Location: loginuser.php');
    exit();
}

// Process reservation form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate input
        if (!isset($_POST['liczba_miejsc']) || !is_numeric($_POST['liczba_miejsc']) || $_POST['liczba_miejsc'] < 1) {
            throw new Exception("Proszę wybrać prawidłową liczbę miejsc.");
        }
        
        $liczba_miejsc = intval($_POST['liczba_miejsc']);
        
        // Check if enough seats are available
        if ($liczba_miejsc > $lot['dostepne_miejsca']) {
            throw new Exception("Nie ma wystarczającej liczby dostępnych miejsc.");
        }
        
        // Calculate total price
        $cena_calkowita = $lot['cena'] * $liczba_miejsc;
        
        // Start transaction
        $db->beginTransaction();
        
        // Generate seat numbers (simplified version - in reality, you'd have a more complex seat allocation system)
        $numer_miejsca = "A" . rand(1, 30); // Just a placeholder for demonstration
        
        // Insert reservation
        $query_insert = "INSERT INTO rezerwacje (id_lotu, id_pasazera, numer_miejsca, klasa_podrozy, 
        status_rezerwacji, data_rezerwacji, cena) 
        VALUES (?, ?, ?, ?, ?, NOW(), ?)";

        $stmt_insert = $db->prepare($query_insert);
        $stmt_insert->execute([
        $flight_id,
        $_SESSION['user_id'],
        $numer_miejsca,
        'ekonomiczna', // Default class, could be made selectable in the form
        'potwierdzona',
        $cena_calkowita
        ]);
        
        // Update available seats in flight
        $query_update = "UPDATE loty SET dostepne_miejsca = dostepne_miejsca - ? WHERE id = ?";
        $stmt_update = $db->prepare($query_update);
        $stmt_update->execute([$liczba_miejsc, $flight_id]);
        
        // Commit transaction
        $db->commit();
        
        $_SESSION['success'] = "Rezerwacja została pomyślnie utworzona!";
        header('Location: moje_rezerwacje.php');
        exit();
        
    } catch (Exception $e) {
        // Rollback transaction on error
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        $_SESSION['error'] = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MosinAIR - Rezerwacja lotu</title>
    <link rel="stylesheet" href="../dodatki/style.css">
    <link rel="icon" type="image/jpeg" sizes="64x64" href="../zdjecia/logo/logo_mosinair.jpeg">
    <style>
        .reservation-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .flight-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .flight-route {
            flex: 2;
        }
        
        .flight-details {
            flex: 1;
            text-align: right;
        }
        
        .airport-code {
            font-size: 1.8em;
            font-weight: bold;
            color: #333;
        }
        
        .flight-path {
            display: flex;
            align-items: center;
            margin: 15px 0;
        }
        
        .flight-path hr {
            flex-grow: 1;
            margin: 0 10px;
            border: none;
            border-top: 2px dashed #ccc;
        }
        
        .flight-path i {
            color: #666;
        }
        
        .airport-name {
            font-size: 0.9em;
            color: #666;
        }
        
        .flight-time {
            font-size: 1.2em;
            font-weight: bold;
            margin-top: 5px;
        }
        
        .flight-date {
            font-size: 0.9em;
            color: #666;
        }
        
        .reservation-form {
            margin-top: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .form-group select, .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
        }
        
        .price-summary {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .total-price {
            font-size: 1.5em;
            font-weight: bold;
            color: #333;
            margin-top: 10px;
        }
        
        .submit-btn {
            background-color: #4CAF50;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            font-weight: bold;
            width: 100%;
        }
        
        .submit-btn:hover {
            background-color: #45a049;
        }
        
        .error-message {
            color: #f44336;
            margin-bottom: 15px;
            padding: 10px;
            background-color: #ffebee;
            border-radius: 5px;
        }
        
        .success-message {
            color: #4CAF50;
            margin-bottom: 15px;
            padding: 10px;
            background-color: #e8f5e9;
            border-radius: 5px;
        }
        
        .flight-airline {
            font-size: 1.2em;
            margin-bottom: 10px;
            color: #333;
        }
        
        .flight-number {
            font-size: 1em;
            color: #666;
            margin-bottom: 20px;
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
                <a href="profil.php">Mój profil</a>
                <a href="moje_rezerwacje.php">Moje rezerwacje</a>
                <a href="../main/logout.php">Wyloguj się</a>
            </div>
        </nav>
    </header>
    <main>
        <div class="reservation-container">
            <h1>Rezerwacja lotu</h1>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="error-message">
                    <?php 
                        echo $_SESSION['error']; 
                        unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="success-message">
                    <?php 
                        echo $_SESSION['success']; 
                        unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>
            
            <div class="flight-info">
                <div class="flight-route">
                    <div class="flight-airline"><?php echo htmlspecialchars($lot['linia_lotnicza']); ?></div>
                    <div class="flight-number">Lot <?php echo htmlspecialchars($lot['numer_lotu']); ?></div>
                    
                    <div class="airport-code"><?php echo htmlspecialchars($lot['kod_start']); ?></div>
                    <div class="airport-name"><?php echo htmlspecialchars($lot['lotnisko_start']); ?>, <?php echo htmlspecialchars($lot['miasto_start']); ?></div>
                    <div class="flight-time"><?php echo date('H:i', strtotime($lot['data_start'])); ?></div>
                    <div class="flight-date"><?php echo date('d.m.Y', strtotime($lot['data_start'])); ?></div>
                    
                    <div class="flight-path">
                        <hr>
                        <i>✈</i>
                        <hr>
                    </div>
                    
                    <div class="airport-code"><?php echo htmlspecialchars($lot['kod_koniec']); ?></div>
                    <div class="airport-name"><?php echo htmlspecialchars($lot['lotnisko_koniec']); ?>, <?php echo htmlspecialchars($lot['miasto_koniec']); ?></div>
                    <div class="flight-time"><?php echo date('H:i', strtotime($lot['data_koniec'])); ?></div>
                    <div class="flight-date"><?php echo date('d.m.Y', strtotime($lot['data_koniec'])); ?></div>
                </div>
                
                <div class="flight-details">
                    <p><strong>Czas lotu:</strong><br>
                    <?php 
                        if ($duration->d > 0) echo $duration->d . 'd ';
                        echo $duration->h . 'h ' . $duration->i . 'min';
                    ?></p>
                    <p><strong>Samolot:</strong><br><?php echo htmlspecialchars($lot['model_samolotu']); ?></p>
                    <p><strong>Status:</strong><br><?php echo htmlspecialchars($lot['status_lotu']); ?></p>
                    <p><strong>Dostępne miejsca:</strong><br><?php echo htmlspecialchars($lot['dostepne_miejsca']); ?></p>
                    <p><strong>Cena za osobę:</strong><br><?php echo number_format($lot['cena'], 2); ?> PLN</p>
                </div>
            </div>
            
            <form action="rezerwacja.php?id=<?php echo $flight_id; ?>" method="POST" class="reservation-form">
                <div class="form-group">
                    <label for="liczba_miejsc">Liczba miejsc:</label>
                    <select id="liczba_miejsc" name="liczba_miejsc" required onchange="updateTotalPrice()">
                        <?php for ($i = 1; $i <= min(10, $lot['dostepne_miejsca']); $i++): ?>
                            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div class="price-summary">
                    <p>Cena za osobę: <span id="price-per-person"><?php echo number_format($lot['cena'], 2); ?></span> PLN</p>
                    <p>Liczba osób: <span id="number-of-people">1</span></p>
                    <div class="total-price">Razem: <span id="total-price"><?php echo number_format($lot['cena'], 2); ?></span> PLN</div>
                </div>
                
                <button type="submit" class="submit-btn">Potwierdź rezerwację</button>
            </form>
        </div>
    </main>
<?php
    include('../main/footer/footer.php')
?>
    
    <script>
        function updateTotalPrice() {
            const numberOfPeople = document.getElementById('liczba_miejsc').value;
            const pricePerPerson = <?php echo $lot['cena']; ?>;
            const totalPrice = numberOfPeople * pricePerPerson;
            
            document.getElementById('number-of-people').textContent = numberOfPeople;
            document.getElementById('total-price').textContent = totalPrice.toFixed(2);
        }
    </script>
</body>
</html>
