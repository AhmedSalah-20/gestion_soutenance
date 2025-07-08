<?php
// Authentication and Authorization Check
require_once '../includes/auth.php';

if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
    header("Location: /projet_soutenance/login.php");
    exit();
}

// Database Configuration
require_once '../includes/config.php';          

// Get current user details
$current_user = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM Administrateur WHERE id_admin = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $current_user = $stmt->fetch();
}

// Fetch all administrators
$administrateurs = $pdo->query("SELECT * FROM Administrateur")->fetchAll();

// Handle deletion if requested
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        // Delete profile photo if exists
        $stmt = $pdo->prepare("SELECT photo_profil FROM Administrateur WHERE id_admin = ?");
        $stmt->execute([$id]);
        $admin = $stmt->fetch();
        if ($admin && $admin['photo_profil'] && file_exists($admin['photo_profil'])) {
            unlink($admin['photo_profil']);
        }

        $stmt = $pdo->prepare("DELETE FROM Administrateur WHERE id_admin = ?");
        $stmt->execute([$id]);
        $_SESSION['success_message'] = "Administrateur supprimé avec succès";
        header("Location: gestion_administrateurs.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Erreur lors de la suppression: " . $e->getMessage();
    }
}

// Handle form submission for adding/editing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $login = $_POST['login'];
    $mot_de_passe = $_POST['mot_de_passe']; // Mot de passe en clair reçu du formulaire
    $email = $_POST['email'];
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    
    // Hacher le mot de passe s'il est fourni
    $hashed_mot_de_passe = !empty($mot_de_passe) ? password_hash($mot_de_passe, PASSWORD_DEFAULT) : null;
    
    // Handle profile photo upload
    $photo_profil = null;
    if (isset($_FILES['photo_profil']) && $_FILES['photo_profil']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/profils/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = mime_content_type($_FILES['photo_profil']['tmp_name']);
        
        if (in_array($fileType, $allowedTypes)) {
            $extension = pathinfo($_FILES['photo_profil']['name'], PATHINFO_EXTENSION);
            $filename = uniqid().'.'.$extension;
            $destination = $uploadDir.$filename;
            
            if (move_uploaded_file($_FILES['photo_profil']['tmp_name'], $destination)) {
                $photo_profil = $destination;
                
                if ($id) {
                    $stmt = $pdo->prepare("SELECT photo_profil FROM Administrateur WHERE id_admin = ?");
                    $stmt->execute([$id]);
                    $oldPhoto = $stmt->fetchColumn();
                    if ($oldPhoto && file_exists($oldPhoto)) {
                        unlink($oldPhoto);
                    }
                }
            }
        }
    }
    
    try {
        if ($id) {
            if ($photo_profil) {
                if ($hashed_mot_de_passe) {
                    $stmt = $pdo->prepare("UPDATE Administrateur SET login=?, mot_de_passe=?, email=?, nom=?, prenom=?, photo_profil=? WHERE id_admin=?");
                    $stmt->execute([$login, $hashed_mot_de_passe, $email, $nom, $prenom, $photo_profil, $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE Administrateur SET login=?, email=?, nom=?, prenom=?, photo_profil=? WHERE id_admin=?");
                    $stmt->execute([$login, $email, $nom, $prenom, $photo_profil, $id]);
                }
            } else {
                if ($hashed_mot_de_passe) {
                    $stmt = $pdo->prepare("UPDATE Administrateur SET login=?, mot_de_passe=?, email=?, nom=?, prenom=? WHERE id_admin=?");
                    $stmt->execute([$login, $hashed_mot_de_passe, $email, $nom, $prenom, $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE Administrateur SET login=?, email=?, nom=?, prenom=? WHERE id_admin=?");
                    $stmt->execute([$login, $email, $nom, $prenom, $id]);
                }
            }
            $_SESSION['success_message'] = "Administrateur mis à jour avec succès";
        } else {
            if ($hashed_mot_de_passe) {
                $stmt = $pdo->prepare("INSERT INTO Administrateur (login, mot_de_passe, email, nom, prenom, photo_profil) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$login, $hashed_mot_de_passe, $email, $nom, $prenom, $photo_profil]);
            } else {
                // Gérer le cas où aucun mot de passe n'est fourni (peut être facultatif pour une mise à jour)
                $_SESSION['error_message'] = "Le mot de passe est requis pour un nouvel administrateur.";
                header("Location: gestion_administrateurs.php");
                exit();
            }
            $_SESSION['success_message'] = "Administrateur ajouté avec succès";
        }
        header("Location: gestion_administrateurs.php");
        exit(); 
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Erreur: " . $e->getMessage();
        header("Location: gestion_administrateurs.php");
        exit();
    }
}

// Fetch admin data for editing if ID is provided
$editAdmin = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM Administrateur WHERE id_admin = ?");
    $stmt->execute([$id]);
    $editAdmin = $stmt->fetch();
}
// Include the view file
require 'gestion_administrateurs_view.php';
?>