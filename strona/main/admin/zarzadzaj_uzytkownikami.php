<?php
require_once '../../config.php';
session_start();

// Sprawdzenie czy administrator jest zalogowany
if (!isset($_SESSION['admin_id'])) {
    header('Location: adminlogin.php');
    exit();
}

// Obsługa usuwania użytkownika
if (isset($_GET['delete_id']) && !empty($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    try {
        // Najpierw usuń rezerwacje użytkownika
        $delete_reservations = $db->prepare("DELETE FROM rezerwacje WHERE id = ?");
        $delete_reservations->execute([$delete_id]);
        
        // Następnie usuń użytkownika
        $delete_user = $db->prepare("DELETE FROM uzytkownicy WHERE id = ?");
        $delete_user->execute([$delete_id]);
        
        $_SESSION['success'] = "Użytkownik został pomyślnie usunięty.";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Błąd podczas usuwania użytkownika: " . $e->getMessage();
    }
    
    header('Location: zarzadzaj_uzytkownikami.php');
    exit();
}

// Obsługa edycji użytkownika
if (isset($_POST['edit_user'])) {
    $user_id = intval($_POST['user_id']);
    $imie = $_POST['imie'];
    $nazwisko = $_POST['nazwisko'];
    $email = $_POST['email'];
    
    try {
        $update_user = $db->prepare("UPDATE uzytkownicy SET imie = ?, nazwisko = ?, email = ? WHERE id = ?");
        $update_user->execute([$imie, $nazwisko, $email, $user_id]);
        
        $_SESSION['success'] = "Dane użytkownika zostały zaktualizowane.";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Błąd podczas aktualizacji danych użytkownika: " . $e->getMessage();
    }
    
    header('Location: zarzadzaj_uzytkownikami.php');
    exit();
}

// Pobieranie danych użytkownika do edycji
$edit_user_data = null;
if (isset($_GET['edit_id']) && !empty($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    
    try {
        $get_user = $db->prepare("SELECT * FROM uzytkownicy WHERE id = ?");
        $get_user->execute([$edit_id]);
        $edit_user_data = $get_user->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $_SESSION['error'] = "Błąd podczas pobierania danych użytkownika: " . $e->getMessage();
    }
}

// Pobieranie listy wszystkich użytkowników
try {
    $query = "SELECT * FROM uzytkownicy ORDER BY id";
    $stmt = $db->query($query);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error'] = "Błąd podczas pobierania listy użytkowników: " . $e->getMessage();
    $users = [];
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MosinAIR - Zarządzanie Użytkownikami</title>
    <link rel="stylesheet" href="../../dodatki/style.css">
    <link rel="stylesheet" href="admin.css">
    <link rel="icon" type="image/jpeg" sizes="64x64" href="../../zdjecia/logo/logo_mosinair.jpeg">
    <style>
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .user-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .user-table th, .user-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        .user-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .user-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .user-table tr:hover {
            background-color: #f1f1f1;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        .edit-btn, .delete-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            color: white;
            font-weight: bold;
        }
        .edit-btn {
            background-color: #4CAF50;
        }
        .delete-btn {
            background-color: #f44336;
        }
        .edit-form {
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
        .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .submit-btn {
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .cancel-btn {
            padding: 10px 15px;
            background-color: #f44336;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-left: 10px;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
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
                <a href="../admin/adminpage.php">Zarządzaj Lotami</a>
                <a href="zarzadzaj_uzytkownikami.php" class="active">Zarządzaj Użytkownikami</a>
                <a href="manage_airports.php">Zarządzaj Lotniskami</a>
                <a href="manage_reservations.php">Zarządzaj Rezerwacjami</a>
                <a href="../../index.php">Wyloguj się</a>
            </div>
        </nav>
    </header>
    <main>
        <div class="container">
            <h1>Zarządzanie Użytkownikami</h1>
            
            <?php if(isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            
            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
            
            <?php if($edit_user_data): ?>
                <div class="edit-form">
                    <h2>Edytuj Użytkownika</h2>
                    <form method="post" action="">
                        <input type="hidden" name="user_id" value="<?php echo $edit_user_data['id']; ?>">
                        
                        <div class="form-group">
                            <label for="imie">Imię:</label>
                            <input type="text" id="imie" name="imie" value="<?php echo htmlspecialchars($edit_user_data['imie']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="nazwisko">Nazwisko:</label>
                            <input type="text" id="nazwisko" name="nazwisko" value="<?php echo htmlspecialchars($edit_user_data['nazwisko']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($edit_user_data['email']); ?>" required>
                        </div>
                        
                        <button type="submit" name="edit_user" class="submit-btn">Zapisz zmiany</button>
                        <a href="zarzadzaj_uzytkownikami.php" class="cancel-btn">Anuluj</a>
                    </form>
                </div>
            <?php endif; ?>
            
            <h2>Lista Użytkowników</h2>
            
            <?php if(count($users) > 0): ?>
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Imię</th>
                            <th>Nazwisko</th>
                            <th>Email</th>
                            <th>Data rejestracji</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['imie']); ?></td>
                                <td><?php echo htmlspecialchars($user['nazwisko']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo isset($user['data_rejestracji']) ? date('d.m.Y H:i', strtotime($user['data_rejestracji'])) : 'Brak danych'; ?></td>
                                <td class="action-buttons">
                                    <a href="zarzadzaj_uzytkownikami.php?edit_id=<?php echo $user['id']; ?>" class="edit-btn">Edytuj</a>
                                    <a href="zarzadzaj_uzytkownikami.php?delete_id=<?php echo $user['id']; ?>" class="delete-btn" onclick="return confirm('Czy na pewno chcesz usunąć tego użytkownika? Ta operacja usunie również wszystkie jego rezerwacje.')">Usuń</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Brak użytkowników w bazie danych.</p>
            <?php endif; ?>
        </div>
    </main>
    <footer>
        <p>&copy; <?php echo date('Y'); ?> MosinAIR. Wszelkie prawa zastrzeżone.</p>
    </footer>
</body>
</html>
