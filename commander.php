<?php
// commander.php
require_once 'auth.php';
requireLogin();

$database = new Database();
$db = $database->getConnection();

// Récupérer les pizzas disponibles
$query = "SELECT * FROM pizzas WHERE disponible = 1";
$stmt = $db->prepare($query);
$stmt->execute();
$pizzas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les créneaux disponibles
$query = "SELECT * FROM creneaux WHERE date_creneau >= CURDATE() AND actif = 1 AND pizzas_commandees < max_pizzas ORDER BY date_creneau, heure_debut";
$stmt = $db->prepare($query);
$stmt->execute();
$creneaux = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Traitement de la commande
if (isset($_POST['commander'])) {
    $creneau_id = $_POST['creneau_id'];
    $total = 0;
    $nb_pizzas = 0;
    
    // Calculer le total et le nombre de pizzas
    foreach ($_POST['pizzas'] as $pizza_id => $quantite) {
        if ($quantite > 0) {
            $query = "SELECT prix FROM pizzas WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$pizza_id]);
            $pizza = $stmt->fetch(PDO::FETCH_ASSOC);
            $total += $pizza['prix'] * $quantite;
            $nb_pizzas += $quantite;
        }
    }
    
    if ($nb_pizzas > 0) {
        // Vérifier si le créneau peut encore accepter des commandes
        $query = "SELECT * FROM creneaux WHERE id = ? AND (pizzas_commandees + ?) <= max_pizzas";
        $stmt = $db->prepare($query);
        $stmt->execute([$creneau_id, $nb_pizzas]);
        $creneau = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($creneau) {
            $db->beginTransaction();
            try {
                // Créer la commande
                $query = "INSERT INTO commandes (user_id, creneau_id, total) VALUES (?, ?, ?)";
                $stmt = $db->prepare($query);
                $stmt->execute([$_SESSION['user_id'], $creneau_id, $total]);
                $commande_id = $db->lastInsertId();
                
                // Ajouter les détails de la commande
                foreach ($_POST['pizzas'] as $pizza_id => $quantite) {
                    if ($quantite > 0) {
                        $query = "SELECT prix FROM pizzas WHERE id = ?";
                        $stmt = $db->prepare($query);
                        $stmt->execute([$pizza_id]);
                        $pizza = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        $query = "INSERT INTO commande_details (commande_id, pizza_id, quantite, prix_unitaire) VALUES (?, ?, ?, ?)";
                        $stmt = $db->prepare($query);
                        $stmt->execute([$commande_id, $pizza_id, $quantite, $pizza['prix']]);
                    }
                }
                
                // Mettre à jour le nombre de pizzas commandées pour ce créneau
                $query = "UPDATE creneaux SET pizzas_commandees = pizzas_commandees + ? WHERE id = ?";
                $stmt = $db->prepare($query);
                $stmt->execute([$nb_pizzas, $creneau_id]);
                
                $db->commit();
                $success = "Commande passée avec succès ! Total: " . number_format($total, 2) . "€";
            } catch (Exception $e) {
                $db->rollback();
                $error = "Erreur lors de la commande.";
            }
        } else {
            $error = "Ce créneau est complet.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commander - Pizza Truck</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f4f4f4; }
        header { background: #d63031; color: white; padding: 1rem 0; }
        .container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
        .nav { display: flex; justify-content: space-between; align-items: center; }
        .nav h1 { font-size: 1.5rem; }
        .nav a { color: white; text-decoration: none; margin-left: 20px; }
        .main { padding: 40px 20px; }
        .section { background: white; margin-bottom: 30px; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .pizzas-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .pizza-card { border: 1px solid #ddd; border-radius: 8px; padding: 20px; }
        .pizza-card h3 { color: #d63031; margin-bottom: 10px; }
        .pizza-card p { color: #666; margin-bottom: 15px; }
        .pizza-price { font-size: 1.2em; font-weight: bold; color: #2d3436; }
        .pizza-quantity { display: flex; align-items: center; margin-top: 15px; }
        .pizza-quantity label { margin-right: 10px; }
        .pizza-quantity input { width: 60px; padding: 5px; text-align: center; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: bold; }
        .form-group select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        .btn { background: #d63031; color: white; padding: 12px 24px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        .btn:hover { background: #b71c1c; }
        .btn:disabled { background: #ccc; cursor: not-allowed; }
        .alert { padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .total { font-size: 1.2em; font-weight: bold; text-align: right; margin-top: 20px; }
        .creneau-info { background: #e3f2fd; padding: 10px; border-radius: 5px; margin-bottom: 10px; }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <nav class="nav">
                <h1>🍕 Pizza Truck</h1>
                <div>
                    <span>Bonjour <?= $_SESSION['user_name'] ?></span>
                    <a href="mes_commandes.php">Mes commandes</a>
                    <a href="logout.php">Déconnexion</a>
                </div>
            </nav>
        </div>
    </header>

    <div class="main container">
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" id="commande-form">
            <div class="section">
                <h2>Choisir un créneau horaire</h2>
                <div class="form-group">
                    <label>Créneau de récupération :</label>
                    <select name="creneau_id" required>
                        <option value="">-- Choisir un créneau --</option>
                        <?php foreach ($creneaux as $creneau): ?>
                            <option value="<?= $creneau['id'] ?>">
                                <?= date('d/m/Y', strtotime($creneau['date_creneau'])) ?> 
                                de <?= date('H:i', strtotime($creneau['heure_debut'])) ?> 
                                à <?= date('H:i', strtotime($creneau['heure_fin'])) ?>
                                (<?= $creneau['pizzas_commandees'] ?>/<?= $creneau['max_pizzas'] ?> pizzas)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="section">
                <h2>Choisir vos pizzas</h2>
                <div class="pizzas-grid">
                    <?php foreach ($pizzas as $pizza): ?>
                        <div class="pizza-card">
                            <h3><?= htmlspecialchars($pizza['nom']) ?></h3>
                            <p><?= htmlspecialchars($pizza['description']) ?></p>
                            <div class="pizza-price"><?= number_format($pizza['prix'], 2) ?>€</div>
                            <div class="pizza-quantity">
                                <label>Quantité :</label>
                                <input type="number" name="pizzas[<?= $pizza['id'] ?>]" min="0" max="10" value="0" class="quantity-input" data-prix="<?= $pizza['prix'] ?>">
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="section">
                <div class="total">
                    Total : <span id="total-prix">0.00</span>€
                </div>
                <button type="submit" name="commander" class="btn" id="btn-commander" disabled>
                    Passer la commande (Paiement sur place)
                </button>
            </div>
        </form>
    </div>

    <script>
        const quantityInputs = document.querySelectorAll('.quantity-input');
        const totalPrix = document.getElementById('total-prix');
        const btnCommander = document.getElementById('btn-commander');

        function updateTotal() {
            let total = 0;
            let hasItems = false;
            
            quantityInputs.forEach(input => {
                const quantity = parseInt(input.value) || 0;
                const prix = parseFloat(input.dataset.prix);
                total += quantity * prix;
                if (quantity > 0) hasItems = true;
            });
            
            totalPrix.textContent = total.toFixed(2);
            btnCommander.disabled = !hasItems;
        }

        quantityInputs.forEach(input => {
            input.addEventListener('input', updateTotal);
        });

        updateTotal();
    </script>
</body>
</html>
