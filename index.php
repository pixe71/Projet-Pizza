<?php
// index.php
require_once 'auth.php';

if (isLoggedIn()) {
    if (isAdmin()) {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: commander.php');
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pizza Truck - Commande en ligne</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: linear-gradient(135deg, #d63031, #e17055); min-height: 100vh; }
        .hero { display: flex; align-items: center; justify-content: center; min-height: 100vh; text-align: center; color: white; }
        .hero-content { max-width: 600px; padding: 40px 20px; }
        .hero h1 { font-size: 3rem; margin-bottom: 20px; text-shadow: 2px 2px 4px rgba(0,0,0,0.3); }
        .hero p { font-size: 1.2rem; margin-bottom: 30px; line-height: 1.6; }
        .btn { display: inline-block; background: white; color: #d63031; padding: 15px 30px; text-decoration: none; border-radius: 25px; font-weight: bold; font-size: 1.1rem; transition: transform 0.3s; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.3); }
        .features { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 30px; margin-top: 50px; }
        .feature { background: rgba(255,255,255,0.1); padding: 20px; border-radius: 10px; backdrop-filter: blur(10px); }
        .feature h3 { margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="hero">
        <div class="hero-content">
            <h1>🍕 Pizza Truck</h1>
            <p>Commandez vos pizzas préférées en ligne et venez les récupérer au créneau qui vous convient le mieux !</p>
            <a href="login.php" class="btn">Commencer ma commande</a>
            
            <div class="features">
                <div class="feature">
                    <h3>📅 Créneaux flexibles</h3>
                    <p>Choisissez l'heure qui vous convient</p>
                </div>
                <div class="feature">
                    <h3>🍕 Pizzas fraîches</h3>
                    <p>Préparées à la commande</p>
                </div>
                <div class="feature">
                    <h3>💳 Paiement sur place</h3>
                    <p>Pas de paiement en ligne requis</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
