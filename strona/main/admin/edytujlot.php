<?php
session_start();
require_once '../../config.php';

// Sprawdzenie czy użytkownik jest zalogowany jako admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: adminlogin.php");
    exit();
}

// Sprawdzenie czy przekazano ID lotu
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Nie podano identyfikatora lotu.";
    header("Location: adminpage.php");
    exit();
}

$lot_id = intval($_GET['id']);

// Pobieranie danych lotnisk do formularza
$lotniska_query = "SELECT id, nazwa FROM lotniska ORDER BY nazwa";
$stmt_lotniska = $db->query($lotniska_query);
$lotniska = $stmt_lotniska->fetchAll(PDO::FETCH_ASSOC);

// Pobieranie danych lotu do edycji
$edit_query = "SELECT * FROM loty WHERE id = :id";
$stmt_edit = $db->prepare($edit_query);
$stmt_edit->bindParam(':id', $lot_id, PDO::PARAM_INT);
$stmt_edit->execute();

if ($stmt_edit->rowCount() == 0) {
    $_SESSION['error'] = "Nie znaleziono lotu o podanym ID.";
    header("Location: adminpage.php");
    exit();
}

$lot = $stmt_edit->fetch(PDO::FETCH_ASSOC);

// Obsługa formularza edycji
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numer_lotu = $_POST['numer_lotu'];
    $id_lotniska_start = $_POST['id_lotniska_start'];
    $id_lotniska_koniec = $_POST['id_lotniska_koniec'];
    $data_start = $_POST['data_start'];
    $data_koniec = $_POST['data_koniec'];
    $status_lotu = $_POST['status_lotu'];

    try {
        $update_query = "UPDATE loty SET 
                        numer_lotu = :numer_lotu, 
                        id_lotniska_start = :id_lotniska_start, 
                        id_lotniska_koniec = :id_lotniska_koniec, 
                        data_start = :data_start, 
                        data_koniec = :data_koniec, 
                        status_lotu = :status_lotu 
                        WHERE id = :id";
        
        $stmt_update = $db->prepare($update_query);
        $stmt_update->bindParam(':numer_lotu', $numer_lotu);
        $stmt_update->bindParam(':id_lotniska_start', $id_lotniska_start);
        $stmt_update->bindParam(':id_lotniska_koniec', $id_lotniska_koniec);
        $stmt_update->bindParam(':data_start', $data_start);
        $stmt_update->bindParam(':data_koniec', $data_koniec);
        $stmt_update->bindParam(':status_lotu', $status_lotu);
        $stmt_update->bindParam(':id', $lot_id);
        
        $stmt_update->execute();
        
        $_SESSION['success'] = "Lot został pomyślnie zaktualizowany.";
        header("Location: adminpage.php");
        exit();
    } catch (PDOException $e) {
        $error_message = "Błąd podczas aktualizacji lotu: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edycja Lotu - Panel Administratora</title>
    <link rel="stylesheet" href="../../dodatki/style.css">
    <link rel="stylesheet" href="admin.css">
    <link rel="icon" type="image/jpeg" sizes="64x64" href="../../zdjecia/logo/logo_mosinair.jpeg">
    <style>
        .container {
            max-width: 800px;
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
                <a href="manage_airports.php">Zarządzaj Lotniskami</a>
                <a href="manage_reservations.php">Zarządzaj Rezerwacjami</a>
                <a href="../../index.php">Wyloguj się</a>
            </div>
        </nav>
    </header>
    <main>
        <div class="container">
            <h1>Edycja Lotu</h1>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <div class="form-container">
                <form method="post" action="">
                    <div class="form-group">
                        <label for="numer_lotu">Numer lotu:</label>
                        <input type="text" id="numer_lotu" name="numer_lotu" required 
                               value="<?php echo htmlspecialchars($lot['numer_lotu']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="id_lotniska_start">Lotnisko startowe:</label>
                        <select id="id_lotniska_start" name="id_lotniska_start" required>
                            <?php foreach ($lotniska as $lotnisko): ?>
                                <option value="<?php echo $lotnisko['id']; ?>" 
                                    <?php echo ($lot['id_lotniska_start'] == $lotnisko['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($lotnisko['nazwa']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="id_lotniska_koniec">Lotnisko docelowe:</label>
                        <select id="id_lotniska_koniec" name="id_lotniska_koniec" required>
                            <?php foreach ($lotniska as $lotnisko): ?>
                                <option value="<?php echo $lotnisko['id']; ?>" 
                                    <?php echo ($lot['id_lotniska_koniec'] == $lotnisko['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($lotnisko['nazwa']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="data_start">Data wylotu:</label>
                        <input type="datetime-local" id="data_start" name="data_start" required 
                               value="<?php echo str_replace(' ', 'T', $lot['data_start']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="data_koniec">Data przylotu:</label>
                        <input type="datetime-local" id="data_koniec" name="data_koniec" required 
                               value="<?php echo str_replace(' ', 'T', $lot['data_koniec']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="status_lotu">Status lotu:</label>
                        <select id="status_lotu" name="status_lotu" required>
                            <option value="aktywny" <?php echo ($lot['status_lotu'] == 'aktywny') ? 'selected' : ''; ?>>Aktywny</option>
                            <option value="odwołany" <?php echo ($lot['status_lotu'] == 'odwołany') ? 'selected' : ''; ?>>Odwołany</option>
                            <option value="zakończony" <?php echo ($lot['status_lotu'] == 'zakończony') ? 'selected' : ''; ?>>Zakończony</option>
                            <option value="opóźniony" <?php echo ($lot['status_lotu'] == 'opóźniony') ? 'selected' : ''; ?>>Opóźniony</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn">Aktualizuj lot</button>
                    <a href="adminpage.php" class="btn btn-secondary">Anuluj</a>
                </form>
            </div>
        </div>
    </main>
    <footer>
        <p>&copy; <?php echo date('Y'); ?> MosinAIR. Wszelkie prawa zastrzeżone.</p>
    </footer>
</body>
</html>
