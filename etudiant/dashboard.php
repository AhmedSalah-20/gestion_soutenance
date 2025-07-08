<?php
require_once '../includes/auth.php';
require_once '../includes/config.php';

if (!isLoggedIn() || $_SESSION['role'] !== 'etudiant') {
    redirectByRole();
}

// Fetch the student's details
$etudiant = null;
try {
    if (isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("SELECT * FROM Etudiant WHERE NCE = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $etudiant = $stmt->fetch();
        if (!$etudiant) {
            error_log("No etudiant found for user_id=" . $_SESSION['user_id']);
        }
    }
} catch (PDOException $e) {
    error_log("Error fetching etudiant: " . $e->getMessage());
}

// Fetch evaluations for this student's soutenances
$evaluations = [];
try {
    $stmt = $pdo->prepare("
        SELECT s.id_soutenance, s.date_soutenance, s.salle, s.theme, e.nom_ens AS enseignant_nom, e.prenom_ens AS enseignant_prenom,
               ev.note, ev.commentaire, ev.date_evaluation
        FROM Soutenance s
        JOIN Etudiant et ON s.id_etudiant = et.NCE
        JOIN Jury j ON s.id_soutenance = j.id_soutenance
        JOIN Enseignant e ON j.matricule_enseignant = e.matricule
        LEFT JOIN Evaluation ev ON s.id_soutenance = ev.id_soutenance AND ev.matricule_enseignant = e.matricule
        WHERE et.NCE = ?
        ORDER BY s.date_soutenance DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $evaluations = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching evaluations for etudiant: " . $e->getMessage());
}

// Handle logout
if (isset($_GET['logout'])) {
    logout();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord - Étudiant</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .top-navbar {
            background-color: #343a40;
            padding: 10px 20px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .user-info {
            display: flex;
            align-items: center;
            cursor: pointer;
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            background-size: cover;
            background-position: center;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
        }
        .dropdown-menu {
            display: none;
            position: absolute;
            right: 20px;
            top: 60px;
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .dropdown-menu.show {
            display: block;
        }
        .dropdown-item {
            padding: 10px 20px;
            color: #333;
            text-decoration: none;
            display: block;
        }
        .dropdown-item:hover {
            background-color: #f8f9fa;
        }
        .main-content {
            padding: 20px;
        }
        .table-container {
            overflow-x: auto;
        }
        .app-footer {
            background-color: #f8f9fa;
            padding: 10px 0;
            border-top: 1px solid #dee2e6;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
    </style>
</head>
<body>
    <header class="top-navbar">
        <div class="d-flex align-items-center">
            <a href="dashboard.php" class="text-white text-decoration-none">
                <h4 class="mb-0">Tableau de Bord - Étudiant</h4>
            </a>
        </div>
        <div class="user-info" id="userDropdown">
            <?php if ($etudiant && $etudiant['photo_profil']): ?>
                <div class="user-avatar" style="background-image: url('<?= htmlspecialchars($etudiant['photo_profil']) ?>');"></div>
            <?php else: ?>
                <div class="user-avatar">
                    <i class="bi bi-person-fill"></i>
                </div>
            <?php endif; ?>
            <div>
                <div class="user-name"><?= htmlspecialchars(($etudiant['prenom'] ?? '') . ' ' . ($etudiant['nom'] ?? '')) ?></div>
                <div class="user-role"><?= htmlspecialchars(ucfirst($_SESSION['role'] ?? 'Étudiant')) ?></div>
            </div>
            <div class="dropdown-menu" id="dropdownMenu">
                <a href="?logout" class="dropdown-item">
                    <i class="bi bi-box-arrow-right me-2"></i> Déconnexion
                </a>
            </div>
        </div>
    </header>

    <main class="main-content">
        <div class="content-container">
            <h2>Tableau de Bord - Étudiant</h2>
            
            <div class="mb-4">
                <h3>Résultats des Évaluations</h3>
                <?php if (empty($evaluations)): ?>
                    <p>Aucune évaluation disponible pour le moment.</p>
                <?php else: ?>
                    <div class="table-container">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Date de Soutenance</th>
                                    <th>Salle</th>
                                    <th>Thème</th>
                                    <th>Enseignant</th>
                                    <th>Note</th>
                                    <th>Commentaire</th>
                                    <th>Date d'Évaluation</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($evaluations as $evaluation): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($evaluation['date_soutenance'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($evaluation['salle'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($evaluation['theme'] ?? '') ?></td>
                                        <td><?= htmlspecialchars(($evaluation['enseignant_prenom'] ?? '') . ' ' . ($evaluation['enseignant_nom'] ?? '')) ?></td>
                                        <td><?= $evaluation['note'] !== null ? htmlspecialchars($evaluation['note']) . '/20' : 'Non évalué' ?></td>
                                        <td><?= htmlspecialchars($evaluation['commentaire'] ?? 'Aucun commentaire') ?></td>
                                        <td><?= htmlspecialchars($evaluation['date_evaluation'] ?? '') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer class="app-footer">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12 text-center">
                    Copyright © <?= date('Y') ?> Gestion des Soutenances
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const userDropdown = document.getElementById('userDropdown');
            const dropdownMenu = document.getElementById('dropdownMenu');
            
            userDropdown.addEventListener('click', function(e) {
                e.stopPropagation();
                dropdownMenu.classList.toggle('show');
            });
            
            document.addEventListener('click', function() {
                dropdownMenu.classList.remove('show');
            });
            
            dropdownMenu.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        });
    </script>
</body>
</html>