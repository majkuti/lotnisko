<?php
    require_once '../config.php';
    session_start();

    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../logowanie/logowanie.php');
        exit();
    }

    // Fetch flights from database
    $query = "SELECT l.*, 
              ls1.nazwa as lotnisko_start, 
              ls2.nazwa as lotnisko_koniec 
              FROM loty l
              JOIN lotniska ls1 ON l.id_lotniska_start = ls1.id
              JOIN lotniska ls2 ON l.id_lotniska_koniec = ls2.id";
    $stmt = $db->query($query);
    $loty = $stmt->fetchAll();

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $email = $_POST['email'];
        $haslo = $_POST['haslo'];
        
        $query = "SELECT id, email, imie FROM uzytkownicy WHERE email = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($haslo, $user['haslo'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['imie'] = $user['imie'];
            header('Location: ../main/loginuser.php');
            exit();
        } else {
            header('Location: logowanie.php?error=1');
            exit();
        }
    }
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="MosinAIR - Twoje linie lotnicze">
    <meta name="keywords" content="loty, rezerwacje, bilety lotnicze, MosinAIR">
    <title>MosinAIR - Panel użytkownika</title>
    <link rel="stylesheet" href="../dodatki/style.css">
    <link rel="icon" type="image/jpeg" sizes="64x64" href="../zdjecia/logo/logo_mosinair.jpeg">
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <a href="loginuser.php">
                    <img src="../zdjecia/logo/logo_mosinair.jpeg" alt="MosinAIR Logo" width="50" height="50">
                </a>
            </div>
            <div class="nav-links">
                <a href="profil.php">Mój profil</a>
                <a href="moje_rezerwacje.php">Moje rezerwacje</a>
                <a href="../main/logout.php">Wyloguj się</a>
            </div>
        </nav>
    </header>
    <main>
        <section class="welcome">
        <h1>Witaj, <?php echo isset($_SESSION['imie']) ? htmlspecialchars($_SESSION['imie']) : 'Użytkowniku'; ?></h1>
            <p>Znajdź i zarezerwuj swój wymarzony lot</p>
            <section class="flights">
            <h2>Dostępne loty</h2>
            <div class="flights-container">
            <?php foreach($loty as $lot): ?>
<div class="flights-container">
    <?php foreach($loty as $lot): ?>
        <div class="flight-card">
            <div class="flight-details">
                <h3>Lot <?php echo htmlspecialchars($lot['numer_lotu']); ?></h3>
                <p>Z: <?php echo htmlspecialchars($lot['lotnisko_start']); ?></p>
                <p>Do: <?php echo htmlspecialchars($lot['lotnisko_koniec']); ?></p>
                <p>Data wylotu: <?php echo date('d.m.Y H:i', strtotime($lot['data_start'])); ?></p>
                <p>Data przylotu: <?php echo date('d.m.Y H:i', strtotime($lot['data_koniec'])); ?></p>
                <p>Status: <?php 
                                if (empty($lot['status_lotu'])) {
                                    echo "Aktywny"; // Default value if status is empty
                                } else {
                                    echo htmlspecialchars($lot['status_lotu']);
                                }
                            ?></p>
                <?php if(isset($lot['cena'])): ?>
                <p>Cena: <?php echo number_format($lot['cena'], 2); ?> PLN</p>
                <?php endif; ?>
            </div>
            <a href="rezerwacja.php?id=<?php echo $lot['id']; ?>" class="book-button">Zarezerwuj</a>
        </div>
    <?php endforeach; ?>
</div>

        <?php if(in_array(strtolower($lot['status_lotu']), ['aktywny', 'dostępny', 'otwarty', 'planowany'])): ?>

            <a href="rezerwacja.php?id=<?php echo $lot['id']; ?>" class="book-button">Zarezerwuj</a>
        <?php else: ?>
            <button class="book-button disabled" disabled>Lot niedostępny</button>
        <?php endif; ?>
    </div>
<?php endforeach; ?>

            </div>
        </section>
    </main>
    <?php include './footer/footer.php'; ?>
</body>
</html>
