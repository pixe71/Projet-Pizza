<?php
// detail_commande.php
require_once 'auth.php';
requireLogin();

$commande_id = $_GET['id'] ?? 0;

$database = new Database();
$db = $database->getConnection();

// Vérifier que la commande appartient au client connecté
$query = "SELECT c.*, cr.date_creneau, cr.heure_debut, cr.heure_fin 
          FROM commandes c 
          JOIN creneaux cr ON c.creneau_id = cr.id 
          WHERE c.id = ? AND c.user_id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$commande_id, $_SESSION['user_id']]);
$commande = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$commande) {
    header('Location: mes_commandes.php');
    exit;
}

// Récupérer les détails des pizzas
$query = "SELECT cd.*, p.nom as pizza_nom 
          FROM commande_details cd 
          JOIN pizzas p ON cd.pizza_id = p.id 
          WHERE cd.commande_id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$commande_id]);
$details = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commande #<?= $commande['id'] ?> - Pizza Truck</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f4f4f4; }
        header { background: #d63031; color: white; padding: 1rem 0; }
        .container { max-width: 800px; margin: 0 auto; padding: 0 20px; }
        .nav { display: flex; justify-content: space-between; align-items: center; }
        .nav h1 { font-size: 1.5rem; }
        .nav a { color: white; text-decoration: none; }
        .main { padding: 40px 20px; }
        .section { background: white; margin-bottom: 30px; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .status-badge { padding: 10px 20px; border-radius: 20px; color: white; font-weight: bold; display: inline-block; margin-bottom: 20px; }
        .status-en_attente { background: #f39c12; }
        .status-confirmee { background: #27ae60; }
        .status-prete { background: #2980b9; }
        .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; }
        .info-item { padding: 15px; background: #f8f9fa; border-radius: 5px; }
        .info-label { font-weight: bold; color: #2d3436; display: block; margin-bottom: 5px; }
        .pizza-item { display: flex; justify-content: between; align-items: center; padding: 15px; border-bottom: 1px solid #eee; }
        .pizza-item:last-child { border-bottom: none; }
        .pizza-info { flex-grow: 1; }
        .pizza-name { font-weight: bold; margin-bottom: 5px; }
        .pizza-quantity { color: #666; }
        .pizza-price { font-weight: bold; color: #d63031; }
        .total { font-size: 1.3em; font-weight: bold; text-align: right; padding: 20px 0; border-top: 2px solid #d63031; margin-top: 20px; }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <nav class="nav">
                <h1>Commande #<?= $commande['id'] ?></h1>
                <a href="mes_commandes.php">← Mes commandes</a>
            </nav>
        </div>
    </header>

    <div class="main container">
        <div class="section">
            <div class="status-badge status-<?= $commande['statut'] ?>">
                <?= ucfirst(str_replace('_', ' ', $commande['statut'])) ?>
            </div>
            
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Date de commande :</span>
                    <?= date('d/m/Y à H:i', strtotime($commande['created_at'])) ?>
                </div>
                <div class="info-item">
                    <span class="info-label">Récupération prévue :</span>
                    <?= date('d/m/Y', strtotime($commande['date_creneau'])) ?><br>
                    de <?= date('H:i', strtotime($commande['heure_debut'])) ?> 
                    à <?= date('H:i', strtotime($commande['heure_fin'])) ?>
                </div>
            </div>
        </div>

        <div class="section">
            <h2>Vos pizzas</h2>
            <?php foreach ($details as $detail): ?>
                <div class="pizza-item">
                    <div class="pizza-info">
                        <div class="pizza-name"><?= htmlspecialchars($detail['pizza_nom']) ?></div>
                        <div class="pizza-quantity">Quantité : <?= $detail['quantite'] ?> × <?= number_format($detail['prix_unitaire'], 2) ?>€</div>
                    </div>
                    <div class="pizza-price">
                        <?= number_format($detail['prix_unitaire'] * $detail['quantite'], 2) ?>€
                    </div>
                </div>
            <?php endforeach; ?>
            
            <div class="total">
                Total : <?= number_format($commande['total'], 2) ?>€
                <br><small style="font-weight: normal; color: #666;">Paiement à effectuer sur place</small>
            </div>
        </div>
    </div>
</body>
</html>
