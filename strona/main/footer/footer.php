<?php
// Check if we're on the homepage
$isHomepage = false;
$currentFile = $_SERVER['SCRIPT_NAME'];
if (strpos($currentFile, 'index.php') !== false && !strpos($currentFile, '/main/') && !strpos($currentFile, '/admin/') && !strpos($currentFile, '/logowanie/')) {
    $isHomepage = true;
}
?>

<footer class="site-footer">
    <div class="footer-container">
        <div class="footer-section">
            <h3>MosinAIR</h3>
            <div class="footer-logo">
                <a href="<?php echo $isHomepage ? '#' : '../index.php'; ?>">
                    <img src="<?php echo $isHomepage ? 'zdjecia/logo/logo_mosinair.jpeg' : '../zdjecia/logo/logo_mosinair.jpeg'; ?>" alt="MosinAIR Logo" width="60" height="60">
                </a>
            </div>
            <p>Twoje zaufane linie lotnicze od 2020 roku.</p>
        </div>
        
        <div class="footer-section">
            <h3>Dane firmy</h3>
            <p>MosinAIR Sp. z o.o.</p>
            <p>ul. Lotnicza 123</p>
            <p>00-001 Warszawa</p>
            <p>NIP: 123-456-78-90</p>
            <p>REGON: 123456789</p>
            <p>KRS: 0000123456</p>
        </div>
        
        <div class="footer-section">
            <h3>Kontakt</h3>
            <p><i class="fas fa-phone"></i> +48 123 456 789</p>
            <p><i class="fas fa-envelope"></i> kontakt@mosinair.pl</p>
            <p><i class="fas fa-clock"></i> Pon-Pt: 8:00-20:00</p>
            <p><i class="fas fa-clock"></i> Sob-Nd: 10:00-18:00</p>
        </div>
        
        <div class="footer-section">
            <h3>Informacje prawne</h3>
            <p><a href="<?php echo $isHomepage ? 'polityka-prywatnosci.php' : '../polityka-prywatnosci.php'; ?>">Polityka prywatności</a></p>
            <p><a href="<?php echo $isHomepage ? 'regulamin.php' : '../regulamin.php'; ?>">Regulamin</a></p>
            <p><a href="<?php echo $isHomepage ? 'cookies.php' : '../cookies.php'; ?>">Polityka cookies</a></p>
            <p><a href="<?php echo $isHomepage ? 'rodo.php' : '../rodo.php'; ?>">Informacje RODO</a></p>
            <?php if (!$isHomepage): ?>
            <p><a href="../index.php">Strona główna</a></p>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="footer-bottom">
        <div class="footer-bottom-container">
            <p>Informacja o przetwarzaniu danych osobowych: Administratorem Twoich danych osobowych jest MosinAIR Sp. z o.o. Dane są przetwarzane w celu świadczenia usług rezerwacji lotów oraz w celach marketingowych. Masz prawo dostępu do swoich danych, ich sprostowania, usunięcia lub ograniczenia przetwarzania. Więcej informacji znajdziesz w naszej <a href="<?php echo $isHomepage ? 'polityka-prywatnosci.php' : '../polityka-prywatnosci.php'; ?>">Polityce prywatności</a>.</p>
            <p>&copy; <?php echo date('Y'); ?> MosinAIR. Wszelkie prawa zastrzeżone.</p>
        </div>
    </div>
</footer>

<style>
.site-footer {
    background-color: #333333;
    color: #ffffff;
    padding: 40px 0 0 0;
    margin-top: 60px;
    font-size: 14px;
}

.footer-container {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    padding: 0 20px 30px 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.footer-section {
    width: 23%;
    margin-bottom: 20px;
}

.footer-section h3 {
    color: #ffffff;
    font-size: 18px;
    margin-bottom: 15px;
    font-weight: 600;
    position: relative;
    padding-bottom: 10px;
}

.footer-section h3:after {
    content: '';
    position: absolute;
    left: 0;
    bottom: 0;
    width: 40px;
    height: 2px;
    background-color: #ffffff;
}

.footer-logo {
    margin: 15px 0;
}

.footer-logo img {
    border-radius: 50%;
    transition: transform 0.3s ease;
}

.footer-logo img:hover {
    transform: scale(1.1);
}

.footer-section p {
    margin-bottom: 10px;
    line-height: 1.6;
}

.footer-section a {
    color: #ffffff;
    text-decoration: none;
    transition: color 0.3s ease;
}

.footer-section a:hover {
    color: #cccccc;
    text-decoration: underline;
}

.footer-bottom {
    background-color: rgba(0, 0, 0, 0.2);
    padding: 20px 0;
}

.footer-bottom-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    text-align: center;
}

.footer-bottom p {
    margin-bottom: 10px;
    font-size: 12px;
    line-height: 1.6;
    color: rgba(255, 255, 255, 0.7);
}

.footer-bottom a {
    color: rgba(255, 255, 255, 0.9);
    text-decoration: underline;
}

@media (max-width: 992px) {
    .footer-section {
        width: 48%;
    }
}

@media (max-width: 768px) {
    .footer-section {
        width: 100%;
    }
    
    .footer-container {
        flex-direction: column;
    }
}
</style>
