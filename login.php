<?php
// login.php
require_once 'auth.php';

if (isLoggedIn()) {
    header('Location: commander.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pizza Truck - Connexion</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f4f4f4; }
        .container { max-width: 400px; margin: 50px auto; padding: 20px; background: white; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #d63031; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="email"], input[type="password"], input[type="tel"] { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        .btn { width: 100%; padding: 12px; background: #d63031; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        .btn:hover { background: #b71c1c; }
        .form-toggle { text-align: center; margin-top: 15px; }
        .form-toggle a { color: #d63031; text-decoration: none; }
        .error { color: red; margin-bottom: 15px; text-align: center; }
        .success { color: green; margin-bottom: 15px; text-align: center; }
        #register-form { display: none; }
    </style>
</head>
<body>
    <div class="container">
        <?php if (isset($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success"><?= $_SESSION['success'] ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <!-- Formulaire de connexion -->
        <div id="login-form">
            <h2>Connexion</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Email :</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Mot de passe :</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" name="login" class="btn">Se connecter</button>
            </form>
            <div class="form-toggle">
                <a href="#" onclick="toggleForm()">Pas encore inscrit ? S'inscrire</a>
            </div>
        </div>

        <!-- Formulaire d'inscription -->
        <div id="register-form">
            <h2>Inscription</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Nom :</label>
                    <input type="text" name="nom" required>
                </div>
                <div class="form-group">
                    <label>Prénom :</label>
                    <input type="text" name="prenom" required>
                </div>
                <div class="form-group">
                    <label>Email :</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Téléphone :</label>
                    <input type="tel" name="telephone" required>
                </div>
                <div class="form-group">
                    <label>Mot de passe :</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" name="register" class="btn">S'inscrire</button>
            </form>
            <div class="form-toggle">
                <a href="#" onclick="toggleForm()">Déjà inscrit ? Se connecter</a>
            </div>
        </div>
    </div>

    <script>
        function toggleForm() {
            const loginForm = document.getElementById('login-form');
            const registerForm = document.getElementById('register-form');
            
            if (loginForm.style.display === 'none') {
                loginForm.style.display = 'block';
                registerForm.style.display = 'none';
            } else {
                loginForm.style.display = 'none';
                registerForm.style.display = 'block';
            }
        }
    </script>
</body>
</html>
