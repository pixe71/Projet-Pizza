<?php
// admin/details_commande.php
require_once '../auth.php';
requireAdmin();

$commande_id = $_GET['id'] ?? 0;

$database = new Database();
$db = $database->getConnection();

// Récupérer les détails de la commande
$query = "SELECT c.*, u.nom, u.prenom, u.email, u.telephone, 
          cr.date_creneau, cr.heure_debut, cr.heure_fin
          FROM commandes c
          JOIN users u ON c.user_id = u.id
          JOIN creneaux cr ON c.creneau_id = cr.id
          WHERE c.id = ?";

$stmt = $db->prepare($query);
$stmt->execute([$commande_id]);
$commande = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$commande) {
    header('Location: dashboard.php');
    exit;
}

// Récupérer les détails des pizzas - CORRIGÉ
$query = "SELECT cd.*, p.nom as produit_nom, p.description
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
    <title>Commande #<?= $commande['id'] ?> - Pizza Truck Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f6fa;
            color: #2c3e50;
            line-height: 1.6;
        }
        
        .header {
            background: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .header h1 {
            font-size: 1.8rem;
            color: #2c3e50;
        }
        
        .header-buttons {
            display: flex;
            gap: 1rem;
            align-items: center;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        
        .card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border: 1px solid #e1e8ed;
        }
        
        .card h2 {
            color: #2c3e50;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.4rem;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 0.8rem 0;
            border-bottom: 1px solid #f1f2f6;
        }
        
        .info-label {
            font-weight: 600;
            color: #7f8c8d;
        }
        
        .info-value {
            font-weight: 500;
            color: #2c3e50;
        }
        
        .status {
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-size: 0.9rem;
            font-weight: 600;
            display: inline-block;
        }
        
        .status.en_attente {
            background: #fff3cd;
            color: #856404;
        }
        
        .status.confirmee {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .status.prete {
            background: #d4edda;
            color: #155724;
        }
        
        .status.annulee {
            background: #f8d7da;
            color: #721c24;
        }
        
        .products-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .product-item {
            display: grid;
            grid-template-columns: 1fr auto auto;
            align-items: center;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 10px;
            gap: 1rem;
        }
        
        .product-info h3 {
            color: #2c3e50;
            margin-bottom: 0.3rem;
        }
        
        .product-description {
            color: #7f8c8d;
            font-size: 0.9rem;
        }
        
        .product-quantity {
            font-size: 1.1rem;
            font-weight: 600;
            color: #e67e22;
            padding: 0.3rem 0.8rem;
            background: white;
            border-radius: 20px;
        }
        
        .product-price {
            font-size: 1.2rem;
            font-weight: 700;
            color: #27ae60;
        }
        
        .total-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem;
            background: #2c3e50;
            color: white;
            border-radius: 10px;
            margin-top: 1rem;
            font-size: 1.2rem;
            font-weight: 600;
        }
        
        .total-amount {
            font-size: 1.5rem;
            font-weight: 700;
        }
        
        .actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        
        .btn-success {
            background: #27ae60;
            color: white;
        }
        
        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        
        .btn-warning {
            background: #f39c12;
            color: white;
        }
        
        .btn {
            background: #3498db;
            color: white;
        }
        
        .contact-info {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .contact-link {
            color: #3498db;
            text-decoration: none;
            font-weight: 500;
        }
        
        .contact-link:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .product-item {
                grid-template-columns: 1fr;
                text-align: center;
                gap: 0.5rem;
            }
            
            .actions {
                flex-direction: column;
            }
            
            .btn {
                justify-content: center;
            }
        }
        
        @media print {
            .actions, .header-buttons {
                display: none;
            }
            
            body {
                background: white;
            }
            
            .card {
                box-shadow: none;
                border: 1px solid #ddd;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🍕 Détails de la commande #<?= $commande['id'] ?></h1>
        <div class="header-buttons">
            <a href="commandes_jour.php" class="btn">← Retour aux commandes</a>
            <a href="dashboard.php" class="btn">🏠 Dashboard</a>
        </div>
    </div>

    <div class="container">
        <!-- Informations générales -->
        <div class="card">
            <h2>📋 Informations générales</h2>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Numéro:</span>
                    <span class="info-value">#<?= $commande['id'] ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Date:</span>
                    <span class="info-value">
                        <?= $commande['created_at'] ? date('d/m/Y à H:i', strtotime($commande['created_at'])) : 'Non définie' ?>
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Statut:</span>
                    <span class="info-value">
                        <span class="status <?= $commande['statut'] ?>">
                            <?= ucfirst(str_replace('_', ' ', $commande['statut'])) ?>
                        </span>
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Total:</span>
                    <span class="info-value" style="font-size: 1.2rem; font-weight: 700; color: #27ae60;">
                        <?= number_format($commande['total'], 2) ?>€
                    </span>
                </div>
            </div>
        </div>

        <!-- Informations client -->
        <div class="card">
            <h2>👤 Informations client</h2>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Nom:</span>
                    <span class="info-value"><?= htmlspecialchars($commande['prenom'] . ' ' . $commande['nom']) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Email:</span>
                    <span class="info-value">
                        <a href="mailto:<?= htmlspecialchars($commande['email']) ?>" class="contact-link">
                            <?= htmlspecialchars($commande['email']) ?>
                        </a>
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Téléphone:</span>
                    <span class="info-value">
                        <a href="tel:<?= htmlspecialchars($commande['telephone']) ?>" class="contact-link">
                            <?= htmlspecialchars($commande['telephone']) ?>
                        </a>
                    </span>
                </div>
            </div>
        </div>

        <!-- Créneau de retrait -->
        <div class="card">
            <h2>🕒 Créneau de retrait</h2>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Date:</span>
                    <span class="info-value"><?= date('d/m/Y', strtotime($commande['date_creneau'])) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Heure:</span>
                    <span class="info-value">
                        <?= date('H:i', strtotime($commande['heure_debut'])) ?> - 
                        <?= date('H:i', strtotime($commande['heure_fin'])) ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Détails de la commande -->
        <div class="card">
            <h2>🍕 Détails de la commande</h2>
            <?php if (empty($details)): ?>
                <p style="text-align: center; color: #7f8c8d; padding: 2rem;">
                    Aucun détail de commande trouvé.
                </p>
            <?php else: ?>
                <div class="products-list">
                    <?php foreach ($details as $detail): ?>
                        <div class="product-item">
                            <div class="product-info">
                                <h3><?= htmlspecialchars($detail['produit_nom']) ?></h3>
                                <div class="product-description"><?= htmlspecialchars($detail['description']) ?></div>
                            </div>
                            <div class="product-quantity">x<?= $detail['quantite'] ?></div>
                            <div class="product-price">
                                <?= number_format($detail['prix_unitaire'] * $detail['quantite'], 2) ?>€
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="total-section">
                        <div>Total de la commande</div>
                        <div class="total-amount"><?= number_format($commande['total'], 2) ?>€</div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Actions -->
        <div class="card">
            <h2>⚡ Actions rapides</h2>
            <div class="actions">
                <?php if ($commande['statut'] === 'en_attente'): ?>
                    <button class="btn btn-success">✅ Confirmer</button>
                    <button class="btn btn-danger">❌ Annuler</button>
                <?php elseif ($commande['statut'] === 'confirmee'): ?>
                    <button class="btn btn-warning">🍕 Marquer prête</button>
                    <button class="btn btn-danger">❌ Annuler</button>
                <?php elseif ($commande['statut'] === 'prete'): ?>
                    <button class="btn btn-success">✅ Marquer terminée</button>
                <?php endif; ?>
                
                <a href="mailto:<?= htmlspecialchars($commande['email']) ?>?subject=Votre commande Pizza Truck #<?= $commande['id'] ?>" 
                   class="btn">📧 Contacter client</a>
                
                <button onclick="window.print()" class="btn">🖨️ Imprimer</button>
            </div>
        </div>
    </div>
</body>
</html>
