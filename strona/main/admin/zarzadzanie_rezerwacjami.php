<?php
session_start();
require_once '../../config.php';

// Sprawdzenie czy użytkownik jest zalogowany jako admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: adminlogin.php");
    exit();
}

// Obsługa anulowania rezerwacji
if (isset($_GET['cancel']) && is_numeric($_GET['cancel'])) {
    $id = intval($_GET['cancel']);
    
    try {
        // Pobierz informacje o rezerwacji
        $get_reservation = "SELECT r.*, l.id as lot_id, l.dostepne_miejsca 
                           FROM rezerwacje r 
                           JOIN loty l ON r.id_lotu = l.id 
                           WHERE r.id = :id";
        $stmt_get = $db->prepare($get_reservation);
        $stmt_get->bindParam(':id', $id);
        $stmt_get->execute();
        $reservation = $stmt_get->fetch(PDO::FETCH_ASSOC);
        
        if (!$reservation) {
            $errors[] = "Nie znaleziono rezerwacji o podanym ID.";
        } else {
            // Rozpocznij transakcję
            $db->beginTransaction();
            
            // Zmień status rezerwacji na anulowany
            $update_status = "UPDATE rezerwacje SET status_rezerwacji = 'anulowana' WHERE id = :id";
            $stmt_status = $db->prepare($update_status);
            $stmt_status->bindParam(':id', $id);
            $stmt_status->execute();
            
            // Zwiększ liczbę dostępnych miejsc w locie
            $update_seats = "UPDATE loty SET dostepne_miejsca = dostepne_miejsca + :liczba_miejsc WHERE id = :lot_id";
            $stmt_seats = $db->prepare($update_seats);
            $stmt_seats->bindParam(':liczba_miejsc', $reservation['liczba_miejsc']);
            $stmt_seats->bindParam(':lot_id', $reservation['lot_id']);
            $stmt_seats->execute();
            
            // Zatwierdź transakcję
            $db->commit();
            
            $success_message = "Rezerwacja została pomyślnie anulowana.";
        }
    } catch (PDOException $e) {
        // Wycofaj transakcję w przypadku błędu
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        $errors[] = "Błąd bazy danych: " . $e->getMessage();
    }
}

// Obsługa potwierdzenia rezerwacji
if (isset($_GET['confirm']) && is_numeric($_GET['confirm'])) {
    $id = intval($_GET['confirm']);
    
    try {
        // Zmień status rezerwacji na potwierdzony
        $update_status = "UPDATE rezerwacje SET status_rezerwacji = 'potwierdzona' WHERE id = :id";
        $stmt_status = $db->prepare($update_status);
        $stmt_status->bindParam(':id', $id);
        
        if ($stmt_status->execute()) {
            $success_message = "Rezerwacja została pomyślnie potwierdzona.";
        } else {
            $errors[] = "Wystąpił błąd podczas potwierdzania rezerwacji.";
        }
    } catch (PDOException $e) {
        $errors[] = "Błąd bazy danych: " . $e->getMessage();
    }
}

// Obsługa oznaczenia rezerwacji jako zrealizowanej
if (isset($_GET['complete']) && is_numeric($_GET['complete'])) {
    $id = intval($_GET['complete']);
    
    try {
        // Zmień status rezerwacji na zrealizowany
        $update_status = "UPDATE rezerwacje SET status_rezerwacji = 'zrealizowana' WHERE id = :id";
        $stmt_status = $db->prepare($update_status);
        $stmt_status->bindParam(':id', $id);
        
        if ($stmt_status->execute()) {
            $success_message = "Rezerwacja została oznaczona jako zrealizowana.";
        } else {
            $errors[] = "Wystąpił błąd podczas oznaczania rezerwacji jako zrealizowanej.";
        }
    } catch (PDOException $e) {
        $errors[] = "Błąd bazy danych: " . $e->getMessage();
    }
}

// Obsługa usuwania rezerwacji
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    try {
        // Pobierz informacje o rezerwacji
        $get_reservation = "SELECT r.*, l.id as lot_id, l.dostepne_miejsca 
                           FROM rezerwacje r 
                           JOIN loty l ON r.id_lotu = l.id 
                           WHERE r.id = :id";
        $stmt_get = $db->prepare($get_reservation);
        $stmt_get->bindParam(':id', $id);
        $stmt_get->execute();
        $reservation = $stmt_get->fetch(PDO::FETCH_ASSOC);
        
        if (!$reservation) {
            $errors[] = "Nie znaleziono rezerwacji o podanym ID.";
        } else {
            // Rozpocznij transakcję
            $db->beginTransaction();
            
            // Jeśli rezerwacja nie jest anulowana, zwiększ liczbę dostępnych miejsc w locie
            if ($reservation['status_rezerwacji'] !== 'anulowana') {
                $update_seats = "UPDATE loty SET dostepne_miejsca = dostepne_miejsca + :liczba_miejsc WHERE id = :lot_id";
                $stmt_seats = $db->prepare($update_seats);
                $stmt_seats->bindParam(':liczba_miejsc', $reservation['liczba_miejsc']);
                $stmt_seats->bindParam(':lot_id', $reservation['lot_id']);
                $stmt_seats->execute();
            }
            
            // Usuń rezerwację
            $delete_query = "DELETE FROM rezerwacje WHERE id = :id";
            $stmt_delete = $db->prepare($delete_query);
            $stmt_delete->bindParam(':id', $id);
            $stmt_delete->execute();
            
            // Zatwierdź transakcję
            $db->commit();
            
            $success_message = "Rezerwacja została pomyślnie usunięta.";
        }
    } catch (PDOException $e) {
        // Wycofaj transakcję w przypadku błędu
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        $errors[] = "Błąd bazy danych: " . $e->getMessage();
    }
}

// Filtrowanie rezerwacji
$where_clauses = [];
$params = [];

if (isset($_GET['filter_status']) && !empty($_GET['filter_status'])) {
    $where_clauses[] = "r.status_rezerwacji = :status";
    $params[':status'] = $_GET['filter_status'];
}

if (isset($_GET['filter_flight']) && !empty($_GET['filter_flight'])) {
    $where_clauses[] = "l.numer_lotu LIKE :flight";
    $params[':flight'] = '%' . $_GET['filter_flight'] . '%';
}

if (isset($_GET['filter_passenger']) && !empty($_GET['filter_passenger'])) {
    $where_clauses[] = "(p.imie LIKE :passenger OR p.nazwisko LIKE :passenger)";
    $params[':passenger'] = '%' . $_GET['filter_passenger'] . '%';
}

// Budowanie zapytania
$where_sql = '';
if (!empty($where_clauses)) {
    $where_sql = ' WHERE ' . implode(' AND ', $where_clauses);
}

// Pobieranie listy rezerwacji
$reservations_query = "SELECT r.*, 
                      l.numer_lotu, l.data_start, l.data_koniec,
                      p.imie, p.nazwisko, p.email,
                      ls1.nazwa as lotnisko_start, ls1.kod_IATA as kod_start,
                      ls2.nazwa as lotnisko_koniec, ls2.kod_IATA as kod_koniec
                      FROM rezerwacje r
                      JOIN loty l ON r.id_lotu = l.id
                      JOIN pasazerowie p ON r.id_pasazera = p.id
                      JOIN lotniska ls1 ON l.id_lotniska_start = ls1.id
                      JOIN lotniska ls2 ON l.id_lotniska_koniec = ls2.id
                      $where_sql
                      ORDER BY r.data_rezerwacji DESC";

$stmt_reservations = $db->prepare($reservations_query);
foreach ($params as $key => $value) {
    $stmt_reservations->bindValue($key, $value);
}
$stmt_reservations->execute();
$reservations = $stmt_reservations->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zarządzanie Rezerwacjami - Panel Administratora</title>
    <link rel="stylesheet" href="../../dodatki/style.css">
    <link rel="stylesheet" href="admin.css">
    <link rel="icon" type="image/jpeg" sizes="64x64" href="../../zdjecia/logo/logo_mosinair.jpeg">
    <style>
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .filter-container {
            background-color: #f5f5f5;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: flex-end;
        }
        .filter-group {
            flex: 1;
            min-width: 200px;
        }
        .filter-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .filter-group input, .filter-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .btn {
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-right: 10px;
        }
        .btn-secondary {
            background-color: #6c757d;
        }
        .btn-danger {
            background-color: #dc3545;
        }
        .btn-warning {
            background-color: #ffc107;
            color: #212529;
        }
        .btn-info {
            background-color: #17a2b8;
        }
        .btn-success {
            background-color: #28a745;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 0.9em;
        }
        table th, table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        table tr:hover {
            background-color: #f5f5f5;
        }
        .action-buttons {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }
        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.8em;
            font-weight: bold;
            text-align: center;
        }
        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }
        .badge-warning {
            background-color: #fff3cd;
            color: #856404;
        }
        .badge-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        .badge-info {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        .reservation-details {
            margin-top: 5px;
            font-size: 0.85em;
            color: #666;
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
                <a href="zarzadzenielotami.php">Zarządzaj Lotami</a>
                <a href="manage_users.php">Zarządzaj Użytkownikami</a>
                <a href="zarzadzaj_lotniskami.php">Zarządzaj Lotniskami</a>
                <a href="zarzadzanie_rezerwacjami.php">Zarządzaj Rezerwacjami</a>
                <a href="../../index.php">Wyloguj się</a>
            </div>
        </nav>
    </header>
    <main>
        <div class="container">
            <h1>Zarządzanie Rezerwacjami</h1>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>
            
            <div class="filter-container">
                <form method="get" action="" class="filter-form" style="display: flex; width: 100%; gap: 15px; flex-wrap: wrap; align-items: flex-end;">
                    <div class="filter-group">
                        <label for="filter_status">Status rezerwacji:</label>
                        <select id="filter_status" name="filter_status">
                            <option value="">Wszystkie</option>
                            <option value="potwierdzona" <?php echo (isset($_GET['filter_status']) && $_GET['filter_status'] === 'potwierdzona') ? 'selected' : ''; ?>>Potwierdzona</option>
                            <option value="anulowana" <?php echo (isset($_GET['filter_status']) && $_GET['filter_status'] === 'anulowana') ? 'selected' : ''; ?>>Anulowana</option>
                            <option value="zrealizowana" <?php echo (isset($_GET['filter_status']) && $_GET['filter_status'] === 'zrealizowana') ? 'selected' : ''; ?>>Zrealizowana</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="filter_flight">Numer lotu:</label>
                        <input type="text" id="filter_flight" name="filter_flight" value="<?php echo isset($_GET['filter_flight']) ? htmlspecialchars($_GET['filter_flight']) : ''; ?>">
                    </div>
                    
                    <div class="filter-group">
                        <label for="filter_passenger">Pasażer (imię/nazwisko):</label>
                        <input type="text" id="filter_passenger" name="filter_passenger" value="<?php echo isset($_GET['filter_passenger']) ? htmlspecialchars($_GET['filter_passenger']) : ''; ?>">
                    </div>
                    
                    <div class="filter-group" style="flex: 0 0 auto;">
                        <button type="submit" class="btn">Filtruj</button>
                        <a href="zarzadzanie_rezerwacjami.php" class="btn btn-secondary">Resetuj</a>
                    </div>
                </form>
            </div>
            
            <h2>Lista rezerwacji</h2>
            
            <?php if (count($reservations) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Pasażer</th>
                            <th>Lot</th>
                            <th>Trasa</th>
                            <th>Data rezerwacji</th>
                            <th>Status</th>
                            <th>Szczegóły</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reservations as $reservation): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($reservation['id']); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($reservation['imie'] . ' ' . $reservation['nazwisko']); ?>
                                    <div class="reservation-details"><?php echo htmlspecialchars($reservation['email']); ?></div>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($reservation['numer_lotu']); ?>
                                    <div class="reservation-details">
                                        <?php 
                                            echo date('d.m.Y H:i', strtotime($reservation['data_start']));
                                        ?>
                                    </div>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($reservation['kod_start'] . ' → ' . $reservation['kod_koniec']); ?>
                                    <div class="reservation-details">
                                        <?php echo htmlspecialchars($reservation['lotnisko_start'] . ' → ' . $reservation['lotnisko_koniec']); ?>
                                    </div>
                                </td>
                                <td><?php echo date('d.m.Y H:i', strtotime($reservation['data_rezerwacji'])); ?></td>
                                <td>
                                    <?php 
                                        $status_class = '';
                                        switch ($reservation['status_rezerwacji']) {
                                            case 'potwierdzona':
                                                $status_class = 'badge-success';
                                                break;
                                            case 'anulowana':
                                                $status_class = 'badge-danger';
                                                break;
                                            case 'zrealizowana':
                                                $status_class = 'badge-info';
                                                break;
                                            default:
                                                $status_class = 'badge-warning';
                                        }
                                    ?>
                                    <span class="badge <?php echo $status_class; ?>">
                                        <?php echo htmlspecialchars(ucfirst($reservation['status_rezerwacji'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <div>Miejsce: <?php echo htmlspecialchars($reservation['numer_miejsca']); ?></div>
                                    <div>Klasa: <?php echo htmlspecialchars(ucfirst($reservation['klasa_podrozy'])); ?></div>
                                    <div>Cena: <?php echo number_format($reservation['cena'], 2); ?> PLN</div>
                                    <?php if (isset($reservation['liczba_miejsc']) && $reservation['liczba_miejsc'] > 1): ?>
                                        <div>Liczba miejsc: <?php echo htmlspecialchars($reservation['liczba_miejsc']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="action-buttons">
                                    <?php if ($reservation['status_rezerwacji'] !== 'potwierdzona'): ?>
                                        <a href="zarzadzanie_rezerwacjami.php?confirm=<?php echo $reservation['id']; ?>" 
                                           class="btn btn-success" 
                                           onclick="return confirm('Czy na pewno chcesz potwierdzić tę rezerwację?');">Potwierdź</a>
                                    <?php endif; ?>
                                    
                                    <?php if ($reservation['status_rezerwacji'] !== 'anulowana'): ?>
                                        <a href="zarzadzanie_rezerwacjami.php?cancel=<?php echo $reservation['id']; ?>" 
                                           class="btn btn-warning" 
                                           onclick="return confirm('Czy na pewno chcesz anulować tę rezerwację?');">Anuluj</a>
                                    <?php endif; ?>
                                    
                                    <?php if ($reservation['status_rezerwacji'] !== 'zrealizowana'): ?>
                                        <a href="zarzadzanie_rezerwacjami.php?complete=<?php echo $reservation['id']; ?>" 
                                           class="btn btn-info" 
                                           onclick="return confirm('Czy na pewno chcesz oznaczyć tę rezerwację jako zrealizowaną?');">Zrealizuj</a>
                                    <?php endif; ?>
                                    
                                    <a href="zarzadzanie_rezerwacjami.php?delete=<?php echo $reservation['id']; ?>" 
                                       class="btn btn-danger" 
                                       onclick="return confirm('Czy na pewno chcesz usunąć tę rezerwację? Ta operacja jest nieodwracalna.');">Usuń</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Brak rezerwacji spełniających kryteria wyszukiwania.</p>
            <?php endif; ?>
            
            <a href="adminpage.php" class="btn btn-secondary">Powrót do panelu administratora</a>
        </div>
    </main>
    <footer>
        <p>&copy; <?php echo date('Y'); ?> MosinAIR. Wszelkie prawa zastrzeżone.</p>
    </footer>
    
    <script>
        // Highlight the current page in the navigation
        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = window.location.pathname.split('/').pop();
            const navLinks = document.querySelectorAll('.nav-links a');
            
            navLinks.forEach(link => {
                const linkPage = link.getAttribute('href');
                if (linkPage === currentPage) {
                    link.style.backgroundColor = '#4a4a4a';
                }
            });
        });
    </script>
</body>
</html>
