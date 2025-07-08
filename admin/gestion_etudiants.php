<?php
// Authentication and Authorization Check
require_once '../includes/auth.php';

if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
    header("Location: /projet_soutenance/login.php");
    exit();
}

// Database Configuration
require_once '../includes/config.php';

// Get current user details (from Administrateur table)
$current_user = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM Administrateur WHERE id_admin = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $current_user = $stmt->fetch();
}

// Fetch all students
$etudiants = $pdo->query("SELECT * FROM Etudiant")->fetchAll();

// Handle logout (aligned with auth.php)
if (isset($_GET['logout'])) {
    logout();
}

// Handle deletion if requested
if (isset($_GET['delete'])) {
    $nce = $_GET['delete'];
    try {
        // Delete profile photo if exists
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
    }
}

// Handle form submission for adding/editing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nce = $_POST['nce'] ?? null;
    $nom = $_POST['nom']; // Changed from nom_etud to nom
    $prenom = $_POST['prenom']; // Changed from prenom_etud to prenom
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
                if ($nce) {
                    $stmt = $pdo->prepare("SELECT photo_profil FROM Etudiant WHERE NCE = ?");
                    $stmt->execute([$nce]);
                    $oldPhoto = $stmt->fetchColumn();
                    if ($oldPhoto && file_exists($oldPhoto)) {
                        unlink($oldPhoto);
                    }
                }
            }
        }
    }
    
    try {
        // Check if the student already exists (for editing)
        $stmt = $pdo->prepare("SELECT NCE FROM Etudiant WHERE NCE = ?");
        $stmt->execute([$nce]);
        $exists = $stmt->fetch();

        if ($exists) {
            // Update existing student
            if ($photo_profil) {
                $stmt = $pdo->prepare("UPDATE Etudiant SET nom=?, prenom=?, login=?, mot_de_passe=?, photo_profil=? WHERE NCE=?");
                $stmt->execute([$nom, $prenom, $login, $hashed_password, $photo_profil, $nce]);
            } else {
                $stmt = $pdo->prepare("UPDATE Etudiant SET nom=?, prenom=?, login=?, mot_de_passe=? WHERE NCE=?");
                $stmt->execute([$nom, $prenom, $login, $hashed_password, $nce]);
            }
            $_SESSION['success_message'] = "Étudiant mis à jour avec succès";
        } else {
            // Insert new student
            $stmt = $pdo->prepare("INSERT INTO Etudiant (NCE, nom, prenom, login, mot_de_passe, photo_profil) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nce, $nom, $prenom, $login, $hashed_password, $photo_profil]);
            $_SESSION['success_message'] = "Étudiant ajouté avec succès";
        }
        header("Location: gestion_etudiants.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Erreur: " . $e->getMessage();
        header("Location: gestion_etudiants.php");
        exit();
    }
}

// Include the view file
require 'gestion_etudiants_view.php';