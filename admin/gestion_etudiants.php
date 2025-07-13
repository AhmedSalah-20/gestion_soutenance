<?php
// Authentication and Authorization Check
require_once '../includes/auth.php';

if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
    header("Location: /projet_soutenance/login.php");
    exit();
}

// Database Configuration
require_once '../includes/config.php';

// Set JSON header for AJAX responses
header('Content-Type: application/json');

// Handle duplicate check via AJAX
if (isset($_GET['check_duplicate'])) {
    $field = $_GET['check_duplicate'];
    $value = $_GET['value'];
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Etudiant WHERE $field = ?");
    $stmt->execute([$value]);
    $count = $stmt->fetchColumn();
    echo json_encode(['exists' => $count > 0]);
    exit();
}

// Get current user details (from Administrateur table)
$current_user = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM Administrateur WHERE id_admin = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $current_user = $stmt->fetch();
}

// Fetch all students
$etudiants = $pdo->query("SELECT * FROM Etudiant")->fetchAll();

// Handle logout
if (isset($_GET['logout'])) {
    logout();
}

// Handle deletion if requested
if (isset($_GET['delete'])) {
    $nce = $_GET['delete'];
    try {
        $stmt = $pdo->prepare("SELECT photo_profil FROM Etudiant WHERE NCE = ?");
        $stmt->execute([$nce]);
        $etudiant = $stmt->fetch();
        if ($etudiant && $etudiant['photo_profil'] && file_exists($etudiant['photo_profil'])) {
            unlink($etudiant['photo_profil']);
        }

        $stmt = $pdo->prepare("DELETE FROM Etudiant WHERE NCE = ?");
        $stmt->execute([$nce]);
        $_SESSION['success_message'] = "Étudiant supprimé avec succès";
        header("Location: gestion_etudiants.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Erreur lors de la suppression: " . $e->getMessage();
        header("Location: gestion_etudiants.php");
        exit();
    }
}

// Handle form submission for adding/editing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    $nce = $_POST['nce'] ?? null;
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $login = trim($_POST['login']);
    $mot_de_passe = $_POST['mot_de_passe'];

    // Server-side validations
    if (!preg_match('/^\d{8}$/', $nce)) {
        $errors['nce'] = 'Le NCE doit contenir exactement 8 chiffres';
    }
    if (!preg_match('/^[a-zA-Z\s-]{2,50}$/', $nom) || preg_match('/\s{2,}/', $nom) || preg_match('/^-|-$/', $nom)) {
        $errors['nom'] = 'Le nom est invalide';
    }
    if (!preg_match('/^[a-zA-Z\s-]{2,50}$/', $prenom) || preg_match('/\s{2,}/', $prenom) || preg_match('/^-|-$/', $prenom)) {
        $errors['prenom'] = 'Le prénom est invalide';
    }
    if (!preg_match('/^[a-zA-Z0-9]{4,20}$/', $login)) {
        $errors['login'] = 'Le login doit contenir 4 à 20 caractères alphanumériques';
    }
    if ($mot_de_passe !== '' && !preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $mot_de_passe)) {
        $errors['mot_de_passe'] = 'Le mot de passe doit contenir au moins 8 caractères, incluant une majuscule, une minuscule, un chiffre et un caractère spécial';
    }

    // Check duplicates
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Etudiant WHERE NCE = ? AND NCE != ?");
    $stmt->execute([$nce, $_POST['etudiantNCE'] ?? '']);
    if ($stmt->fetchColumn() > 0) {
        $errors['nce'] = 'Ce NCE existe déjà';
    }
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Etudiant WHERE login = ? AND NCE != ?");
    $stmt->execute([$login, $_POST['etudiantNCE'] ?? '']);
    if ($stmt->fetchColumn() > 0) {
        $errors['login'] = 'Ce login existe déjà';
    }

    // Handle profile photo upload
    $photo_profil = null;
    if (isset($_FILES['photo_profil']) && $_FILES['photo_profil']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../Uploads/profils/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = mime_content_type($_FILES['photo_profil']['tmp_name']);
        $fileSize = $_FILES['photo_profil']['size'];
        $maxSize = 2 * 1024 * 1024;
        
        if (!in_array($fileType, $allowedTypes)) {
            $errors['photo_profil'] = 'La photo doit être au format JPEG, PNG ou GIF';
        } elseif ($fileSize > $maxSize) {
            $errors['photo_profil'] = 'La photo ne doit pas dépasser 2MB';
        } else {
            $extension = pathinfo($_FILES['photo_profil']['name'], PATHINFO_EXTENSION);
            $filename = uniqid().'.'.$extension;
            $destination = $uploadDir.$filename;
            
            if (move_uploaded_file($_FILES['photo_profil']['tmp_name'], $destination)) {
                $photo_profil = $destination;
                if ($_POST['etudiantNCE']) {
                    $stmt = $pdo->prepare("SELECT photo_profil FROM Etudiant WHERE NCE = ?");
                    $stmt->execute([$_POST['etudiantNCE']]);
                    $oldPhoto = $stmt->fetchColumn();
                    if ($oldPhoto && file_exists($oldPhoto)) {
                        unlink($oldPhoto);
                    }
                }
            } else {
                $errors['photo_profil'] = 'Erreur lors du téléchargement de la photo';
            }
        }
    }

    if (empty($errors)) {
        try {
            $hashed_password = $mot_de_passe ? password_hash($mot_de_passe, PASSWORD_DEFAULT) : null;
            $stmt = $pdo->prepare("SELECT NCE FROM Etudiant WHERE NCE = ?");
            $stmt->execute([$nce]);
            $exists = $stmt->fetch();

            if ($exists) {
                if ($photo_profil) {
                    $stmt = $pdo->prepare("UPDATE Etudiant SET nom=?, prenom=?, login=?, mot_de_passe=?, photo_profil=? WHERE NCE=?");
                    $stmt->execute([$nom, $prenom, $login, $hashed_password ?: $exists['mot_de_passe'], $photo_profil, $nce]);
                } else {
                    $stmt = $pdo->prepare("UPDATE Etudiant SET nom=?, prenom=?, login=?, mot_de_passe=? WHERE NCE=?");
                    $stmt->execute([$nom, $prenom, $login, $hashed_password ?: $exists['mot_de_passe'], $nce]);
                }
                echo json_encode(['success' => true, 'message' => 'Étudiant mis à jour avec succès']);
            } else {
                $stmt = $pdo->prepare("INSERT INTO Etudiant (NCE, nom, prenom, login, mot_de_passe, photo_profil) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$nce, $nom, $prenom, $login, $hashed_password, $photo_profil]);
                echo json_encode(['success' => true, 'message' => 'Étudiant ajouté avec succès']);
            }
            exit();
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage(), 'errors' => []]);
            exit();
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Veuillez corriger les erreurs dans le formulaire', 'errors' => $errors]);
        exit();
    }
}

// Include the view file
require 'gestion_etudiants_view.php';
?>