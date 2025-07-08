<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assigner les jurys - Administration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="top-navbar">
        <div class="d-flex align-items-center">
            <a href="dashboard.php" class="text-white text-decoration-none">
                <h4 class="mb-0">Assigner les jurys</h4>
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
                <a href="gestion_etudiants.php" class="nav-item">
                    <i class="bi bi-people-fill"></i> Gérer les étudiants
                </a>
                <a href="gestion_soutenances.php" class="nav-item">
                    <i class="bi bi-calendar-event"></i> Gérer les soutenances
                </a>
                <a href="assigner_jurys.php" class="nav-item active">
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
                    <h2>Assignation des jurys</h2>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#juryModal">
                        <i class="bi bi-plus-circle"></i> Ajouter une attribution
                    </button>
                </div>
                
                <div class="table-container">
                    <table id="juryTable" class="table table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th><i class="bi bi-hash icon"></i>ID</th>
                                <th><i class="bi bi-calendar-event icon"></i>Soutenance (Date)</th>
                                <th><i class="bi bi-person-fill icon"></i>Étudiant</th>
                                <th><i class="bi bi-people-fill icon"></i>Jury</th>
                                <th><i class="bi bi-gear-fill icon"></i>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($jury_assignments as $assignment): ?>
                                <tr>
                                    <td><?= htmlspecialchars($assignment['id_jury']) ?></td>
                                    <td><?= htmlspecialchars($assignment['date_soutenance']) . ' - ' . htmlspecialchars($assignment['salle']) ?></td>
                                    <td><?= htmlspecialchars($assignment['etudiant_nom'] . ' ' . $assignment['etudiant_prenom']) ?></td>
                                    <td><?= htmlspecialchars($assignment['nom_ens'] . ' ' . $assignment['prenom_ens']) ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-warning btn-action edit-btn" 
                                                data-id="<?= htmlspecialchars($assignment['id_jury']) ?>"
                                                data-id_soutenance="<?= htmlspecialchars($assignment['id_soutenance']) ?>"
                                                data-matricule_enseignant="<?= htmlspecialchars($assignment['matricule_enseignant']) ?>">
                                            <i class="bi bi-pencil-square"></i> Modifier
                                        </button>
                                        <a href="assigner_jurys.php?delete=<?= $assignment['id_jury'] ?>" class="btn btn-sm btn-danger btn-action" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette attribution?')">
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
    
    <!-- Jury Modal -->
    <div class="modal fade" id="juryModal" tabindex="-1" aria-labelledby="juryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="assigner_jurys.php" id="juryForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="juryModalLabel">Ajouter une attribution</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id_jury" id="juryId">
                        <div class="mb-3">
                            <label for="id_soutenance" class="form-label">Soutenance</label>
                            <select class="form-control" id="id_soutenance" name="id_soutenance" required>
                                <option value="">Sélectionner une soutenance</option>
                                <?php foreach ($soutenances as $soutenance): ?>
                                    <option value="<?= htmlspecialchars($soutenance['id_soutenance']) ?>">
                                        <?= htmlspecialchars($soutenance['date_soutenance'] . ' - ' . $soutenance['salle'] . ' - ' . $soutenance['nom'] . ' ' . $soutenance['prenom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Enseignants (Jury) - Sélectionnez 2 à 3</label>
                            <div class="jury-selection" id="jurySelection">
                                <?php foreach ($enseignants as $enseignant): ?>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input jury-checkbox" 
                                               name="matricule_enseignant[]" 
                                               value="<?= htmlspecialchars($enseignant['matricule']) ?>" 
                                               id="enseignant_<?= htmlspecialchars($enseignant['matricule']) ?>">
                                        <label class="form-check-label" for="enseignant_<?= htmlspecialchars($enseignant['matricule']) ?>">
                                            <?= htmlspecialchars($enseignant['nom_ens'] . ' ' . $enseignant['prenom_ens']) ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <small id="juryCountMessage" class="text-muted">Sélectionnez 2 à 3 enseignants.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary" id="saveJuryBtn" disabled>Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        // Pass jury_groups to JavaScript
        const juryGroups = <?php echo json_encode($jury_groups); ?> || [];

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize DataTable with 5 rows per page and fixed height
            $('#juryTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/fr-FR.json'
                },
                pageLength: 5,
                lengthChange: false,
                pagingType: 'simple_numbers',
                scrollY: 'calc(100vh - 350px)',
                scrollCollapse: true
            });

            // User dropdown functionality
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
            
            // Edit button functionality
            document.querySelectorAll('.edit-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const juryModal = new bootstrap.Modal(document.getElementById('juryModal'));
                    document.getElementById('juryModalLabel').textContent = 'Modifier une attribution';
                    document.getElementById('juryId').value = this.dataset.id;
                    document.getElementById('id_soutenance').value = this.dataset.id_soutenance;

                    // Fetch existing jurys for this soutenance using juryGroups
                    const idSoutenance = this.dataset.id_soutenance;
                    const checkboxes = document.querySelectorAll('.jury-checkbox');
                    checkboxes.forEach(cb => cb.checked = false);

                    const juryGroup = juryGroups.find(group => group.id_soutenance == idSoutenance);
                    if (juryGroup && juryGroup.matricules) {
                        const matricules = juryGroup.matricules.split(',');
                        matricules.forEach(matricule => {
                            const checkbox = document.querySelector(`#enseignant_${matricule}`);
                            if (checkbox) checkbox.checked = true;
                        });
                    } else {
                        const matriculeEnseignant = this.dataset.matricule_enseignant;
                        if (matriculeEnseignant) {
                            const checkbox = document.querySelector(`#enseignant_${matriculeEnseignant}`);
                            if (checkbox) checkbox.checked = true;
                        }
                    }
                    updateJurySelection(); // Update validation after setting checkboxes
                    juryModal.show();
                });
            });
            
            // Reset modal when closed
            document.getElementById('juryModal').addEventListener('hidden.bs.modal', function () {
                document.getElementById('juryModalLabel').textContent = 'Ajouter une attribution';
                this.querySelector('form').reset();
                const messageDiv = document.getElementById('juryCountMessage');
                if (messageDiv) {
                    messageDiv.classList.remove('text-danger', 'text-success');
                    messageDiv.textContent = 'Sélectionnez 2 à 3 enseignants.';
                }
                updateJurySelection();
            });

            // Jury selection validation
            const juryCheckboxes = document.querySelectorAll('.jury-checkbox');
            const saveJuryBtn = document.getElementById('saveJuryBtn');
            const juryCountMessage = document.getElementById('juryCountMessage');

            function updateJurySelection() {
                const selectedCount = document.querySelectorAll('.jury-checkbox:checked').length;
                console.log('Selected count:', selectedCount); // Debug log
                if (selectedCount >= 2 && selectedCount <= 3) {
                    saveJuryBtn.disabled = false;
                    juryCountMessage.textContent = `Sélection : ${selectedCount} enseignant(s).`;
                    juryCountMessage.classList.remove('text-danger');
                    juryCountMessage.classList.add('text-success');
                } else {
                    saveJuryBtn.disabled = true;
                    juryCountMessage.textContent = `Sélectionnez entre 2 et 3 enseignants (actuellement : ${selectedCount}).`;
                    juryCountMessage.classList.remove('text-success');
                    juryCountMessage.classList.add('text-danger');
                }
            }

            // Add event listeners to checkboxes
            juryCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateJurySelection);
            });

            // Initial check and manual trigger to ensure state
            updateJurySelection();

            // Prevent anchor scrolling
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                });
            });
        });
    </script>
</body>
</html>