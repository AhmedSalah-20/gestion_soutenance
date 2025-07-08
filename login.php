<?php
ob_start(); // Start output buffering
require_once 'includes/auth.php';
require_once 'includes/config.php';

// Add debug output
error_log("Login page loaded");

// Redirect if already logged in
if (isLoggedIn()) {
    error_log("User is logged in, redirecting via redirectByRole()");
    redirectByRole();
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    
    if (empty($login) || empty($password)) {
        $error = "Tous les champs sont obligatoires";
        error_log("Login attempt failed: Empty credentials for $login");
    } else {
        error_log("Attempting login for: $login");
        if (authenticate($login, $password)) {
            error_log("Login successful for $login, redirecting...");
            redirectByRole();
        } else {
            $error = "Identifiants incorrects";
            error_log("Failed login attempt for username: $login");
        }
    }
}

// Check for session timeout message
if (isset($_GET['timeout']) && !isset($error)) {
    $error = "Votre session a expiré. Veuillez vous reconnecter.";
}

ob_end_flush(); // End output buffering
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Gestion des Soutenances</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body { 
            background-color: #f8f9fa;
            background-image: linear-gradient(to right, #f5f7fa, #e4e8f0);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-container { 
            max-width: 400px; 
            width: 100%;
            margin: 0 auto;
        }
        .login-card { 
            border-radius: 10px; 
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            border: none;
        }
        .card-header {
            border-radius: 10px 10px 0 0 !important;
        }
        .form-control:focus {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
    </style>
</head>
<body>
    <div class="container login-container">
        <div class="card login-card">
            <div class="card-header bg-primary text-white text-center py-3">
                <h3><i class="bi bi-person-circle me-2"></i> Connexion</h3>
            </div>
            <div class="card-body p-4">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show mb-4">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <form method="POST" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="login" class="form-label">
                            <i class="bi bi-person-fill me-2"></i>Identifiant
                        </label>
                        <input type="text" class="form-control form-control-lg" id="login" name="login" required
                               value="<?= htmlspecialchars($_POST['login'] ?? '') ?>">
                        <div class="invalid-feedback">
                            Veuillez saisir votre identifiant
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="password" class="form-label">
                            <i class="bi bi-lock-fill me-2"></i>Mot de passe
                        </label>
                        <input type="password" class="form-control form-control-lg" id="password" name="password" required>
                        <div class="invalid-feedback">
                            Veuillez saisir votre mot de passe
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg w-100 py-2">
                        <i class="bi bi-box-arrow-in-right me-2"></i> Se connecter
                    </button>
                </form>
            </div>
            <div class="card-footer text-center text-muted py-3">
                <small>© <?= date('Y') ?> Gestion des Soutenances</small>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function () {
            'use strict'
            
            const forms = document.querySelectorAll('.needs-validation')
            
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()
    </script>
</body>
</html>