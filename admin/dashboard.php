<?php
// admin/dashboard.php
require_once '../auth.php';
requireAdmin();

$database = new Database();
$db = $database->getConnection();

$message = '';

// ============================================
// SUPPRESSION AUTOMATIQUE DES CRÉNEAUX PASSÉS
// ============================================
try {
    // Supprimer automatiquement les créneaux dont l'heure de fin est passée
    $query_cleanup = "DELETE FROM creneaux 
                      WHERE CONCAT(date_creneau, ' ', heure_fin) < NOW()";
    $stmt_cleanup = $db->prepare($query_cleanup);
    $stmt_cleanup->execute();
    
    $creneaux_supprimes = $stmt_cleanup->rowCount();
    
    // Log de la suppression (optionnel)
    if ($creneaux_supprimes > 0) {
        error_log("Dashboard: $creneaux_supprimes créneaux passés supprimés automatiquement");
    }
} catch (Exception $e) {
    error_log("Erreur suppression auto créneaux: " . $e->getMessage());
}
// ============================================

// Traitement des actions POST
if ($_POST) {
    $action = $_POST['action'] ?? '';

    if ($action === 'ajouter_creneau') {
        $date = $_POST['date_creneau'];
        $heure_debut = $_POST['heure_debut'];
        $heure_fin = $_POST['heure_fin'];
        $max_pizzas = $_POST['max_pizzas'];

        $query = "INSERT INTO creneaux (date_creneau, heure_debut, heure_fin, max_pizzas) VALUES (?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        $stmt->execute([$date, $heure_debut, $heure_fin, $max_pizzas]);

        $message = "Créneau ajouté !";
    }

    if ($action === 'confirmer_commande') {
        $commande_id = $_POST['commande_id'];
        $query = "UPDATE commandes SET statut = 'confirmee' WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$commande_id]);
        $message = "Commande confirmée !";
    }

    if ($action === 'commande_prete') {
        $commande_id = $_POST['commande_id'];
        $query = "UPDATE commandes SET statut = 'prete' WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$commande_id]);
        $message = "Commande prête !";
    }

    if ($action === 'commande_terminee') {
        $commande_id = $_POST['commande_id'];
        $query = "UPDATE commandes SET statut = 'terminee' WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$commande_id]);
        $message = "Commande terminée !";
    }

    if ($action === 'annuler_commande') {
        $commande_id = $_POST['commande_id'];
        $query = "UPDATE commandes SET statut = 'annulee' WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$commande_id]);
        $message = "Commande annulée !";
    }

    if ($action === 'supprimer_creneau') {
        $creneau_id = $_POST['creneau_id'];

        $query = "SELECT COUNT(*) as nb_commandes FROM commandes WHERE creneau_id = ? AND statut != 'annulee'";
        $stmt = $db->prepare($query);
        $stmt->execute([$creneau_id]);
        $result = $stmt->fetch();

        if ($result['nb_commandes'] > 0) {
            $message = "Impossible de supprimer : commandes associées.";
        } else {
            $query = "DELETE FROM creneaux WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$creneau_id]);
            $message = "Créneau supprimé !";
        }
    }
}

// Statistiques rapides
$query = "SELECT 
    COUNT(*) as total_commandes_jour,
    COUNT(CASE WHEN c.statut = 'en_attente' THEN 1 END) as commandes_attente,
    COUNT(CASE WHEN c.statut = 'confirmee' THEN 1 END) as commandes_confirmees,
    COUNT(CASE WHEN c.statut = 'prete' THEN 1 END) as commandes_pretes,
    SUM(CASE WHEN c.statut = 'terminee' THEN c.total ELSE 0 END) as revenus_jour
    FROM commandes c
    JOIN creneaux cr ON c.creneau_id = cr.id
    WHERE cr.date_creneau = CURDATE()";

$stmt = $db->prepare($query);
$stmt->execute();
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Prochains créneaux (maintenant ils sont automatiquement filtrés car les passés sont supprimés)
$query = "SELECT * FROM creneaux 
          WHERE date_creneau >= CURDATE() 
          ORDER BY date_creneau ASC, heure_debut ASC 
          LIMIT 10";
$stmt = $db->prepare($query);
$stmt->execute();
$creneaux = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Commandes actives (non terminées/annulées)
$query = "SELECT c.*, cr.date_creneau, cr.heure_debut, cr.heure_fin, u.nom as client_nom
          FROM commandes c
          JOIN creneaux cr ON c.creneau_id = cr.id
          JOIN users u ON c.user_id = u.id
          WHERE c.statut NOT IN ('terminee', 'annulee')
          AND cr.date_creneau >= CURDATE()
          ORDER BY cr.date_creneau ASC, cr.heure_debut ASC";

$stmt = $db->prepare($query);
$stmt->execute();
$commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
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

        /* Indicateur de nettoyage automatique */
        .auto-cleanup-indicator {
            background: #d4edda;
            color: #155724;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
            border: 1px solid #c3e6cb;
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
        .btn-warning { background: #f39c12; }
        .btn-warning:hover { background: #e67e22; }
        .btn-danger { background: #e74c3c; }
        .btn-danger:hover { background: #c0392b; }
        .btn-small { padding: 0.3rem 0.6rem; font-size: 0.8rem; }

        .alert {
            background: #d4edda;
            color: #155724;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
            border-left: 4px solid #27ae60;
        }

        /* Alerte spéciale pour le nettoyage automatique */
        .alert-cleanup {
            background: #fff3cd;
            color: #856404;
            border-left-color: #ffc107;
            display: flex;
            align-items: center;
            gap: 0.5rem;
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

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-box {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            position: relative;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #3498db;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #7f8c8d;
            font-size: 0.9rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            align-items: end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #555;
        }

        .form-group input {
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.9rem;
        }

        .form-group input:focus {
            outline: none;
            border-color: #3498db;
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

        .status {
            padding: 0.3rem 0.6rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status-en_attente { background: #fff3cd; color: #856404; }
        .status-confirmee { background: #cce5ff; color: #0056b3; }
        .status-prete { background: #ffe0b3; color: #cc6600; }
        .status-terminee { background: #d1f2eb; color: #00695c; }
        .status-annulee { background: #f8d7da; color: #721c24; }

        .action-buttons {
            display: flex;
            gap: 0.3rem;
            flex-wrap: wrap;
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 1rem;
            }

            .header-buttons {
                flex-wrap: wrap;
                justify-content: center;
            }

            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            table {
                font-size: 0.8rem;
            }

            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🍕 Dashboard Admin</h1>
        <div class="header-buttons">
            <div class="auto-cleanup-indicator">
                🧹 Auto-nettoyage actif
            </div>
            <a href="stats.php" class="btn">📊 Statistiques</a>
            <a href="../logout.php" class="btn btn-danger">🚪 Déconnexion</a>
        </div>
    </div>

    <div class="container">
        <?php if ($message): ?>
            <div class="alert"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <?php if (isset($creneaux_supprimes) && $creneaux_supprimes > 0): ?>
            <div class="alert alert-cleanup">
                🧹 <strong><?= $creneaux_supprimes ?></strong> créneaux passés ont été automatiquement supprimés lors de cette session.
            </div>
        <?php endif; ?>

        <!-- Statistiques du jour -->
        <div class="stats-grid">
            <div class="stat-box">
                <div class="stat-number"><?= $stats['total_commandes_jour'] ?? 0 ?></div>
                <div class="stat-label">Commandes aujourd'hui</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?= $stats['commandes_attente'] ?? 0 ?></div>
                <div class="stat-label">En attente</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?= $stats['commandes_confirmees'] ?? 0 ?></div>
                <div class="stat-label">Confirmées</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?= number_format($stats['revenus_jour'] ?? 0, 0) ?>€</div>
                <div class="stat-label">Revenus du jour</div>
            </div>
        </div>

        <!-- Ajouter un créneau -->
        <div class="card">
            <h2>🕒 Ajouter un créneau</h2>
            <form method="POST">
                <input type="hidden" name="action" value="ajouter_creneau">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Date</label>
                        <input type="date" name="date_creneau" required min="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="form-group">
                        <label>Heure début</label>
                        <input type="time" name="heure_debut" required>
                    </div>
                    <div class="form-group">
                        <label>Heure fin</label>
                        <input type="time" name="heure_fin" required>
                    </div>
                    <div class="form-group">
                        <label>Max pizzas</label>
                        <input type="number" name="max_pizzas" required min="1" max="100" value="50">
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn">➕ Ajouter</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Prochains créneaux -->
        <div class="card">
            <h2>📅 Prochains créneaux <small style="color: #27ae60;">(créneaux passés supprimés automatiquement)</small></h2>
            <?php if (!empty($creneaux)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Horaire</th>
                            <th>Capacité</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($creneaux as $creneau): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($creneau['date_creneau'])) ?></td>
                                <td><?= date('H:i', strtotime($creneau['heure_debut'])) ?> - <?= date('H:i', strtotime($creneau['heure_fin'])) ?></td>
                                <td><?= $creneau['max_pizzas'] ?> pizzas</td>
                                <td>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Supprimer ce créneau ?')">
                                        <input type="hidden" name="action" value="supprimer_creneau">
                                        <input type="hidden" name="creneau_id" value="<?= $creneau['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-small">🗑️ Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div style="text-align: center; padding: 2rem; color: #7f8c8d;">
                    <p>🕒 Aucun créneau programmé</p>
                    <small>Ajoutez de nouveaux créneaux ci-dessus</small>
                </div>
            <?php endif; ?>
        </div>

        <!-- Commandes actives -->
        <div class="card">
            <h2>📋 Commandes actives</h2>
            <?php if (!empty($commandes)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Client</th>
                            <th>Date/Heure</th>
                            <th>Montant</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($commandes as $commande): ?>
                            <tr>
                                <td>#<?= $commande['id'] ?></td>
                                <td><?= htmlspecialchars($commande['client_nom']) ?></td>
                                <td>
                                    <?= date('d/m', strtotime($commande['date_creneau'])) ?><br>
                                    <small><?= date('H:i', strtotime($commande['heure_debut'])) ?>-<?= date('H:i', strtotime($commande['heure_fin'])) ?></small>
                                </td>
                                <td><?= number_format($commande['total'], 2) ?>€</td>
                                <td>
                                    <span class="status status-<?= $commande['statut'] ?>">
                                        <?= ucfirst(str_replace('_', ' ', $commande['statut'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <?php if ($commande['statut'] === 'en_attente'): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="confirmer_commande">
                                                <input type="hidden" name="commande_id" value="<?= $commande['id'] ?>">
                                                <button type="submit" class="btn btn-success btn-small">✅ Confirmer</button>
                                            </form>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Annuler ?')">
                                                <input type="hidden" name="action" value="annuler_commande">
                                                <input type="hidden" name="commande_id" value="<?= $commande['id'] ?>">
                                                <button type="submit" class="btn btn-danger btn-small">❌ Annuler</button>
                                            </form>

                                        <?php elseif ($commande['statut'] === 'confirmee'): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="commande_prete">
                                                <input type="hidden" name="commande_id" value="<?= $commande['id'] ?>">
                                                <button type="submit" class="btn btn-warning btn-small">🍕 Prête</button>
                                            </form>

                                        <?php elseif ($commande['statut'] === 'prete'): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="commande_terminee">
                                                <input type="hidden" name="commande_id" value="<?= $commande['id'] ?>">
                                                <button type="submit" class="btn btn-success btn-small">🎉 Remise</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div style="text-align: center; padding: 2rem; color: #7f8c8d;">
                    <p>📋 Aucune commande active</p>
                    <small>Les nouvelles commandes apparaîtront ici</small>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Auto-refresh de la page toutes les 2 minutes pour maintenir le nettoyage automatique
        setTimeout(() => {
            location.reload();
        }, 120000); // 2 minutes
    </script>
</body>
</html>
