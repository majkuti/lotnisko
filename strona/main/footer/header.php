<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Determine if we're on the main page or in a subdirectory
$root_path = '';
if (strpos($_SERVER['SCRIPT_NAME'], '/main/') !== false || 
    strpos($_SERVER['SCRIPT_NAME'], '/logowanie/') !== false || 
    strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false) {
    $root_path = '../';
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - MosinAIR' : 'MosinAIR'; ?></title>
    <link rel="stylesheet" href="<?php echo $root_path; ?>dodatki/style.css">
    <?php if (isset($additional_css)): ?>
        <link rel="stylesheet" href="<?php echo $additional_css; ?>">
    <?php endif; ?>
    <link rel="icon" type="image/jpeg" sizes="64x64" href="<?php echo $root_path; ?>zdjecia/logo/logo_mosinair.jpeg">
</head>
<body>
    <header>
        <nav class="top-nav">
            <div class="nav-container">
                <div class="logo">
                    <a href="<?php echo $root_path; ?>index.php">
                        <img src="<?php echo $root_path; ?>zdjecia/logo/logo_mosinair.jpeg" alt="MosinAIR Logo" width="50" height="50">
                    </a>
                </div>
                
                <div class="menu-toggle" id="menu-toggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
                
                <div class="nav-links" id="nav-links">
                    <a href="<?php echo $root_path; ?>index.php">Strona główna</a>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="<?php echo $root_path; ?>main/profil.php">Mój profil</a>
                        <a href="<?php echo $root_path; ?>main/logout.php" class="logout-link">Wyloguj się</a>
                    <?php else: ?>
                        <a href="<?php echo $root_path; ?>logowanie/logowanie.php">Zaloguj się</a>
                        <a href="<?php echo $root_path; ?>logowanie/rejestracja.php">Zarejestruj się</a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>
    <main>

<style>
.top-nav {
    background-color: #333333;
    color: #ffffff;
    padding: 15px 0;
    position: sticky;
    top: 0;
    z-index: 1000;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.nav-container {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 20px;
}

.logo img {
    border-radius: 50%;
    transition: transform 0.3s ease;
}

.logo img:hover {
    transform: scale(1.1);
}

.nav-links {
    display: flex;
    gap: 20px;
}

.nav-links a {
    color: #ffffff;
    text-decoration: none;
    font-weight: 500;
    padding: 8px 12px;
    border-radius: 4px;
    transition: background-color 0.3s ease;
}

.nav-links a:hover {
    background-color: rgba(255, 255, 255, 0.1);
}

.logout-link {
    color: #ff6b6b !important;
}

.logout-link:hover {
    background-color: rgba(255, 107, 107, 0.1) !important;
}

.menu-toggle {
    display: none;
    flex-direction: column;
    justify-content: space-between;
    width: 30px;
    height: 21px;
    cursor: pointer;
}

.menu-toggle span {
    display: block;
    height: 3px;
    width: 100%;
    background-color: #ffffff;
    border-radius: 3px;
    transition: all 0.3s ease;
}

@media (max-width: 768px) {
    .menu-toggle {
        display: flex;
    }
    
    .nav-links {
        position: absolute;
        top: 70px;
        left: 0;
        right: 0;
        background-color: #333333;
        flex-direction: column;
        align-items: center;
        padding: 20px;
        gap: 15px;
        display: none;
        box-shadow: 0 5px 10px rgba(0,0,0,0.1);
    }
    
    .nav-links.active {
        display: flex;
    }
    
    .nav-links a {
        width: 100%;
        text-align: center;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.getElementById('menu-toggle');
    const navLinks = document.getElementById('nav-links');
    
    if (menuToggle && navLinks) {
        menuToggle.addEventListener('click', function() {
            navLinks.classList.toggle('active');
        });
    }
});
</script>
