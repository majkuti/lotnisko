<?php
session_start();

// Sprawdź czy użytkownik jest zalogowany
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Połączenie z bazą danych
require_once '../config.php';

// Pobierz dane użytkownika
$user_id = $_SESSION['user_id'];

try {
    $stmt = $db->prepare("SELECT * FROM uzytkownicy WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        // Użytkownik nie istnieje w bazie danych
        session_destroy();
        header("Location: ../index.php");
        exit();
    }
} catch(PDOException $e) {
    echo "Błąd: " . $e->getMessage();
    exit();
}

// Obsługa aktualizacji profilu
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $imie = trim($_POST['imie']);
    $nazwisko = trim($_POST['nazwisko']);
    $email = trim($_POST['email']);
    
    // Walidacja danych
    if (empty($imie) || empty($nazwisko) || empty($email)) {
        $message = '<div class="alert alert-danger">Imię, nazwisko i email są wymagane!</div>';
    } else {
        // Sprawdź czy email jest już używany przez innego użytkownika
        $check_email = $db->prepare("SELECT id FROM uzytkownicy WHERE email = ? AND id != ?");
        $check_email->execute([$email, $user_id]);
        
        if ($check_email->rowCount() > 0) {
            $message = '<div class="alert alert-danger">Ten adres email jest już używany!</div>';
        } else {
            // Aktualizacja danych
            $update_stmt = $db->prepare("UPDATE uzytkownicy SET imie = ?, nazwisko = ?, email = ? WHERE id = ?");
            
            if ($update_stmt->execute([$imie, $nazwisko, $email, $user_id])) {
                $message = '<div class="alert alert-success">Profil został zaktualizowany pomyślnie!</div>';
                // Odśwież dane użytkownika
                $stmt->execute([$user_id]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $message = '<div class="alert alert-danger">Wystąpił błąd podczas aktualizacji profilu!</div>';
            }
        }
    }
};

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../main/profil.css">
    <link rel="stylesheet" href="../dodatki/style.css">
    <title>Document</title>
</head>
<body>
<header>
    <nav>
        <div class="logo">
            <a href="../main/loginuser.php">
                <img src="../zdjecia/logo/logo_mosinair.jpeg" alt="MosinAIR Logo" width="50" height="50">
            </a>
        </div>
        <div class="nav-links">
            <a class="nav-link active" href="#edit-profile" data-bs-toggle="tab">Edytuj profil</a>
            <a class="nav-link" href="../main/loginuser.php">Strona główna</a>
            <a class="nav-link text-danger" href="../main/logout.php">Wyloguj się</a>
        </div>
    </nav>
</header>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-12">
            <h1 class="mb-4">Profil użytkownika</h1>
            <?php echo $message; ?>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Informacje osobiste</h5>
                </div>
                <div class="card-body">
                    <p><strong>Imię:</strong> <?php echo htmlspecialchars($user['imie']); ?></p>
                    <p><strong>Nazwisko:</strong> <?php echo htmlspecialchars($user['nazwisko']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                    <p><strong>Data rejestracji:</strong> <?php echo date('d.m.Y', strtotime($user['data_rejestracji'])); ?></p>
                </div>
            </div>
            
            <!-- Removed the Menu card from here -->
        </div>
        
        <div class="col-md-8">
            <div class="tab-content">
                <div class="tab-pane fade show active" id="edit-profile">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Edytuj profil</h5>
                        </div>
                        <div class="card-body">
                            <form method="post" action="">
                                <div class="mb-3">
                                    <label for="imie" class="form-label">Imię</label>
                                    <input type="text" class="form-control" id="imie" name="imie" value="<?php echo htmlspecialchars($user['imie']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="nazwisko" class="form-label">Nazwisko</label>
                                    <input type="text" class="form-control" id="nazwisko" name="nazwisko" value="<?php echo htmlspecialchars($user['nazwisko']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                </div>
                                <button type="submit" name="update_profile" class="btn btn-primary">
                                    Zapisz zmiany
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Aktywuj zakładkę po załadowaniu strony
    const firstTab = document.querySelector('.nav-link');
    if (firstTab) {
        firstTab.click();
    }
    
    // Obsługa zakładek
    const tabLinks = document.querySelectorAll('.nav-link');
    tabLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            if (this.getAttribute('href').startsWith('#')) {
                e.preventDefault();
                
                // Usuń aktywną klasę ze wszystkich zakładek
                tabLinks.forEach(l => {
                    if (l.getAttribute('href').startsWith('#')) {
                        l.classList.remove('active');
                    }
                });
                
                // Dodaj aktywną klasę do klikniętej zakładki
                this.classList.add('active');
                
                // Ukryj wszystkie panele
                const tabPanes = document.querySelectorAll('.tab-pane');
                tabPanes.forEach(pane => {
                    pane.classList.remove('show', 'active');
                });
                
                // Pokaż wybrany panel
                const targetId = this.getAttribute('href');
                const targetPane = document.querySelector(targetId);
                if (targetPane) {
                    targetPane.classList.add('show', 'active');
                }
            }
        });
    });
});
</script>
<?php
include '../main/footer/footer.php';
?>
</body>
</html>
