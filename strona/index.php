<?php
    require_once 'config.php';
    session_start();

    // Pobieranie lotów z bazy danych
    $query = "SELECT l.*, 
              ls1.nazwa as lotnisko_start, 
              ls2.nazwa as lotnisko_koniec 
              FROM loty l
              JOIN lotniska ls1 ON l.id_lotniska_start = ls1.id
              JOIN lotniska ls2 ON l.id_lotniska_koniec = ls2.id";
    $stmt = $db->query($query);
    $loty = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="MosinAIR - Twoje linie lotnicze">
    <meta name="keywords" content="loty, rezerwacje, bilety lotnicze, MosinAIR">
    <title>MosinAIR</title>
    <link rel="stylesheet" href="./dodatki/style.css">
    <link rel="icon" type="image/jpeg" sizes="64x64" href="zdjecia/logo/logo_mosinair.jpeg">
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <a href="index.php">
                    <img src="zdjecia/logo/logo_mosinair.jpeg" alt="MosinAIR Logo" width="50" height="50" id="logoindex">
                </a>
            </div>
            <div class="nav-links">
                <a href="logowanie/logowanie.php">Zaloguj się</a>
                <a href="logowanie/rejestracja.php">Zarejestruj się</a>
            </div>
        </nav>
    </header>
    <main>
        <section class="welcome">
            <h1>Witamy w MosinAIR</h1>
            <p>Znajdź i zarezerwuj swój wymarzony lot</p>
        </section>
        <section class="flights">
            <h2>Dostępne loty</h2>
            <div class="flights-container">
                <?php foreach($loty as $lot): ?>
                    <div class="flight-card">
                        <div class="flight-details">
                            <h3>Lot <?php echo htmlspecialchars($lot['numer_lotu']); ?></h3>
                            <p>Z: <?php echo htmlspecialchars($lot['lotnisko_start']); ?></p>
                            <p>Do: <?php echo htmlspecialchars($lot['lotnisko_koniec']); ?></p>
                            <p>Data wylotu: <?php echo date('d.m.Y H:i', strtotime($lot['data_start'])); ?></p>
                            <p>Data przylotu: <?php echo date('d.m.Y H:i', strtotime($lot['data_koniec'])); ?></p>
                            <p>Status: <?php echo htmlspecialchars($lot['status_lotu']); ?></p>
                            <?php if(isset($lot['cena'])): ?>
                            <p>Cena: <?php echo number_format($lot['cena'], 2); ?> PLN</p>
                            <?php endif; ?>
                        </div>
                        <a href="logowanie/logowanie.php" class="book-button">Zarezerwuj</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
    <?php include './main/footer/footer.php'; ?>
</body>
</html>
