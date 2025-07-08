<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des étudiants - Administration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="top-navbar">
        <div class="d-flex align-items-center">
            <a href="dashboard.php" class="text-white text-decoration-none">
                <h4 class="mb-0">Gestion des étudiants</h4>
            </a>
        </div>
        <div class="user-info" id="userDropdown">
            <?php if ($current_user && $current_user['photo_profil']): ?>
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
                <a href="gestion_administrateurs.php" class="nav-item">
                    <i class="bi bi-shield-lock"></i> Gérer les administrateurs
                </a>
                <a href="gestion_enseignants.php" class="nav-item">
                    <i class="bi bi-person-video3"></i> Gérer les enseignants
                </a>
                <a href="gestion_etudiants.php" class="nav-item active">
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
                
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2>Liste des étudiants</h2>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#etudiantModal">
                        <i class="bi bi-plus-circle"></i> Ajouter un étudiant
                    </button>
                </div>
                
                <div class="table-container">
                    <table id="etudiantsTable" class="table table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th>Photo</th>
                                <th>NCE</th>
                                <th>Nom</th>
                                <th>Prénom</th>
                                <th>Login</th>
                                <th>Mot de passe</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($etudiants as $etudiant): ?>
                                <tr>
                                    <td>
                                        <?php if ($etudiant['photo_profil']): ?>
                                            <img src="<?= htmlspecialchars($etudiant['photo_profil']) ?>" class="profile-img">
                                        <?php else: ?>
                                            <div class="default-avatar">
                                                <i class="bi bi-person-fill"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($etudiant['NCE']) ?></td>
                                    <td><?= htmlspecialchars($etudiant['nom']) ?></td>
                                    <td><?= htmlspecialchars($etudiant['prenom']) ?></td>
                                    <td><?= htmlspecialchars($etudiant['login']) ?></td>
                                    <td>••••••••</td>
                                    <td>
                                        <button class="btn btn-sm btn-warning btn-action edit-btn" 
                                                data-nce="<?= htmlspecialchars($etudiant['NCE']) ?>"
                                                data-nom="<?= htmlspecialchars($etudiant['nom']) ?>"
                                                data-prenom="<?= htmlspecialchars($etudiant['prenom']) ?>"
                                                data-login="<?= htmlspecialchars($etudiant['login']) ?>"
                                                data-password="">
                                            <i class="bi bi-pencil-square"></i> Modifier
                                        </button>
                                        <a href="gestion_etudiants.php?delete=<?= $etudiant['NCE'] ?>" class="btn btn-sm btn-danger btn-action" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet étudiant?')">
                                            <i class="bi bi-trash"></i> Supprimer
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Footer Section -->
    <footer class="app-footer">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12 text-center">
                    Copyright © <?= date('Y') ?>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Étudiant Modal -->
    <div class="modal fade" id="etudiantModal" tabindex="-1" aria-labelledby="etudiantModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="gestion_etudiants.php" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="etudiantModalLabel">Ajouter un étudiant</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="nce" id="etudiantNCE">
                        <div class="mb-3">
                            <label for="photo_profil" class="form-label">Photo de profil</label>
                            <input type="file" class="form-control" id="photo_profil" name="photo_profil" accept="image/*">
                        </div>
                        <div class="mb-3">
                            <label for="nce" class="form-label">NCE</label>
                            <input type="text" class="form-control" id="nce" name="nce" required>
                        </div>
                        <div class="mb-3">
                            <label for="nom" class="form-label">Nom</label>
                            <input type="text" class="form-control" id="nom" name="nom" required>
                        </div>
                        <div class="mb-3">
                            <label for="prenom" class="form-label">Prénom</label>
                            <input type="text" class="form-control" id="prenom" name="prenom" required>
                        </div>
                        <div class="mb-3">
                            <label for="login" class="form-label">Login</label>
                            <input type="text" class="form-control" id="login" name="login" required>
                        </div>
                        <div class="mb-3">
                            <label for="mot_de_passe" class="form-label">Mot de passe</label>
                            <div class="position-relative">
                                <input type="password" class="form-control" id="mot_de_passe" name="mot_de_passe" required>
                                <i class="bi bi-eye-slash password-toggle" id="togglePassword"></i>
                            </div>
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
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="gestion_etudiants.js"></script>
</body>
</html>