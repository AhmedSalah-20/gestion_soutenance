<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Gestion des Soutenances</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">

</head>
<body>
    <header class="top-navbar">
        <div class="d-flex align-items-center">
            <a href="dashboard.php" class="text-white text-decoration-none">
                <h4 class="mb-0">Dashboard Administrateur</h4>
            </a>
        </div>
        <div class="user-info" id="userDropdown">
            <?php if ($current_user && isset($current_user['photo_profil']) && $current_user['photo_profil']): ?>
                <div class="user-avatar" style="background-image: url('<?= htmlspecialchars($current_user['photo_profil']) ?>')"></div>
            <?php else: ?>
                <div class="user-avatar">
                    <i class="bi bi-person-fill"></i>
                </div>
            <?php endif; ?>
            <div>
                <div class="user-name"><?= htmlspecialchars(($current_user['prenom'] ?? '') . ' ' . ($current_user['nom'] ?? '')) ?></div>
                <div class="user-role"><?= htmlspecialchars(ucfirst($_SESSION['role'] ?? 'Administrateur')) ?></div>
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
                <a href="dashboard.php" class="nav-item active">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
                <a href="gestion_administrateurs.php" class="nav-item">
                    <i class="bi bi-shield-lock"></i> Gérer les administrateurs
                </a>
                <a href="gestion_enseignants.php" class="nav-item">
                    <i class="bi bi-person-video3"></i> Gérer les enseignants
                </a>
                <a href="gestion_etudiants.php" class="nav-item">
                    <i class="bi bi-people-fill"></i> Gérer les étudiants
                </a>
                <a href="gestion_soutenances.php" class="nav-item">
                    <i class="bi bi-calendar-event"></i> Gérer les soutenances
                </a>
                <a href="assigner_jurys.php" class="nav-item">
                    <i class="bi bi-people"></i> Assigner les jurys
                </a>
            </div>
        </nav>

        <main class="main-content">
            <div class="content-container">
                <!-- Statistiques -->
                <div class="row">
                    <div class="col-md-3 mb-4">
                        <div class="card text-white bg-primary">
                            <div class="card-body">
                                <h5 class="card-title">Administrateurs</h5>
                                <h2><?= htmlspecialchars($stats['administrateurs'] ?? 0) ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card text-white bg-success">
                            <div class="card-body">
                                <h5 class="card-title">Enseignants</h5>
                                <h2><?= htmlspecialchars($stats['enseignants'] ?? 0) ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card text-white bg-info">
                            <div class="card-body">
                                <h5 class="card-title">Étudiants</h5>
                                <h2><?= htmlspecialchars($stats['etudiants'] ?? 0) ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card text-white bg-warning">
                            <div class="card-body">
                                <h5 class="card-title">Soutenances</h5>
                                <h2><?= htmlspecialchars($stats['soutenances'] ?? 0) ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dernières Soutenances -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Dernières Soutenances</h5>
                        <div class="table-container">
                            <table id="soutenancesTable" class="table table-striped" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Date</th>
                                        <th>Étudiant</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($recentSoutenances)): ?>
                                        <tr>
                                            <td colspan="3" class="text-center">Aucune soutenance récente</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($recentSoutenances as $soutenance): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($soutenance['id_soutenance'] ?? '') ?></td>
                                                <td><?= htmlspecialchars($soutenance['date_soutenance'] ?? '') ?></td>
                                                <td><?= htmlspecialchars(($soutenance['prenom'] ?? '') . ' ' . ($soutenance['nom'] ?? '')) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
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

            // Initialize DataTable
            $('#soutenancesTable').DataTable({
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