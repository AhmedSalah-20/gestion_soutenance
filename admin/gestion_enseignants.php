<?php
// Authentication and Authorization Check
require_once '../includes/auth.php';

if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
    header("Location: /projet_soutenance/login.php");
    exit();
}

// Database Configuration
require_once '../includes/config.php';

// Get current user details (from Administrateur table, as in gestion_administrateurs.php)
$current_user = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM Administrateur WHERE id_admin = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $current_user = $stmt->fetch();
}

// Fetch all teachers
$enseignants = $pdo->query("SELECT * FROM Enseignant")->fetchAll();

// Handle logout (aligned with auth.php)
if (isset($_GET['logout'])) {
    logout();
}

// Handle deletion if requested
if (isset($_GET['delete'])) {
    $matricule = $_GET['delete'];
    try {
        // Delete profile photo if exists
        $stmt = $pdo->prepare("SELECT photo_profil FROM Enseignant WHERE matricule = ?");
        $stmt->execute([$matricule]);
        $enseignant = $stmt->fetch();
        if ($enseignant && $enseignant['photo_profil'] && file_exists($enseignant['photo_profil'])) {
            unlink($enseignant['photo_profil']);
        }

        $stmt = $pdo->prepare("DELETE FROM Enseignant WHERE matricule = ?");
        $stmt->execute([$matricule]);
        $_SESSION['success_message'] = "Enseignant supprimé avec succès";
        header("Location: gestion_enseignants.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Erreur lors de la suppression: " . $e->getMessage();
    }
}

// Handle form submission for adding/editing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $matricule = $_POST['matricule'] ?? null;
    $nom_ens = $_POST['nom_ens'];
    $prenom_ens = $_POST['prenom_ens'];
    $login = $_POST['login'];
    $mot_de_passe = $_POST['mot_de_passe'];
    $hashed_password = password_hash($mot_de_passe, PASSWORD_DEFAULT);
    
    // Handle profile photo upload
    $photo_profil = null;
    if (isset($_FILES['photo_profil']) && $_FILES['photo_profil']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/profils/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = mime_content_type($_FILES['photo_profil']['tmp_name']);
        
        if (in_array($fileType, $allowedTypes)) {
            $extension = pathinfo($_FILES['photo_profil']['name'], PATHINFO_EXTENSION);
            $filename = uniqid().'.'.$extension;
            $destination = $uploadDir.$filename;
            
            if (move_uploaded_file($_FILES['photo_profil']['tmp_name'], $destination)) {
                $photo_profil = $destination;
                
                // Delete old photo if exists
                if ($matricule) {
                    $stmt = $pdo->prepare("SELECT photo_profil FROM Enseignant WHERE matricule = ?");
                    $stmt->execute([$matricule]);
                    $oldPhoto = $stmt->fetchColumn();
                    if ($oldPhoto && file_exists($oldPhoto)) {
                        unlink($oldPhoto);
                    }
                }
            }
        }
    }
    
    try {
        // Check if the teacher already exists (for editing)
        $stmt = $pdo->prepare("SELECT matricule FROM Enseignant WHERE matricule = ?");
        $stmt->execute([$matricule]);
        $exists = $stmt->fetch();

        if ($exists) {
            // Update existing teacher
            if ($photo_profil) {
                $stmt = $pdo->prepare("UPDATE Enseignant SET nom_ens=?, prenom_ens=?, login=?, mot_de_passe=?, photo_profil=? WHERE matricule=?");
                $stmt->execute([$nom_ens, $prenom_ens, $login, $hashed_password, $photo_profil, $matricule]);
            } else {
                $stmt = $pdo->prepare("UPDATE Enseignant SET nom_ens=?, prenom_ens=?, login=?, mot_de_passe=? WHERE matricule=?");
                $stmt->execute([$nom_ens, $prenom_ens, $login, $hashed_password, $matricule]);
            }
            $_SESSION['success_message'] = "Enseignant mis à jour avec succès";
        } else {
            // Insert new teacher
            $stmt = $pdo->prepare("INSERT INTO Enseignant (matricule, nom_ens, prenom_ens, login, mot_de_passe, photo_profil) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$matricule, $nom_ens, $prenom_ens, $login, $hashed_password, $photo_profil]);
            $_SESSION['success_message'] = "Enseignant ajouté avec succès";
        }
        header("Location: gestion_enseignants.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Erreur: " . $e->getMessage();
        header("Location: gestion_enseignants.php");
        exit();
    }
}

// Include the view file
require 'gestion_enseignants_view.php';