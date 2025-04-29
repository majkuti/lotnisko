<?php
session_start();
require_once '../../config.php';

// Sprawdzenie czy użytkownik jest zalogowany jako admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Obsługa usuwania lotu
if (isset($_GET['delete_id']) && !empty($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    
    // Najpierw usuwamy powiązane rezerwacje
    $delete_reservations = $conn->prepare("DELETE FROM rezerwacje WHERE lot_id = ?");
    $delete_reservations->bind_param("i", $delete_id);
    $delete_reservations->execute();
    
    // Następnie usuwamy lot
    $delete_query = $conn->prepare("DELETE FROM loty WHERE id = ?");
    $delete_query->bind_param("i", $delete_id);
    
    if ($delete_query->execute()) {
        $success_message = "Lot został pomyślnie usunięty.";
    } else {
        $error_message = "Błąd podczas usuwania lotu: " . $conn->error;
    }
}

// Obsługa dodawania/edycji lotu
if (isset($_POST['submit'])) {
    $numer_lotu = $_POST['numer_lotu'];
    $skad = $_POST['skad'];
    $dokad = $_POST['dokad'];
    $data_wylotu = $_POST['data_wylotu'];
    $data_przylotu = $_POST['data_przylotu'];
    $cena = $_POST['cena'];
    $dostepne_miejsca = $_POST['dostepne_miejsca'];
    $samolot_id = $_POST['samolot_id'];
    
    // Sprawdzenie czy to edycja czy dodawanie nowego lotu
    if (isset($_POST['edit_id']) && !empty($_POST['edit_id'])) {
        // Edycja istniejącego lotu
        $edit_id = $_POST['edit_id'];
        $update_query = $conn->prepare("UPDATE loty SET numer_lotu = ?, skad = ?, dokad = ?, data_wylotu = ?, data_przylotu = ?, cena = ?, dostepne_miejsca = ?, samolot_id = ? WHERE id = ?");
        $update_query->bind_param("sssssdiis", $numer_lotu, $skad, $dokad, $data_wylotu, $data_przylotu, $cena, $dostepne_miejsca, $samolot_id, $edit_id);
        
        if ($update_query->execute()) {
            $success_message = "Lot został pomyślnie zaktualizowany.";
        } else {
            $error_message = "Błąd podczas aktualizacji lotu: " . $conn->error;
        }
    } else {
        // Dodawanie nowego lotu
        $insert_query = $conn->prepare("INSERT INTO loty (numer_lotu, skad, dokad, data_wylotu, data_przylotu, cena, dostepne_miejsca, samolot_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $insert_query->bind_param("sssssdiis", $numer_lotu, $skad, $dokad, $data_wylotu, $data_przylotu, $cena, $dostepne_miejsca, $samolot_id);
        
        if ($insert_query->execute()) {
            $success_message = "Nowy lot został pomyślnie dodany.";
        } else {
            $error_message = "Błąd podczas dodawania lotu: " . $conn->error;
        }
    }
}

// Pobieranie danych lotu do edycji
$edit_data = null;
if (isset($_GET['edit_id']) && !empty($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $edit_query = $conn->prepare("SELECT * FROM loty WHERE id = ?");
    $edit_query->bind_param("i", $edit_id);
    $edit_query->execute();
    $result = $edit_query->get_result();
    
    if ($result->num_rows > 0) {
        $edit_data = $result->fetch_assoc();
    }
}

// Pobieranie listy samolotów do formularza
$samoloty_query = "SELECT id, model FROM samoloty ORDER BY model";
$samoloty_result = $conn->query($samoloty_query);
$samoloty = [];
if ($samoloty_result->num_rows > 0) {
    while ($row = $samoloty_result->fetch_assoc()) {
        $samoloty[] = $row;
    }
}

// Pobieranie listy wszystkich lotów
$loty_query = "SELECT l.*, s.model as samolot_model 
               FROM loty l 
               LEFT JOIN samoloty s ON l.samolot_id = s.id 
               ORDER BY l.data_wylotu DESC";
$loty_result = $conn->query($loty_query);
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zarządzanie Lotami - Panel Administratora</title>
    <link rel="stylesheet" href="../css/style.css">
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
        }
        .btn-danger {
            background-color: #f44336;
        }
        .btn-edit {
            background-color: #2196F3;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        .table th, .table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .table th {
            background-color: #f2f2f2;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-success {
            background-color: #dff0d8;
            color: #3c763d;
        }
        .alert-danger {
            background-color: #f2dede;
            color: #a94442;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Zarządzanie Lotami</h1>
        <a href="index.php" class="btn" style="margin-bottom: 20px;">Powrót do panelu administratora</a>
        
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <div class="form-container">
            <h2><?php echo $edit_data ? 'Edytuj lot' : 'Dodaj nowy lot'; ?></h2>
            <form method="post" action="">
                <?php if ($edit_data): ?>
                    <input type="hidden" name="edit_id" value="<?php echo $edit_data['id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="numer_lotu">Numer lotu:</label>
                    <input type="text" id="numer_lotu" name="numer_lotu" required 
                           value="<?php echo $edit_data ? $edit_data['numer_lotu'] : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="skad">Skąd:</label>
                    <input type="text" id="skad" name="skad" required 
                           value="<?php echo $edit_data ? $edit_data['skad'] : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="dokad">Dokąd:</label>
                    <input type="text" id="dokad" name="dokad" required 
                           value="<?php echo $edit_data ? $edit_data['dokad'] : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="data_wylotu">Data wylotu:</label>
                    <input type="datetime-local" id="data_wylotu" name="data_wylotu" required 
                           value="<?php echo $edit_data ? str_replace(' ', 'T', $edit_data['data_wylotu']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="data_przylotu">Data przylotu:</label>
                    <input type="datetime-local" id="data_przylotu" name="data_przylotu" required 
                           value="<?php echo $edit_data ? str_replace(' ', 'T', $edit_data['data_przylotu']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="cena">Cena:</label>
                    <input type="number" id="cena" name="cena" step="0.01" required 
                           value="<?php echo $edit_data ? $edit_data['cena'] : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="dostepne_miejsca">Dostępne miejsca:</label>
                    <input type="number" id="dostepne_miejsca" name="dostepne_miejsca" required 
                           value="<?php echo $edit_data ? $edit_data['dostepne_miejsca'] : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="samolot_id">Samolot:</label>
                    <select id="samolot_id" name="samolot_id" required>
                        <option value="">Wybierz samolot</option>
                        <?php foreach ($samoloty as $samolot): ?>
                            <option value="<?php echo $samolot['id']; ?>" 
                                <?php echo ($edit_data && $edit_data['samolot_id'] == $samolot['id']) ? 'selected' : ''; ?>>
                                <?php echo $samolot['model']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="submit" name="submit" class="btn">
                    <?php echo $edit_data ? 'Aktualizuj lot' : 'Dodaj lot'; ?>
                </button>
                
                <?php if ($edit_data): ?>
                    <a href="zarzadzenielotami.php" class="btn" style="margin-left: 10px;">Anuluj edycję</a>
                <?php endif; ?>
            </form>
        </div>
        
        <h2>Lista lotów</h2>
        <div style="overflow-x: auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Numer lotu</th>
                        <th>Skąd</th>
                        <th>Dokąd</th>
                        <th>Data wylotu</th>
                        <th>Data przylotu</th>
                        <th>Cena</th>
                        <th>Dostępne miejsca</th>
                        <th>Samolot</th>
                        <th>Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($loty_result->num_rows > 0): ?>
                        <?php while ($lot = $loty_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $lot['id']; ?></td>
                                <td><?php echo $lot['numer_lotu']; ?></td>
                                <td><?php echo $lot['skad']; ?></td>
                                <td><?php echo $lot['dokad']; ?></td>
                                <td><?php echo $lot['data_wylotu']; ?></td>
                                <td><?php echo $lot['data_przylotu']; ?></td>
                                <td><?php echo number_format($lot['cena'], 2); ?> zł</td>
                                <td><?php echo $lot['dostepne_miejsca']; ?></td>
                                <td><?php echo $lot['samolot_model']; ?></td>
                                <td>
                                    <a href="zarzadzenielotami.php?edit_id=<?php echo $lot['id']; ?>" class="btn btn-edit">Edytuj</a>
                                    <a href="zarzadzenielotami.php?delete_id=<?php echo $lot['id']; ?>" 
                                       class="btn btn-danger" 
                                       onclick="return confirm('Czy na pewno chcesz usunąć ten lot? Ta operacja usunie również wszystkie powiązane rezerwacje.')">Usuń</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10">Brak lotów w bazie danych.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
