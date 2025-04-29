<?php
    require_once '../config.php';
    session_start();
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $imie = $_POST['imie'];
        $nazwisko = $_POST['nazwisko'];
        $email = $_POST['email'];
        $haslo = $_POST['haslo'];
        $haslo2 = $_POST['haslo2'];
    
        if ($haslo !== $haslo2) {
            $_SESSION['error'] = "Hasła nie są identyczne!";
            header("Location: ../logowanie/rejestracja.php");
            exit();
        }
    
        $hashed_password = password_hash($haslo, PASSWORD_DEFAULT);
    
        try {
            $stmt = $db->prepare("SELECT id FROM uzytkownicy WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                $_SESSION['error'] = "Ten email jest już zarejestrowany!";
                header("Location: ../logowanie/rejestracja.php");
                exit();
            }
    
            $sql = "INSERT INTO uzytkownicy (imie, nazwisko, email, haslo) VALUES (?, ?, ?, ?)";
            $stmt = $db->prepare($sql);
            $stmt->execute([$imie, $nazwisko, $email, $hashed_password]);
    
            $_SESSION['success'] = "Rejestracja zakończona sukcesem! Możesz się teraz zalogować.";
            header("Location: ../logowanie/logowanie.php");
            exit();
    
        } catch(PDOException $e) {
            $_SESSION['error'] = "Błąd podczas rejestracji. Spróbuj ponownie później.";
            header("Location: ../logowanie/rejestracja.php");
            exit();
        }
    }
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="MosinAIR - Rejestracja">
    <title>MosinAIR - Rejestracja</title>
    <link rel="stylesheet" href="../dodatki/style.css">
    <link rel="stylesheet" href="../logowanie/rejestracja.css">
    <link rel="icon" type="image/jpeg" sizes="64x64" href="../zdjecia/logo/logo_mosinair.jpeg">
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <a href="../index.php">
                    <img src="../zdjecia/logo/logo_mosinair.jpeg" alt="MosinAIR Logo" width="50" height="50">
                </a>
            </div>
            <div class="nav-links">
                <a href="../logowanie/logowanie.php">Zaloguj się</a>
                <a href="../logowanie/rejestracja.php" class="active">Zarejestruj się</a>
            </div>
        </nav>
    </header>
    <main>
        <div class="register-container">
            <h1>Rejestracja</h1>
            <?php
                if(isset($_SESSION['error'])) {
                    echo '<p class="error">'.$_SESSION['error'].'</p>';
                    unset($_SESSION['error']);
                }
            ?>
            <form action="../logowanie/rejestracja.php" method="POST" class="register-form">
                <div class="form-group">
                    <label for="imie">Imię:</label>
                    <input type="text" id="imie" name="imie" required placeholder="Wprowadź imię">
                </div>
                <div class="form-group">
                    <label for="nazwisko">Nazwisko:</label>
                    <input type="text" id="nazwisko" name="nazwisko" required placeholder="Wprowadź nazwisko">
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required placeholder="Wprowadź email">
                </div>
                <div class="form-group">
                    <label for="haslo">Hasło:</label>
                    <input type="password" id="haslo" name="haslo" required placeholder="Wprowadź hasło">
                </div>
                <div class="form-group">
                    <label for="haslo2">Powtórz hasło:</label>
                    <input type="password" id="haslo2" name="haslo2" required placeholder="Powtórz hasło">
                </div>
                <button type="submit" class="register-btn">Zarejestruj się</button>
            </form>
            <p class="login-link">Masz już konto? <a href="../logowanie/logowanie.php">Zaloguj się</a></p>
        </div>
    </main>
    <?php
    include '../main/footer/footer.php';
    ?>
</body>
</html>
