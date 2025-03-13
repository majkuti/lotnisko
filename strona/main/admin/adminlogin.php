<?php
    require_once '../../config.php';
    session_start();

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = $_POST['email'];
        $haslo = $_POST['haslo'];
    
        try {
            $stmt = $db->prepare("SELECT * FROM administratorzy WHERE email = ?");
            $stmt->execute([$email]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if ($admin && password_verify($haslo, $admin['haslo'])) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_email'] = $admin['email'];
                header("Location: adminpage.php");
                exit();
            } else {
                $_SESSION['error'] = "Nieprawidłowe dane logowania administratora";
                header("Location: adminlogin.php");
                exit();
            }
    
        } catch(PDOException $e) {
            $_SESSION['error'] = "Błąd podczas logowania: " . $e->getMessage();
            header("Location: adminlogin.php");
            exit();
        }
    }

?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="MosinAIR - Panel Administratora">
    <title>MosinAIR - Logowanie Administratora</title>
    <link rel="stylesheet" href="../../dodatki/style.css">
    <link rel="stylesheet" href="../admin/admin.css">
    <link rel="icon" type="image/jpeg" sizes="64x64" href="../zdjecia/logo/logo_mosinair.jpeg">
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <a href="../../index.php">
                    <img src="../../zdjecia/logo/logo_mosinair.jpeg" alt="MosinAIR Logo" width="50" height="50">
                </a>
            </div>
            <div class="nav-links">
                <a href="../../index.php">Strona główna</a>
            </div>
        </nav>
    </header>
    <main>
        <div class="login-container">
            <h1>Panel Administratora</h1>
            <?php
                if(isset($_SESSION['error'])) {
                    echo '<p class="error">'.$_SESSION['error'].'</p>';
                    unset($_SESSION['error']);
                }
            ?>
            <form action="adminlogin.php" method="POST" class="login-form">
                <div class="form-group">
                    <label for="email">Email administratora:</label>
                    <input type="email" id="email" name="email" required placeholder="Wprowadź email">
                </div>
                <div class="form-group">
                    <label for="haslo">Hasło:</label>
                    <input type="password" id="haslo" name="haslo" required placeholder="Wprowadź hasło">
                </div>
                <button type="submit" class="login-btn">Zaloguj jako administrator</button>
            </form>
        </div>
    </main>
    <footer>
        <p>&copy; <?php echo date('Y'); ?> MosinAIR. Wszelkie prawa zastrzeżone.</p>
    </footer>
</body>
</html>
