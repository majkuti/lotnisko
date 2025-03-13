<?php
    require_once '../config.php';
    session_start();



    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = $_POST['email'];
        $haslo = $_POST['haslo'];
    
        try {
            $stmt = $db->prepare("SELECT * FROM uzytkownicy WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
    
            if ($user && password_verify($haslo, $user['haslo'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['imie'] = $user['imie'];
                $_SESSION['user_email'] = $user['email'];
                
                header("Location: ../main/loginuser.php");
                exit();
            } else {
                $_SESSION['error'] = "Nieprawidłowy email lub hasło";
                header("Location: logowanie.php");
                exit();
            }
    
        } catch(PDOException $e) {
            $_SESSION['error'] = "Błąd podczas logowania. Spróbuj ponownie później.";
            header("Location: logowanie.php");
            exit();
        }
    }
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="MosinAIR - Logowanie">
    <title>MosinAIR - Logowanie</title>
    <link rel="stylesheet" href="../dodatki/style.css">
    <link rel="stylesheet" href="../logowanie/login.css">
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
                <a href="../logowanie/logowanie.php" class="active">Zaloguj się</a>
                <a href="../logowanie/rejestracja.php">Zarejestruj się</a>
            </div>
        </nav>
    </header>
    <main>
        <div class="login-container">
            <h1>Logowanie</h1>
            <?php
                if(isset($_SESSION['error'])) {
                    echo '<p class="error">'.$_SESSION['error'].'</p>';
                    unset($_SESSION['error']);
                }
            ?>
            <form action="../logowanie/logowanie.php" method="POST" class="login-form">
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required placeholder="Wprowadź email">
                </div>
                <div class="form-group">
                    <label for="haslo">Hasło:</label>
                    <input type="password" id="haslo" name="haslo" required placeholder="Wprowadź hasło">
                </div>
                <button type="submit" class="login-btn">Zaloguj się</button>
            </form>
            <p class="register-link">Nie masz konta? <a href="../logowanie/rejestracja.php">Zarejestruj się</a></p>
        </div>
        <div class="admin-login">
    <hr class="divider">
    <p>Panel administratora</p>
    <a href="../main/admin/adminlogin.php" class="admin-btn">Zaloguj jako administrator</a>
</div>
    </main>
    <footer>
        <p>&copy; <?php echo date('Y'); ?> MosinAIR. Wszelkie prawa zastrzeżone.</p>
    </footer>
</body>
</html>
