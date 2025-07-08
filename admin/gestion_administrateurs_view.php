<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des administrateurs - Administration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="top-navbar">
        <div class="d-flex align-items-center">
            <a href="dashboard.php" class="text-white text-decoration-none">
                <h4 class="mb-0">Gestion des administrateurs</h4>
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
                <a href="gestion_administrateurs.php" class="nav-item active">
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
                    <h2>Liste des administrateurs</h2>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#adminModal">
                        <i class="bi bi-plus-circle"></i> Ajouter un administrateur
                    </button>
                </div>
                
                <div class="table-container">
                    <table id="administrateursTable" class="table table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th>Photo</th>
                                <th>Login</th>
                                <th>Mot de passe</th>
                                <th>Email</th>
                                <th>Nom</th>
                                <th>Prénom</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($administrateurs as $admin): ?>
                                <tr>
                                    <td>
                                        <?php if ($admin['photo_profil']): ?>
                                            <img src="<?= htmlspecialchars($admin['photo_profil']) ?>" class="profile-img">
                                        <?php else: ?>
                                            <div class="default-avatar">
                                                <i class="bi bi-person-fill"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($admin['login']) ?></td>
                                    <td>••••••••</td>
                                    <td><?= htmlspecialchars($admin['email']) ?></td>
                                    <td><?= htmlspecialchars($admin['nom']) ?></td>
                                    <td><?= htmlspecialchars($admin['prenom']) ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-warning btn-action edit-btn" 
                                                data-id="<?= $admin['id_admin'] ?>"
                                                data-login="<?= htmlspecialchars($admin['login']) ?>"
                                                data-password=""
                                                data-email="<?= htmlspecialchars($admin['email']) ?>"
                                                data-nom="<?= htmlspecialchars($admin['nom']) ?>"
                                                data-prenom="<?= htmlspecialchars($admin['prenom']) ?>">
                                            <i class="bi bi-pencil-square"></i> Modifier
                                        </button>
                                        <a href="gestion_administrateurs.php?delete=<?= $admin['id_admin'] ?>" class="btn btn-sm btn-danger btn-action" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet administrateur?')">
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
                    Copyright © <?= date('Y') ?> Gestion des Soutenances
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Admin Modal -->
    <div class="modal fade" id="adminModal" tabindex="-1" aria-labelledby="adminModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="gestion_administrateurs.php" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="adminModalLabel"><?= isset($editAdmin) ? 'Modifier' : 'Ajouter' ?> un administrateur</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="adminId" value="<?= $editAdmin['id_admin'] ?? '' ?>">
                        <div class="mb-3">
                            <label for="photo_profil" class="form-label">Photo de profil</label>
                            <input type="file" class="form-control" id="photo_profil" name="photo_profil" accept="image/*">
                            <?php if (isset($editAdmin) && $editAdmin['photo_profil']): ?>
                                <div class="mt-2">
                                    <img src="<?= htmlspecialchars($editAdmin['photo_profil']) ?>" class="profile-img">
                                    <small class="text-muted">Photo actuelle</small>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <label for="login" class="form-label">Login</label>
                            <input type="text" class="form-control" id="login" name="login" value="<?= $editAdmin['login'] ?? '' ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="mot_de_passe" class="form-label">Mot de passe</label>
                            <div class="position-relative">
                                <input type="password" class="form-control" id="mot_de_passe" name="mot_de_passe" value="" required>
                                <i class="bi bi-eye-slash password-toggle" id="togglePassword"></i>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= $editAdmin['email'] ?? '' ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="nom" class="form-label">Nom</label>
                            <input type="text" class="form-control" id="nom" name="nom" value="<?= $editAdmin['nom'] ?? '' ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="prenom" class="form-label">Prénom</label>
                            <input type="text" class="form-control" id="prenom" name="prenom" value="<?= $editAdmin['prenom'] ?? '' ?>" required>
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
    <script src="gestion_administrateurs.js"></script>
</body>
</html>