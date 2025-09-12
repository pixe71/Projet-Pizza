<?php
// admin/stats.php
require_once '../auth.php';
requireAdmin();

$database = new Database();
$db = $database->getConnection();

// Date sélectionnée (par défaut aujourd'hui)
$date_selectionnee = $_GET['date'] ?? date('Y-m-d');

// Statistiques pour la date sélectionnée
$query = "SELECT 
    COUNT(*) as total_commandes,
    COUNT(CASE WHEN statut = 'terminee' THEN 1 END) as commandes_terminees,
    COUNT(CASE WHEN statut = 'confirmee' THEN 1 END) as commandes_confirmees,
    COUNT(CASE WHEN statut = 'prete' THEN 1 END) as commandes_pretes,
    COUNT(CASE WHEN statut = 'en_attente' THEN 1 END) as commandes_attente,
    COUNT(CASE WHEN statut = 'annulee' THEN 1 END) as commandes_annulees,
    SUM(CASE WHEN statut = 'terminee' THEN total ELSE 0 END) as revenus_terminees,
    SUM(total) as revenus_total_commandes
    FROM commandes c
    JOIN creneaux cr ON c.creneau_id = cr.id
    WHERE cr.date_creneau = ?";

$stmt = $db->prepare($query);
$stmt->execute([$date_selectionnee]);
$stats_jour = $stmt->fetch(PDO::FETCH_ASSOC);

// Statistiques des 7 derniers jours
$query = "SELECT 
    cr.date_creneau,
    COUNT(CASE WHEN c.statut = 'terminee' THEN 1 END) as commandes_jour,
    SUM(CASE WHEN c.statut = 'terminee' THEN c.total ELSE 0 END) as revenus_jour
    FROM creneaux cr
    LEFT JOIN commandes c ON cr.id = c.creneau_id
    WHERE cr.date_creneau >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY cr.date_creneau
    ORDER BY cr.date_creneau DESC";

$stmt = $db->prepare($query);
$stmt->execute();
$stats_semaine = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Top clients pour la date sélectionnée
$query = "SELECT 
    u.nom,
    u.prenom,
    u.email,
    COUNT(c.id) as nb_commandes,
    SUM(c.total) as total_depense
    FROM users u
    JOIN commandes c ON u.id = c.user_id
    JOIN creneaux cr ON c.creneau_id = cr.id
    WHERE cr.date_creneau = ?
    GROUP BY u.id
    ORDER BY total_depense DESC
    LIMIT 10";

$stmt = $db->prepare($query);
$stmt->execute([$date_selectionnee]);
$top_clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques - Pizza Truck Admin</title>
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
        
        .date-selector {
            margin-bottom: 1rem;
        }
        
        .date-selector input {
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-right: 0.5rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .stat-box {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
            border-left: 4px solid #3498db;
            transition: transform 0.2s;
        }
        
        .stat-box:hover {
            transform: translateY(-2px);
        }
        
        .stat-number {
            font-size: 1.8rem;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 0.3rem;
        }
        
        .stat-label {
            color: #7f8c8d;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
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
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .evolution {
            font-weight: bold;
        }
        
        .evolution.positive {
            color: #27ae60;
        }
        
        .evolution.negative {
            color: #e74c3c;
        }
        
        .no-data {
            text-align: center;
            color: #7f8c8d;
            padding: 2rem;
            background: #f8f9fa;
            border-radius: 5px;
        }
        
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 1rem;
            }
            
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                gap: 0.8rem;
            }
            
            .stat-number {
                font-size: 1.4rem;
            }
            
            table {
                font-size: 0.8rem;
            }
            
            th, td {
                padding: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📊 Statistiques Pizza Truck</h1>
        <div class="header-buttons">
            <a href="dashboard.php" class="btn">🏠 Dashboard</a>
            <a href="../logout.php" class="btn btn-danger">🚪 Déconnexion</a>
        </div>
    </div>

    <div class="container">
        <!-- Sélecteur de date -->
        <div class="card">
            <h2>📅 Sélection de date</h2>
            <form method="GET" class="date-selector">
                <input type="date" name="date" value="<?= $date_selectionnee ?>" max="<?= date('Y-m-d') ?>">
                <button type="submit" class="btn">🔍 Analyser</button>
            </form>
        </div>

        <!-- Statistiques du jour sélectionné -->
        <div class="card">
            <h2>📊 <?= date('d/m/Y', strtotime($date_selectionnee)) ?></h2>
            <div class="stats-grid">
                <div class="stat-box">
                    <div class="stat-number"><?= $stats_jour['total_commandes'] ?></div>
                    <div class="stat-label">Total commandes</div>
                    <?php if ($stats_jour['total_commandes'] > 0): ?>
                        <a href="commandes_jour.php?date=<?= $date_selectionnee ?>" class="btn btn-small">
                            👁️ Voir détails
                        </a>
                    <?php endif; ?>
                </div>
                
                <div class="stat-box">
                    <div class="stat-number"><?= $stats_jour['commandes_terminees'] ?></div>
                    <div class="stat-label">Terminées</div>
                    <?php if ($stats_jour['commandes_terminees'] > 0): ?>
                        <a href="commandes_jour.php?date=<?= $date_selectionnee ?>&statut=terminee" class="btn btn-small">
                            👁️ Voir détails
                        </a>
                    <?php endif; ?>
                </div>
                
                <div class="stat-box">
                    <div class="stat-number"><?= $stats_jour['commandes_confirmees'] ?></div>
                    <div class="stat-label">Confirmées</div>
                    <?php if ($stats_jour['commandes_confirmees'] > 0): ?>
                        <a href="commandes_jour.php?date=<?= $date_selectionnee ?>&statut=confirmee" class="btn btn-small">
                            👁️ Voir détails
                        </a>
                    <?php endif; ?>
                </div>
                
                <div class="stat-box">
                    <div class="stat-number"><?= $stats_jour['commandes_pretes'] ?></div>
                    <div class="stat-label">Prêtes</div>
                    <?php if ($stats_jour['commandes_pretes'] > 0): ?>
                        <a href="commandes_jour.php?date=<?= $date_selectionnee ?>&statut=prete" class="btn btn-small">
                            👁️ Voir détails
                        </a>
                    <?php endif; ?>
                </div>
                
                <div class="stat-box">
                    <div class="stat-number"><?= $stats_jour['commandes_attente'] ?></div>
                    <div class="stat-label">En attente</div>
                    <?php if ($stats_jour['commandes_attente'] > 0): ?>
                        <a href="commandes_jour.php?date=<?= $date_selectionnee ?>&statut=en_attente" class="btn btn-small">
                            👁️ Voir détails
                        </a>
                    <?php endif; ?>
                </div>
                
                <div class="stat-box">
                    <div class="stat-number"><?= $stats_jour['commandes_annulees'] ?></div>
                    <div class="stat-label">Annulées</div>
                    <?php if ($stats_jour['commandes_annulees'] > 0): ?>
                        <a href="commandes_jour.php?date=<?= $date_selectionnee ?>&statut=annulee" class="btn btn-small">
                            👁️ Voir détails
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="stats-grid" style="margin-top: 1.5rem;">
                <div class="stat-box" style="border-left-color: #27ae60;">
                    <div class="stat-number"><?= number_format($stats_jour['revenus_terminees'], 2) ?>€</div>
                    <div class="stat-label">Revenus confirmés</div>
                </div>
                
                <div class="stat-box" style="border-left-color: #f39c12;">
                    <div class="stat-number"><?= number_format($stats_jour['revenus_total_commandes'], 2) ?>€</div>
                    <div class="stat-label">Revenus potentiels</div>
                </div>
                
                <div class="stat-box" style="border-left-color: #9b59b6;">
                    <div class="stat-number">
                        <?= $stats_jour['total_commandes'] > 0 ? number_format($stats_jour['revenus_total_commandes'] / $stats_jour['total_commandes'], 2) : '0.00' ?>€
                    </div>
                    <div class="stat-label">Panier moyen</div>
                </div>
            </div>
        </div>

        <!-- Évolution sur 7 jours -->
        <div class="card">
            <h2>📈 Évolution des 7 derniers jours</h2>
            <?php if (empty($stats_semaine)): ?>
                <div class="no-data">
                    <h3>📭 Pas de données</h3>
                    <p>Aucune donnée disponible pour les 7 derniers jours.</p>
                </div>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Commandes terminées</th>
                                <th>Revenus</th>
                                <th>Évolution</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $previous_revenue = null;
                            foreach ($stats_semaine as $stat): 
                                $evolution = '';
                                if ($previous_revenue !== null && $previous_revenue > 0) {
                                    $evolution_pct = (($stat['revenus_jour'] - $previous_revenue) / $previous_revenue) * 100;
                                    $evolution_class = $evolution_pct >= 0 ? 'positive' : 'negative';
                                    $evolution_symbol = $evolution_pct >= 0 ? '+' : '';
                                    $evolution = sprintf('<span class="evolution %s">%s%.1f%%</span>', 
                                        $evolution_class, $evolution_symbol, $evolution_pct);
                                }
                                $previous_revenue = $stat['revenus_jour'];
                            ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($stat['date_creneau'])) ?></td>
                                    <td><?= $stat['commandes_jour'] ?></td>
                                    <td><?= number_format($stat['revenus_jour'], 2) ?>€</td>
                                    <td><?= $evolution ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Top clients du jour -->
        <div class="card">
            <h2>🏆 Top clients du <?= date('d/m/Y', strtotime($date_selectionnee)) ?></h2>
            <?php if (empty($top_clients)): ?>
                <div class="no-data">
                    <h3>👤 Pas de clients</h3>
                    <p>Aucune commande client pour cette date.</p>
                </div>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Position</th>
                                <th>Client</th>
                                <th>Email</th>
                                <th>Nb commandes</th>
                                <th>Total dépensé</th>
                                <th>Panier moyen</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($top_clients as $index => $client): ?>
                                <tr>
                                    <td>
                                        <?php
                                        $medals = ['🥇', '🥈', '🥉'];
                                        echo $medals[$index] ?? ($index + 1);
                                        ?>
                                    </td>
                                    <td><?= htmlspecialchars($client['prenom'] . ' ' . $client['nom']) ?></td>
                                    <td><?= htmlspecialchars($client['email']) ?></td>
                                    <td><?= $client['nb_commandes'] ?></td>
                                    <td><?= number_format($client['total_depense'], 2) ?>€</td>
                                    <td><?= number_format($client['total_depense'] / $client['nb_commandes'], 2) ?>€</td>
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
