<?php
require_once '../../config.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: adminlogin.php');
    exit();
}

$query_users = "SELECT COUNT(*) as total_users FROM uzytkownicy";
$stmt_users = $db->query($query_users);
$users_count = $stmt_users->fetch()['total_users'];
// Fetch flights from database
$query = "SELECT l.*, 
          ls1.nazwa as lotnisko_start, 
          ls2.nazwa as lotnisko_koniec 
          FROM loty l
          JOIN lotniska ls1 ON l.id_lotniska_start = ls1.id
          JOIN lotniska ls2 ON l.id_lotniska_koniec = ls2.id";
$stmt = $db->query($query);
$loty = $stmt->fetchAll();

$query_reservations = "SELECT COUNT(*) as total_reservations FROM rezerwacje";
$stmt_reservations = $db->query($query_reservations);
$reservations_count = $stmt_reservations->fetch()['total_reservations'];


$query_flights = "SELECT COUNT(*) as total_flights FROM loty";
$stmt_flights = $db->query($query_flights);
$flights_count = $stmt_flights->fetch()['total_flights'];
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MosinAIR - Panel Administratora</title>
    <link rel="stylesheet" href="../../dodatki/style.css">
    <link rel="stylesheet" href="admin.css">
    <link rel="icon" type="image/jpeg" sizes="64x64" href="../../zdjecia/logo/logo_mosinair.jpeg">
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
                <a href="../admin/zarzadzaj_uzytkownikami.php">Zarządzaj Użytkownikami</a>
                <a href="manage_airports.php">Zarządzaj Lotniskami</a>
                <a href="manage_reservations.php">Zarządzaj Rezerwacjami</a>
                <a href="../../index.php">Wyloguj się</a>
            </div>
        </nav>
    </header>
    <main>
        <div class="admin-dashboard">
            <h1>Panel Administratora</h1>
            <p>Witaj, <?php echo htmlspecialchars($_SESSION['admin_email']); ?></p>

            <section class="admin-stats">
                <h2>Statystyki</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3>Aktywne Loty</h3>
                             <p class="stat-number"><?php echo $flights_count; ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>Dzisiejsze Rezerwacje</h3>
                        <p class="stat-number"><?php echo $reservations_count; ?></p>
                        </div>
                    <div class="stat-card">
                      <h3>Zarejestowani Użytkownicy</h3>
                        <p class="stat-number"><?php echo $users_count; ?></p>
                        </div>
                </div>
            </section>

            <section class="flight-management">
                <h2>Zarządzanie Lotami</h2>
                <div class="action-buttons">
                    <a href="../admin/dodawnie_lotu.php" class="admin-btn">Dodaj Nowy Lot</a>
                </div>
                <div class="flights-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Numer Lotu</th>
                                <th>Z</th>
                                <th>Do</th>
                                <th>Data Wylotu</th>
                                <th>Status</th>
                                <th>Cena (PLN)</th>
                                <th>Dostępne Miejsca</th>
                                <th>Akcje</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($loty as $lot): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($lot['numer_lotu']); ?></td>
                                <td><?php echo htmlspecialchars($lot['lotnisko_start']); ?></td>
                                <td><?php echo htmlspecialchars($lot['lotnisko_koniec']); ?></td>
                                <td><?php echo date('d.m.Y H:i', strtotime($lot['data_start'])); ?></td>
                                <td><?php echo htmlspecialchars($lot['status_lotu']); ?></td>
                                <td><?php echo number_format($lot['cena'], 2); ?></td>
                                <td><?php echo htmlspecialchars($lot['dostepne_miejsca']); ?></td>
                                <td class="action-buttons">
                                    <a href="edytujlot.php?id=<?php echo $lot['id']; ?>" class="edit-btn">Edytuj</a>
                                    <a href="usunlot.php?id=<?php echo $lot['id']; ?>" class="delete-btn" onclick="return confirm('Czy na pewno chcesz usunąć ten lot?')">Usuń</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>
    <footer>
        <p>&copy; <?php echo date('Y'); ?> MosinAIR. Wszelkie prawa zastrzeżone.</p>
    </footer>
</body>
</html>
