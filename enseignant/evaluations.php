<?php
require_once '../includes/auth.php';
require_once '../includes/config.php';

if (!isLoggedIn() || $_SESSION['role'] !== 'enseignant') {
    redirectByRole();
}

// Fetch the enseignant's details
$enseignant = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM Enseignant WHERE matricule = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $enseignant = $stmt->fetch();
}

// Fetch soutenances assigned to this enseignant that need evaluation
$stmt = $pdo->prepare("
    SELECT s.id_soutenance, s.date_soutenance, s.salle, s.theme, e.nom AS etudiant_nom, e.prenom AS etudiant_prenom,
           ev.id_evaluation, ev.note, ev.commentaire
    FROM Soutenance s
    JOIN Etudiant e ON s.id_etudiant = e.NCE
    JOIN Jury j ON s.id_soutenance = j.id_soutenance
    LEFT JOIN Evaluation ev ON s.id_soutenance = ev.id_soutenance AND ev.matricule_enseignant = ?
    WHERE j.matricule_enseignant = ?
    ORDER BY s.date_soutenance DESC
");
$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
$soutenances = $stmt->fetchAll();

// Handle form submission for adding/editing evaluations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_soutenance = $_POST['id_soutenance'] ?? null;
    $note = $_POST['note'] ?? null;
    $commentaire = $_POST['commentaire'] ?? '';
    $id_evaluation = $_POST['id_evaluation'] ?? null;

    try {
        if ($id_evaluation) {
            // Update existing evaluation
            $stmt = $pdo->prepare("UPDATE Evaluation SET note = ?, commentaire = ?, date_evaluation = NOW() WHERE id_evaluation = ? AND matricule_enseignant = ?");
            $stmt->execute([$note, $commentaire, $id_evaluation, $_SESSION['user_id']]);
            $_SESSION['success_message'] = "Évaluation mise à jour avec succès";
        } else {
            // Add new evaluation
            $stmt = $pdo->prepare("INSERT INTO Evaluation (id_soutenance, matricule_enseignant, note, commentaire, date_evaluation) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$id_soutenance, $_SESSION['user_id'], $note, $commentaire]);
            $_SESSION['success_message'] = "Évaluation ajoutée avec succès";
        }
        header("Location: evaluations.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Erreur: " . $e->getMessage();
    }
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
    <title>Évaluations - Enseignant</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
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
        .sidebar {
            width: 250px;
            height: calc(100vh - 60px);
            position: fixed;
            background-color: #f8f9fa;
            border-right: 1px solid #dee2e6;
        }
        .nav-item {
            display: block;
            padding: 10px 20px;
            color: #333;
            text-decoration: none;
        }
        .nav-item.active, .nav-item:hover {
            background-color: #e9ecef;
        }
        .main-content {
            margin-left: 250px;
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
            width: calc(100% - 250px);
            margin-left: 250px;
        }
        /* Enhanced Table Styles */
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .table thead th {
            background-color: #343a40;
            color: white;
            border: none;
        }
        .table tbody tr:hover {
            background-color: #e9ecef;
            transition: background-color 0.3s;
        }
        .table tbody td {
            vertical-align: middle;
        }
        .table .icon {
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <header class="top-navbar">
        <div class="d-flex align-items-center">
            <a href="dashboard.php" class="text-white text-decoration-none">
                <h4 class="mb-0">Évaluations - Enseignant</h4>
            </a>
        </div>
        <div class="user-info" id="userDropdown">
            <?php if ($enseignant && $enseignant['photo_profil']): ?>
                <div class="user-avatar" style="background-image: url('<?= htmlspecialchars($enseignant['photo_profil']) ?>');"></div>
            <?php else: ?>
                <div class="user-avatar">
                    <i class="bi bi-person-fill"></i>
                </div>
            <?php endif; ?>
            <div>
                <div class="user-name"><?= htmlspecialchars(($enseignant['prenom_ens'] ?? '') . ' ' . ($enseignant['nom_ens'] ?? '')) ?></div>
                <div class="user-role"><?= htmlspecialchars(ucfirst($_SESSION['role'] ?? 'Enseignant')) ?></div>
            </div>
            <div class="dropdown-menu" id="dropdownMenu">
                <a href="?logout" class="dropdown-item">
                    <i class="bi bi-box-arrow-right me-2"></i> Déconnexion
                </a>
            </div>
        </div>
    </header>

    <div class="main-container">
        <nav class="sidebar">
            <div class="sidebar-header p-3 bg-dark border-bottom">
                <h5 class="text-white mb-0">
                    <i class="bi bi-list me-2"></i>Menu
                </h5>
            </div>
            <div class="nav-menu pt-2">
                <a href="dashboard.php" class="nav-item">
                    <i class="bi bi-house-door"></i> Tableau de Bord
                </a>
                <a href="evaluations.php" class="nav-item active">
                    <i class="bi bi-clipboard-check"></i> Évaluations
                </a>
            </div>
        </nav>

        <main class="main-content">
            <div class="content-container">
                <h2>Évaluations des Soutenances</h2>
                
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= $_SESSION['success_message'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['success_message']); ?>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= $_SESSION['error_message'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['error_message']); ?>
                <?php endif; ?>
                
                <?php if (empty($soutenances)): ?>
                    <p>Aucune soutenance à évaluer pour le moment.</p>
                <?php else: ?>
                    <div class="card">
                        <div class="card-body">
                            <div class="table-container">
                                <table id="evaluationsTable" class="table table-striped" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th><i class="bi bi-calendar-event icon"></i>Date</th>
                                            <th><i class="bi bi-door-open icon"></i>Salle</th>
                                            <th><i class="bi bi-book icon"></i>Thème</th>
                                            <th><i class="bi bi-person-fill icon"></i>Étudiant</th>
                                            <th><i class="bi bi-star-fill icon"></i>Note</th>
                                            <th><i class="bi bi-chat-left-text icon"></i>Commentaire</th>
                                            <th><i class="bi bi-gear-fill icon"></i>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($soutenances as $soutenance): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($soutenance['date_soutenance']) ?></td>
                                                <td><?= htmlspecialchars($soutenance['salle']) ?></td>
                                                <td><?= htmlspecialchars($soutenance['theme']) ?></td>
                                                <td><?= htmlspecialchars($soutenance['etudiant_nom'] . ' ' . $soutenance['etudiant_prenom']) ?></td>
                                                <td>
                                                    <?php if ($soutenance['note'] !== null): ?>
                                                        <?= htmlspecialchars($soutenance['note']) ?>/20
                                                    <?php else: ?>
                                                        Non évalué
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($soutenance['commentaire'] ?? 'Aucun commentaire') ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-primary evaluate-btn" 
                                                            data-id-soutenance="<?= htmlspecialchars($soutenance['id_soutenance']) ?>"
                                                            data-id-evaluation="<?= htmlspecialchars($soutenance['id_evaluation'] ?? '') ?>"
                                                            data-note="<?= htmlspecialchars($soutenance['note'] ?? '') ?>"
                                                            data-commentaire="<?= htmlspecialchars($soutenance['commentaire'] ?? '') ?>"
                                                            data-bs-toggle="modal" data-bs-target="#evaluationModal">
                                                        <i class="bi bi-pencil-square"></i> Évaluer
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Evaluation Modal -->
    <div class="modal fade" id="evaluationModal" tabindex="-1" aria-labelledby="evaluationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="evaluations.php">
                    <div class="modal-header">
                        <h5 class="modal-title" id="evaluationModalLabel">Évaluer une Soutenance</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id_soutenance" id="modalIdSoutenance">
                        <input type="hidden" name="id_evaluation" id="modalIdEvaluation">
                        <div class="mb-3">
                            <label for="note" class="form-label">Note (0-20)</label>
                            <input type="number" class="form-control" id="note" name="note" step="0.01" min="0" max="20" required>
                        </div>
                        <div class="mb-3">
                            <label for="commentaire" class="form-label">Commentaire (optionnel)</label>
                            <textarea class="form-control" id="commentaire" name="commentaire" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <footer class="app-footer">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12 text-center">
                    Copyright © <?= date('Y') ?> Gestion des Soutenances
                </div>
            </div>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
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

            // Handle evaluate button click to populate modal
            document.querySelectorAll('.evaluate-btn').forEach(button => {
                button.addEventListener('click', function() {
                    document.getElementById('modalIdSoutenance').value = this.dataset.idSoutenance;
                    document.getElementById('modalIdEvaluation').value = this.dataset.idEvaluation;
                    document.getElementById('note').value = this.dataset.note;
                    document.getElementById('commentaire').value = this.dataset.commentaire;
                });
            });

            // Reset modal when closed
            document.getElementById('evaluationModal').addEventListener('hidden.bs.modal', function () {
                this.querySelector('form').reset();
            });

            // Initialize DataTable
            $('#evaluationsTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/fr-FR.json"
                },
                "paging": true,
                "searching": true,
                "ordering": true,
                "info": true
            });
        });
    </script>
</body>
</html>