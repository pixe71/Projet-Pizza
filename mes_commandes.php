<?php
// mes_commandes.php
require_once 'auth.php';
requireLogin();

$database = new Database();
$db = $database->getConnection();

// Récupérer les commandes du client
$query = "SELECT c.*, cr.date_creneau, cr.heure_debut, cr.heure_fin 
          FROM commandes c 
          JOIN creneaux cr ON c.creneau_id = cr.id 
          WHERE c.user_id = ? 
          ORDER BY c.created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Commandes - Pizza Truck</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f4f4f4; }
        header { background: #d63031; color: white; padding: 1rem 0; }
        .container { max-width: 1000px; margin: 0 auto; padding: 0 20px; }
        .nav { display: flex; justify-content: space-between; align-items: center; }
        .nav h1 { font-size: 1.5rem; }
        .nav a { color: white; text-decoration: none; margin-left: 20px; }
        .main { padding: 40px 20px; }
        .commande-card { background: white; margin-bottom: 20px; padding: 25px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .commande-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 15px; }
        .commande-id { font-size: 1.1em; font-weight: bold; }
        .status-badge { padding: 6px 12px; border-radius: 15px; color: white; font-size: 0.9em; font-weight: bold; }
        .status-en_attente { background: #f39c12; }
        .status-confirmee { background: #27ae60; }
        .status-prete { background: #2980b9; }
        .status-annulee { background: #e74c3c; }
        .commande-info { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 15px; }
        .info-item { background: #f8f9fa; padding: 12px; border-radius: 5px; }
        .info-label { font-weight: bold; color: #2d3436; display: block; margin-bottom: 5px; }
        .btn { background: #d63031; color: white; padding: 10px 20px; border: none; border-radius: 5px; text-decoration: none; display: inline-block; }
        .btn:hover { background: #b71c1c; }
        .no-commandes { text-align: center; padding: 40px; background: white; border-radius: 10px; color: #666; }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <nav class="nav">
                <h1>🍕 Mes Commandes</h1>
                <div>
                    <a href="commander.php">Nouvelle commande</a>
                    <a href="logout.php">Déconnexion</a>
                </div>
            </nav>
        </div>
    </header>

    <div class="main container">
        <?php if (empty($commandes)): ?>
            <div class="no-commandes">
                <h2>Aucune commande</h2>
                <p>Vous n'avez pas encore passé de commande.</p>
                <a href="commander.php" class="btn">Passer ma première commande</a>
            </div>
        <?php else: ?>
            <?php foreach ($commandes as $commande): ?>
                <div class="commande-card">
                    <div class="commande-header">
                        <div class="commande-id">Commande #<?= $commande['id'] ?></div>
                        <div class="status-badge status-<?= $commande['statut'] ?>">
                            <?= ucfirst(str_replace('_', ' ', $commande['statut'])) ?>
                        </div>
                    </div>
                    
                    <div class="commande-info">
                        <div class="info-item">
                            <span class="info-label">Date de commande :</span>
                            <?= date('d/m/Y H:i', strtotime($commande['created_at'])) ?>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Récupération prévue :</span>
                            <?= date('d/m/Y', strtotime($commande['date_creneau'])) ?><br>
                            <?= date('H:i', strtotime($commande['heure_debut'])) ?> - <?= date('H:i', strtotime($commande['heure_fin'])) ?>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Total :</span>
                            <?= number_format($commande['total'], 2) ?>€
                        </div>
                    </div>
                    
                    <a href="detail_commande.php?id=<?= $commande['id'] ?>" class="btn">Voir les détails</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
