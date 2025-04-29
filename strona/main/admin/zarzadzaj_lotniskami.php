<?php
session_start();
require_once '../../config.php';

// Sprawdzenie czy użytkownik jest zalogowany jako admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: adminlogin.php");
    exit();
}

// Obsługa dodawania nowego lotniska
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_airport'])) {
    $nazwa = trim($_POST['nazwa']);
    $miasto = trim($_POST['miasto']);
    $kraj = trim($_POST['kraj']);
    $kod_IATA = trim($_POST['kod_IATA']);
    $terminal_count = intval($_POST['terminal_count']);
    
    // Walidacja danych
    $errors = [];
    
    if (empty($nazwa)) {
        $errors[] = "Nazwa lotniska jest wymagana.";
    }
    
    if (empty($miasto)) {
        $errors[] = "Miasto jest wymagane.";
    }
    
    if (empty($kraj)) {
        $errors[] = "Kraj jest wymagany.";
    }
    
    if (empty($kod_IATA)) {
        $errors[] = "Kod IATA jest wymagany.";
    } elseif (strlen($kod_IATA) !== 3) {
        $errors[] = "Kod IATA musi składać się z 3 znaków.";
    }
    
    if ($terminal_count < 1) {
        $errors[] = "Liczba terminali musi być większa od 0.";
    }
    
    // Sprawdzenie czy kod IATA jest unikalny
    $check_query = "SELECT id FROM lotniska WHERE kod_IATA = :kod_IATA";
    $stmt_check = $db->prepare($check_query);
    $stmt_check->bindParam(':kod_IATA', $kod_IATA);
    $stmt_check->execute();
    
    if ($stmt_check->rowCount() > 0) {
        $errors[] = "Lotnisko z podanym kodem IATA już istnieje.";
    }
    
    // Jeśli nie ma błędów, dodaj lotnisko
    if (empty($errors)) {
        try {
            $insert_query = "INSERT INTO lotniska (nazwa, miasto, kraj, kod_IATA, terminal_count) 
                             VALUES (:nazwa, :miasto, :kraj, :kod_IATA, :terminal_count)";
            $stmt_insert = $db->prepare($insert_query);
            $stmt_insert->bindParam(':nazwa', $nazwa);
            $stmt_insert->bindParam(':miasto', $miasto);
            $stmt_insert->bindParam(':kraj', $kraj);
            $stmt_insert->bindParam(':kod_IATA', $kod_IATA);
            $stmt_insert->bindParam(':terminal_count', $terminal_count);
            
            if ($stmt_insert->execute()) {
                $success_message = "Lotnisko zostało pomyślnie dodane.";
            } else {
                $errors[] = "Wystąpił błąd podczas dodawania lotniska.";
            }
        } catch (PDOException $e) {
            $errors[] = "Błąd bazy danych: " . $e->getMessage();
        }
    }
}

// Obsługa usuwania lotniska
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    try {
        // Sprawdź czy lotnisko jest używane w lotach
        $check_flights = "SELECT COUNT(*) FROM loty WHERE id_lotniska_start = :id OR id_lotniska_koniec = :id";
        $stmt_check = $db->prepare($check_flights);
        $stmt_check->bindParam(':id', $id);
        $stmt_check->execute();
        $flight_count = $stmt_check->fetchColumn();
        
        if ($flight_count > 0) {
            $errors[] = "Nie można usunąć lotniska, ponieważ jest używane w lotach.";
        } else {
            // Sprawdź czy lotnisko ma przypisane gate'y
            $check_gates = "SELECT COUNT(*) FROM gates WHERE id_lotniska = :id";
            $stmt_gates = $db->prepare($check_gates);
            $stmt_gates->bindParam(':id', $id);
            $stmt_gates->execute();
            $gate_count = $stmt_gates->fetchColumn();
            
            if ($gate_count > 0) {
                $errors[] = "Nie można usunąć lotniska, ponieważ ma przypisane gate'y.";
            } else {
                // Usuń lotnisko
                $delete_query = "DELETE FROM lotniska WHERE id = :id";
                $stmt_delete = $db->prepare($delete_query);
                $stmt_delete->bindParam(':id', $id);
                
                if ($stmt_delete->execute()) {
                    $success_message = "Lotnisko zostało pomyślnie usunięte.";
                } else {
                    $errors[] = "Wystąpił błąd podczas usuwania lotniska.";
                }
            }
        }
    } catch (PDOException $e) {
        $errors[] = "Błąd bazy danych: " . $e->getMessage();
    }
}

// Obsługa edycji lotniska
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_airport'])) {
    $id = intval($_POST['id']);
    $nazwa = trim($_POST['nazwa']);
    $miasto = trim($_POST['miasto']);
    $kraj = trim($_POST['kraj']);
    $kod_IATA = trim($_POST['kod_IATA']);
    $terminal_count = intval($_POST['terminal_count']);
    
    // Walidacja danych
    $errors = [];
    
    if (empty($nazwa)) {
        $errors[] = "Nazwa lotniska jest wymagana.";
    }
    
    if (empty($miasto)) {
        $errors[] = "Miasto jest wymagane.";
    }
    
    if (empty($kraj)) {
        $errors[] = "Kraj jest wymagany.";
    }
    
    if (empty($kod_IATA)) {
        $errors[] = "Kod IATA jest wymagany.";
    } elseif (strlen($kod_IATA) !== 3) {
        $errors[] = "Kod IATA musi składać się z 3 znaków.";
    }
    
    if ($terminal_count < 1) {
        $errors[] = "Liczba terminali musi być większa od 0.";
    }
    
    // Sprawdzenie czy kod IATA jest unikalny (z wyjątkiem edytowanego lotniska)
    $check_query = "SELECT id FROM lotniska WHERE kod_IATA = :kod_IATA AND id != :id";
    $stmt_check = $db->prepare($check_query);
    $stmt_check->bindParam(':kod_IATA', $kod_IATA);
    $stmt_check->bindParam(':id', $id);
    $stmt_check->execute();
    
    if ($stmt_check->rowCount() > 0) {
        $errors[] = "Lotnisko z podanym kodem IATA już istnieje.";
    }
    
    // Jeśli nie ma błędów, zaktualizuj lotnisko
    if (empty($errors)) {
        try {
            $update_query = "UPDATE lotniska SET 
                            nazwa = :nazwa, 
                            miasto = :miasto, 
                            kraj = :kraj, 
                            kod_IATA = :kod_IATA, 
                            terminal_count = :terminal_count 
                            WHERE id = :id";
            
            $stmt_update = $db->prepare($update_query);
            $stmt_update->bindParam(':nazwa', $nazwa);
            $stmt_update->bindParam(':miasto', $miasto);
            $stmt_update->bindParam(':kraj', $kraj);
            $stmt_update->bindParam(':kod_IATA', $kod_IATA);
            $stmt_update->bindParam(':terminal_count', $terminal_count);
            $stmt_update->bindParam(':id', $id);
            
            if ($stmt_update->execute()) {
                $success_message = "Lotnisko zostało pomyślnie zaktualizowane.";
            } else {
                $errors[] = "Wystąpił błąd podczas aktualizacji lotniska.";
            }
        } catch (PDOException $e) {
            $errors[] = "Błąd bazy danych: " . $e->getMessage();
        }
    }
}

// Pobieranie danych lotniska do edycji
$edit_data = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $id = intval($_GET['edit']);
    
    $edit_query = "SELECT * FROM lotniska WHERE id = :id";
    $stmt_edit = $db->prepare($edit_query);
    $stmt_edit->bindParam(':id', $id);
    $stmt_edit->execute();
    
    $edit_data = $stmt_edit->fetch(PDO::FETCH_ASSOC);
    
    if (!$edit_data) {
        $errors[] = "Nie znaleziono lotniska o podanym ID.";
    }
}

// Pobieranie listy lotnisk
$airports_query = "SELECT * FROM lotniska ORDER BY nazwa";
$stmt_airports = $db->query($airports_query);
$airports = $stmt_airports->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zarządzanie Lotniskami - Panel Administratora</title>
    <link rel="stylesheet" href="../../dodatki/style.css">
    <link rel="stylesheet" href="admin.css">
    <link rel="icon" type="image/jpeg" sizes="64x64" href="../../zdjecia/logo/logo_mosinair.jpeg">
    <style>
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .form-container {
            background-color: #f5f5f5;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
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
        }
        table th, table td {
            padding: 12px;
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
                <a href="manage_reservations.php">Zarządzaj Rezerwacjami</a>
                <a href="../../index.php">Wyloguj się</a>
            </div>
        </nav>
    </header>
    <main>
        <div class="container">
            <h1>Zarządzanie Lotniskami</h1>
            
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
            
            <div class="form-container">
                <h2><?php echo $edit_data ? 'Edytuj Lotnisko' : 'Dodaj Nowe Lotnisko'; ?></h2>
                <form method="post" action="">
                    <?php if ($edit_data): ?>
                        <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="nazwa">Nazwa lotniska:</label>
                        <input type="text" id="nazwa" name="nazwa" required 
                               value="<?php echo $edit_data ? htmlspecialchars($edit_data['nazwa']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="miasto">Miasto:</label>
                        <input type="text" id="miasto" name="miasto" required 
                               value="<?php echo $edit_data ? htmlspecialchars($edit_data['miasto']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="kraj">Kraj:</label>
                        <input type="text" id="kraj" name="kraj" required 
                               value="<?php echo $edit_data ? htmlspecialchars($edit_data['kraj']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="kod_IATA">Kod IATA (3 znaki):</label>
                        <input type="text" id="kod_IATA" name="kod_IATA" required maxlength="3" 
                               value="<?php echo $edit_data ? htmlspecialchars($edit_data['kod_IATA']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="terminal_count">Liczba terminali:</label>
                        <input type="number" id="terminal_count" name="terminal_count" required min="1" 
                               value="<?php echo $edit_data ? htmlspecialchars($edit_data['terminal_count']) : '1'; ?>">
                    </div>
                    
                    <?php if ($edit_data): ?>
                        <button type="submit" name="edit_airport" class="btn">Aktualizuj lotnisko</button>
                        <a href="zarzadzaj_lotniskami.php" class="btn btn-secondary">Anuluj</a>
                    <?php else: ?>
                        <button type="submit" name="add_airport" class="btn">Dodaj lotnisko</button>
                    <?php endif; ?>
                </form>
            </div>
            
            <h2>Lista lotnisk</h2>
            
            <?php if (count($airports) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nazwa</th>
                            <th>Miasto</th>
                            <th>Kraj</th>
                            <th>Kod IATA</th>
                            <th>Liczba terminali</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($airports as $airport): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($airport['id']); ?></td>
                                <td><?php echo htmlspecialchars($airport['nazwa']); ?></td>
                                <td><?php echo htmlspecialchars($airport['miasto']); ?></td>
                                <td><?php echo htmlspecialchars($airport['kraj']); ?></td>
                                <td><?php echo htmlspecialchars($airport['kod_IATA']); ?></td>
                                <td><?php echo htmlspecialchars($airport['terminal_count']); ?></td>
                                <td class="action-buttons">
                                    <a href="zarzadzaj_lotniskami.php?edit=<?php echo $airport['id']; ?>" class="btn btn-warning">Edytuj</a>
                                    <a href="zarzadzaj_lotniskami.php?delete=<?php echo $airport['id']; ?>" 
                                       class="btn btn-danger" 
                                       onclick="return confirm('Czy na pewno chcesz usunąć to lotnisko?');">Usuń</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Brak lotnisk w bazie danych.</p>
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