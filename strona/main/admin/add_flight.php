<?php
require_once '../../config.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: adminlogin.php');
    exit();
}

// Fetch airports and aircraft
$query = "SELECT id, nazwa FROM lotniska";
$stmt = $db->query($query);
$lotniska = $stmt->fetchAll();

$query_samoloty = "SELECT id, model FROM samoloty";
$stmt_samoloty = $db->query($query_samoloty);
$samoloty = $stmt_samoloty->fetchAll();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $numer_lotu = $_POST['numer_lotu'];
    $id_lotniska_start = $_POST['id_lotniska_start'];
    $id_lotniska_koniec = $_POST['id_lotniska_koniec'];
    $data_start = $_POST['data_start'];
    $data_koniec = $_POST['data_koniec'];
    $id_samolotu = $_POST['id_samolotu'];
    $status_lotu = 'aktywny';

    $stmt = $db->prepare("INSERT INTO loty (numer_lotu, id_lotniska_start, id_lotniska_koniec, data_start, data_koniec, status_lotu, id_samolotu) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$numer_lotu, $id_lotniska_start, $id_lotniska_koniec, $data_start, $data_koniec, $status_lotu, $id_samolotu]);

    header("Location: adminpage.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Dodaj nowy lot - MosinAIR</title>
    <link rel="stylesheet" href="../../dodatki/style.css">
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <div class="admin-container">
        <h2>Dodaj nowy lot</h2>
        <form method="POST" class="add-flight-form">
            <div class="form-group">
                <label for="numer_lotu">Numer lotu:</label>
                <input type="text" id="numer_lotu" name="numer_lotu" required>
            </div>
            
            <div class="form-group">
                <label for="id_lotniska_start">Lotnisko startowe:</label>
                <select id="id_lotniska_start" name="id_lotniska_start" required>
                    <?php foreach($lotniska as $lotnisko): ?>
                        <option value="<?php echo $lotnisko['id']; ?>">
                            <?php echo htmlspecialchars($lotnisko['nazwa']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="id_lotniska_koniec">Lotnisko docelowe:</label>
                <select id="id_lotniska_koniec" name="id_lotniska_koniec" required>
                    <?php foreach($lotniska as $lotnisko): ?>
                        <option value="<?php echo $lotnisko['id']; ?>">
                            <?php echo htmlspecialchars($lotnisko['nazwa']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="data_start">Data i czas wylotu:</label>
                <input type="datetime-local" id="data_start" name="data_start" required>
            </div>

            <div class="form-group">
                <label for="data_koniec">Data i czas przylotu:</label>
                <input type="datetime-local" id="data_koniec" name="data_koniec" required>
            </div>

            <button type="submit" class="admin-btn">Dodaj lot</button>
            <a href="adminpage.php" class="admin-btn">Powrót</a>
        </form>
    </div>
    <div class="form-group">
    <label for="id_samolotu">Samolot:</label>
    <select id="id_samolotu" name="id_samolotu" required>
        <?php foreach($samoloty as $samolot): ?>
            <option value="<?php echo $samolot['id']; ?>">
                <?php echo htmlspecialchars($samolot['model']); ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>
</body>
</html>
