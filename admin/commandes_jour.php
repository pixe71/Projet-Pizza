<?php
// admin/commandes_jour.php
require_once '../auth.php';
requireAdmin();

$database = new Database();
$db = $database->getConnection();

$date_selectionnee = $_GET['date'] ?? date('Y-m-d');
$statut_filtre = $_GET['statut'] ?? null;

// Construction de la requête selon le filtre
$where_statut = '';
$params = [$date_selectionnee];

if ($statut_filtre) {
    $where_statut = ' AND c.statut = ?';
    $params[] = $statut_filtre;
}

// Requête pour récupérer les commandes du jour
$query = "SELECT 
    c.id,
    c.total,
    c.statut,
    c.created_at as date_commande,
    u.nom as client_nom,
    u.prenom as client_prenom,
    u.email as client_email,
    u.telephone as client_telephone,
    cr.date_creneau,
    cr.heure_debut,
    cr.heure_fin
    FROM commandes c
    JOIN users u ON c.user_id = u.id
    JOIN creneaux cr ON c.creneau_id = cr.id
    WHERE cr.date_creneau = ? $where_statut
    ORDER BY cr.heure_debut ASC, c.created_at ASC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Statistiques de la journée
$query_stats = "SELECT 
    COUNT(*) as total_commandes,
    SUM(c.total) as total_revenus,
    AVG(c.total) as panier_moyen
    FROM commandes c
    JOIN creneaux cr ON c.creneau_id = cr.id
    WHERE cr.date_creneau = ? $where_statut";

$stmt_stats = $db->prepare($query_stats);
$stmt_stats->execute($params);
$stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commandes du <?= date('d/m/Y', strtotime($date_selectionnee)) ?> - Pizza Truck</title>
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        
        .btn {
            background: #3498db;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.9rem;
            transition: background 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn:hover { background: #2980b9; }
        .btn-success { background: #27ae60; }
        .btn-success:hover { background: #219a52; }
        .btn-danger { background: #e74c3c; }
        .btn-danger:hover { background: #c0392b; }
        .btn-small {
            padding: 0.3rem 0.6rem;
            font-size: 0.8rem;
        }
        
        .card {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .card h2 {
            margin-bottom: 1rem;
            color: #2c3e50;
            font-size: 1.3rem;
        }
        
        .filters {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }
        
        .filter-btn {
            padding: 0.4rem 0.8rem;
            border: 2px solid #3498db;
            background: white;
            color: #3498db;
            text-decoration: none;
            border-radius: 5px;
            font-size: 0.8rem;
            transition: all 0.3s;
        }
        
        .filter-btn:hover,
        .filter-btn.active {
            background: #3498db;
            color: white;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .stat-box {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
            border-left: 4px solid #3498db;
        }
        
        .stat-number {
            font-size: 1.5rem;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 0.3rem;
        }
        
        .stat-label {
            color: #7f8c8d;
            font-size: 0.9rem;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            background: white;
        }
        
        th, td {
            padding: 0.8rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #555;
            position: sticky;
            top: 0;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .status {
            padding: 0.3rem 0.6rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 500;
            text-transform: capitalize;
        }
        
        .status-en_attente {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-confirmee {
            background: #d4edda;
            color: #155724;
        }
        
        .status-prete {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .status-terminee {
            background: #d1e7dd;
            color: #0f5132;
        }
        
        .status-annulee {
            background: #f8d7da;
            color: #721c24;
        }
        
        .no-data {
            text-align: center;
            color: #7f8c8d;
            padding: 3rem;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .table-container {
            overflow-x: auto;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .header h1 {
                font-size: 1.4rem;
            }
            
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                gap: 0.8rem;
            }
            
            .stat-number {
                font-size: 1.2rem;
            }
            
            table {
                font-size: 0.8rem;
            }
            
            th, td {
                padding: 0.5rem 0.3rem;
            }
            
            .filters {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>
            📅 Commandes du <?= date('d/m/Y', strtotime($date_selectionnee)) ?>
            <?php if ($statut_filtre): ?>
                <small style="color: #7f8c8d; font-size: 1rem;">
                    (<?= ucfirst(str_replace('_', ' ', $statut_filtre)) ?>)
                </small>
            <?php endif; ?>
        </h1>
        <div class="header-buttons">
            <a href="stats.php?date=<?= $date_selectionnee ?>" class="btn">📊 Retour stats</a>
            <a href="dashboard.php" class="btn">🏠 Dashboard</a>
        </div>
    </div>

    <div class="container">
        <!-- Filtres par statut -->
        <div class="card">
            <h2>🔍 Filtrer par statut</h2>
            <div class="filters">
                <a href="commandes_jour.php?date=<?= $date_selectionnee ?>" 
                   class="filter-btn <?= !$statut_filtre ? 'active' : '' ?>">
                    📋 Toutes
                </a>
                <a href="commandes_jour.php?date=<?= $date_selectionnee ?>&statut=en_attente" 
                   class="filter-btn <?= $statut_filtre === 'en_attente' ? 'active' : '' ?>">
                    ⏳ En attente
                </a>
                <a href="commandes_jour.php?date=<?= $date_selectionnee ?>&statut=confirmee" 
                   class="filter-btn <?= $statut_filtre === 'confirmee' ? 'active' : '' ?>">
                    ✅ Confirmées
                </a>
                <a href="commandes_jour.php?date=<?= $date_selectionnee ?>&statut=prete" 
                   class="filter-btn <?= $statut_filtre === 'prete' ? 'active' : '' ?>">
                    🍕 Prêtes
                </a>
                <a href="commandes_jour.php?date=<?= $date_selectionnee ?>&statut=terminee" 
                   class="filter-btn <?= $statut_filtre === 'terminee' ? 'active' : '' ?>">
                    ✅ Terminées
                </a>
                <a href="commandes_jour.php?date=<?= $date_selectionnee ?>&statut=annulee" 
                   class="filter-btn <?= $statut_filtre === 'annulee' ? 'active' : '' ?>">
                    ❌ Annulées
                </a>
            </div>
        </div>

        <!-- Statistiques -->
        <?php if ($stats['total_commandes'] > 0): ?>
            <div class="card">
                <h2>📊 Résumé</h2>
                <div class="stats-grid">
                    <div class="stat-box">
                        <div class="stat-number"><?= $stats['total_commandes'] ?></div>
                        <div class="stat-label">Commandes</div>
                    </div>
                    <div class="stat-box" style="border-left-color: #27ae60;">
                        <div class="stat-number"><?= number_format($stats['total_revenus'], 2) ?>€</div>
                        <div class="stat-label">Total revenus</div>
                    </div>
                    <div class="stat-box" style="border-left-color: #f39c12;">
                        <div class="stat-number"><?= number_format($stats['panier_moyen'], 2) ?>€</div>
                        <div class="stat-label">Panier moyen</div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Liste des commandes -->
        <div class="card">
            <h2>📋 Liste des commandes</h2>
            
            <?php if (empty($commandes)): ?>
                <div class="no-data">
                    <h3>📭 Aucune commande</h3>
                    <p>
                        <?php if ($statut_filtre): ?>
                            Aucune commande avec le statut "<?= ucfirst(str_replace('_', ' ', $statut_filtre)) ?>" 
                            pour le <?= date('d/m/Y', strtotime($date_selectionnee)) ?>.
                        <?php else: ?>
                            Aucune commande pour le <?= date('d/m/Y', strtotime($date_selectionnee)) ?>.
                        <?php endif; ?>
                    </p>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Client</th>
                                <th>Email</th>
                                <th>Téléphone</th>
                                <th>Créneau</th>
                                <th>Total</th>
                                <th>Statut</th>
                                <th>Commandé le</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($commandes as $commande): ?>
                                <tr>
                                    <td><strong>#<?= $commande['id'] ?></strong></td>
                                    <td><?= htmlspecialchars($commande['client_prenom'] . ' ' . $commande['client_nom']) ?></td>
                                    <td>
                                        <a href="mailto:<?= htmlspecialchars($commande['client_email']) ?>">
                                            <?= htmlspecialchars($commande['client_email']) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <a href="tel:<?= htmlspecialchars($commande['client_telephone']) ?>">
                                            <?= htmlspecialchars($commande['client_telephone']) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <strong><?= date('H:i', strtotime($commande['heure_debut'])) ?></strong>
                                        -
                                        <?= date('H:i', strtotime($commande['heure_fin'])) ?>
                                    </td>
                                    <td><strong><?= number_format($commande['total'], 2) ?>€</strong></td>
                                    <td>
                                        <span class="status status-<?= $commande['statut'] ?>">
                                            <?= ucfirst(str_replace('_', ' ', $commande['statut'])) ?>
                                        </span>
                                    </td>
                                    <td><?= date('d/m H:i', strtotime($commande['date_commande'])) ?></td>
                                    <td>
                                        <a href="details_commande.php?id=<?= $commande['id'] ?>" 
                                           class="btn btn-small">
                                            👁️ Détails
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
