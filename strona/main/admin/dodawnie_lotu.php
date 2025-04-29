<?php
require_once '../../config.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../admin/adminpage.php');
    exit();
}

// Fetch airlines from database
$query_airlines = "SELECT * FROM linie_lotnicze";
$stmt_airlines = $db->query($query_airlines);
$airlines = $stmt_airlines->fetchAll();

// Fetch airports from database
$query_airports = "SELECT * FROM lotniska";
$stmt_airports = $db->query($query_airports);
$airports = $stmt_airports->fetchAll();

// Fetch aircraft from database
$query_aircraft = "SELECT * FROM samoloty";
$stmt_aircraft = $db->query($query_aircraft);
$aircraft = $stmt_aircraft->fetchAll();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate and sanitize input
        $id_samolotu = filter_input(INPUT_POST, 'id_samolotu', FILTER_VALIDATE_INT);
        $id_lotniska_start = filter_input(INPUT_POST, 'id_lotniska_start', FILTER_VALIDATE_INT);
        $id_lotniska_koniec = filter_input(INPUT_POST, 'id_lotniska_koniec', FILTER_VALIDATE_INT);
        $id_linii_lotniczych = filter_input(INPUT_POST, 'id_linii_lotniczych', FILTER_VALIDATE_INT);
        $numer_lotu = filter_input(INPUT_POST, 'numer_lotu', FILTER_SANITIZE_STRING);
        $data_start = filter_input(INPUT_POST, 'data_start', FILTER_SANITIZE_STRING);
        $data_koniec = filter_input(INPUT_POST, 'data_koniec', FILTER_SANITIZE_STRING);
        $status_lotu = filter_input(INPUT_POST, 'status_lotu', FILTER_SANITIZE_STRING);
        
        // Nowe pola: cena i dostępne miejsca
        $cena = filter_input(INPUT_POST, 'cena', FILTER_VALIDATE_FLOAT);
        $dostepne_miejsca = filter_input(INPUT_POST, 'dostepne_miejsca', FILTER_VALIDATE_INT);
        
        // Validate required fields
        if (!$id_samolotu || !$id_lotniska_start || !$id_lotniska_koniec || 
            !$id_linii_lotniczych || !$numer_lotu || !$data_start || !$data_koniec || !$status_lotu ||
            !$cena || !$dostepne_miejsca) {
            throw new Exception("Wszystkie pola są wymagane.");
        }
        
        // Check if departure and arrival airports are different
        if ($id_lotniska_start === $id_lotniska_koniec) {
            throw new Exception("Lotnisko wylotu i przylotu nie może być takie samo.");
        }
        
        // Check if departure time is before arrival time
        $start_datetime = new DateTime($data_start);
        $end_datetime = new DateTime($data_koniec);
        
        if ($start_datetime >= $end_datetime) {
            throw new Exception("Data wylotu musi być wcześniejsza niż data przylotu.");
        }
        
        // Validate price and seats
        if ($cena <= 0) {
            throw new Exception("Cena musi być większa od zera.");
        }
        
        if ($dostepne_miejsca <= 0) {
            throw new Exception("Liczba dostępnych miejsc musi być większa od zera.");
        }
        
        // Insert flight into database
        $query = "INSERT INTO loty (id_samolotu, id_lotniska_start, id_lotniska_koniec, id_linii_lotniczych, 
                 numer_lotu, data_start, data_koniec, status_lotu, cena, dostepne_miejsca) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $db->prepare($query);
        $stmt->execute([
            $id_samolotu, 
            $id_lotniska_start, 
            $id_lotniska_koniec, 
            $id_linii_lotniczych, 
            $numer_lotu, 
            $data_start, 
            $data_koniec, 
            $status_lotu,
            $cena,
            $dostepne_miejsca
        ]);
        
        $_SESSION['success'] = "Lot został pomyślnie dodany.";
        header('Location: ..//admin/adminpage.php');;
        exit();
        
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MosinAIR - Dodawanie Lotu</title>
    <link rel="stylesheet" href="../../dodatki/style.css">
    <link rel="stylesheet" href="../admin/admin.css">
    <link rel="icon" type="image/jpeg" sizes="64x64" href="../../zdjecia/logo/logo_mosinair.jpeg">
    <style>
        .form-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .form-group input, .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .form-actions {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
        }
        
        .btn {
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        
        .btn-primary {
            background-color: #4CAF50;
            color: white;
        }
        
        .btn-secondary {
            background-color: #f44336;
            color: white;
        }
        
        .error-message {
            color: #f44336;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <a href="adminpage.php">
                    <img src="../../zdjecia/logo/logo_mosinair.jpeg" alt="MosinAIR Logo" width="50" height="50">
                </a>
            </div>
            <div class="nav-links">
                <a href="../admin/adminpage.php">Zarządzaj Lotami</a>
                <a href="../admin/zarzadzaj_uzytkownikami.php">Zarządzaj Użytkownikami</a>
                <a href="manage_airports.php">Zarządzaj Lotniskami</a>
                <a href="manage_reservations.php">Zarządzaj Rezerwacjami</a>
                <a href="../logout.php">Wyloguj się</a>
            </div>
        </nav>
    </header>
    <main>
        <div class="form-container">
            <h1>Dodawanie Nowego Lotu</h1>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="error-message">
                    <?php 
                        echo $_SESSION['error']; 
                        unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>
            
            <form action="dodawnie_lotu.php" method="POST">
                <div class="form-group">
                    <label for="numer_lotu">Numer Lotu:</label>
                    <input type="text" id="numer_lotu" name="numer_lotu" required 
                           placeholder="np. MA101" pattern="[A-Z0-9]+" maxlength="10">
                </div>
                
                <div class="form-group">
                    <label for="id_linii_lotniczych">Linia Lotnicza:</label>
                    <select id="id_linii_lotniczych" name="id_linii_lotniczych" required>
                        <option value="">Wybierz linię lotniczą</option>
                        <?php foreach ($airlines as $airline): ?>
                            <option value="<?php echo $airline['id']; ?>">
                                <?php echo htmlspecialchars($airline['nazwa'] . ' (' . $airline['kod_IATA'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="id_samolotu">Samolot:</label>
                    <select id="id_samolotu" name="id_samolotu" required onchange="updateMaxSeats()">
                        <option value="">Wybierz samolot</option>
                        <?php foreach ($aircraft as $plane): ?>
                            <option value="<?php echo $plane['id']; ?>" data-seats="<?php echo $plane['liczba_miejsc']; ?>">
                                <?php echo htmlspecialchars($plane['model'] . ' (Miejsca: ' . $plane['liczba_miejsc'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="id_lotniska_start">Lotnisko Wylotu:</label>
                    <select id="id_lotniska_start" name="id_lotniska_start" required>
                        <option value="">Wybierz lotnisko wylotu</option>
                        <?php foreach ($airports as $airport): ?>
                            <option value="<?php echo $airport['id']; ?>">
                                <?php echo htmlspecialchars($airport['nazwa'] . ' - ' . $airport['miasto'] . ' (' . $airport['kod_IATA'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="id_lotniska_koniec">Lotnisko Przylotu:</label>
                    <select id="id_lotniska_koniec" name="id_lotniska_koniec" required>
                        <option value="">Wybierz lotnisko przylotu</option>
                        <?php foreach ($airports as $airport): ?>
                            <option value="<?php echo $airport['id']; ?>">
                                <?php echo htmlspecialchars($airport['nazwa'] . ' - ' . $airport['miasto'] . ' (' . $airport['kod_IATA'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="data_start">Data i Godzina Wylotu:</label>
                    <input type="datetime-local" id="data_start" name="data_start" required>
                </div>
                
                <div class="form-group">
                    <label for="data_koniec">Data i Godzina Przylotu:</label>
                    <input type="datetime-local" id="data_koniec" name="data_koniec" required>
                </div>
                
                <!-- Nowe pola: cena i dostępne miejsca -->
                <div class="form-group">
                    <label for="cena">Cena biletu (PLN):</label>
                    <input type="number" id="cena" name="cena" required min="1" step="0.01" placeholder="np. 299.99">
                </div>
                
                <div class="form-group">
                    <label for="dostepne_miejsca">Dostępne miejsca:</label>
                    <input type="number" id="dostepne_miejsca" name="dostepne_miejsca" required min="1" placeholder="np. 150">
                </div>
                
                <div class="form-group">
                    <label for="status_lotu">Status Lotu:</label>
                    <select id="status_lotu" name="status_lotu" required>
                        <option value="planowany">Planowany</option>
                        <option value="aktywny">Aktywny</option>
                        <option value="boarding">Boarding</option>
                        <option value="w_locie">W locie</option>
                        <option value="wyladowal">Wylądował</option>
                        <option value="opozniony">Opóźniony</option>
                        <option value="odwolany">Odwołany</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <a href="../admin/adminpage.php" class="btn btn-secondary">Anuluj</a>
                    <button type="submit" class="btn btn-primary">Dodaj Lot</button>
                </div>
            </form>
        </div>
    </main>
    <footer>
        <p>&copy; <?php echo date('Y'); ?> MosinAIR. Wszelkie prawa zastrzeżone.</p>
    </footer>
    
    <script>
        // Client-side validation to ensure departure and arrival airports are different
        document.querySelector('form').addEventListener('submit', function(e) {
            const departureAirport = document.getElementById('id_lotniska_start').value;
            const arrivalAirport = document.getElementById('id_lotniska_koniec').value;
            
            if (departureAirport === arrivalAirport) {
                e.preventDefault();
                alert('Lotnisko wylotu i przylotu nie może być takie samo.');
            }
            
            const departureDate = new Date(document.getElementById('data_start').value);
            const arrivalDate = new Date(document.getElementById('data_koniec').value);
            
            if (departureDate >= arrivalDate) {
                e.preventDefault();
                alert('Data wylotu musi być wcześniejsza niż data przylotu.');
            }
            
            const cena = parseFloat(document.getElementById('cena').value);
            if (isNaN(cena) || cena <= 0) {
                e.preventDefault();
                alert('Cena musi być liczbą dodatnią.');
            }

            const dostepneMiejsca = parseInt(document.getElementById('dostepne_miejsca').value);
            if (isNaN(dostepneMiejsca) || dostepneMiejsca <= 0) {
                e.preventDefault();
                alert('Dostępne miejsca muszą być liczbą dodatnią.');
            }
        });
    </script>
</body>
</html> 