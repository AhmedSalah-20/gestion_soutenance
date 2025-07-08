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

// Fetch all soutenances with student details
$stmt = $pdo->query("
    SELECT s.*, e.nom, e.prenom 
    FROM Soutenance s 
    JOIN Etudiant e ON s.id_etudiant = e.NCE
");
$soutenances = $stmt->fetchAll();

// Fetch all students for the dropdown in the form
$etudiants = $pdo->query("SELECT NCE, nom, prenom FROM Etudiant")->fetchAll();

// Handle logout (aligned with auth.php)
if (isset($_GET['logout'])) {
    logout();
}

// Handle deletion if requested
if (isset($_GET['delete'])) {
    $id_soutenance = $_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM Soutenance WHERE id_soutenance = ?");
        $stmt->execute([$id_soutenance]);
        $_SESSION['success_message'] = "Soutenance supprimée avec succès";
        header("Location: gestion_soutenances.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Erreur lors de la suppression: " . $e->getMessage();
    }
}

// Handle form submission for adding/editing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_soutenance = $_POST['id_soutenance'] ?? null;
    $id_etudiant = $_POST['id_etudiant'];
    $date_soutenance = $_POST['date_soutenance'];
    $salle = $_POST['salle'];
    $theme = $_POST['theme'];
    
    try {
        // Check if the soutenance already exists (for editing)
        if ($id_soutenance) {
            $stmt = $pdo->prepare("SELECT id_soutenance FROM Soutenance WHERE id_soutenance = ?");
            $stmt->execute([$id_soutenance]);
            $exists = $stmt->fetch();

            if ($exists) {
                // Update existing soutenance
                $stmt = $pdo->prepare("UPDATE Soutenance SET id_etudiant=?, date_soutenance=?, salle=?, theme=? WHERE id_soutenance=?");
                $stmt->execute([$id_etudiant, $date_soutenance, $salle, $theme, $id_soutenance]);
                $_SESSION['success_message'] = "Soutenance mise à jour avec succès";
            }
        } else {
            // Insert new soutenance
            $stmt = $pdo->prepare("INSERT INTO Soutenance (id_etudiant, date_soutenance, salle, theme) VALUES (?, ?, ?, ?)");
            $stmt->execute([$id_etudiant, $date_soutenance, $salle, $theme]);
            $_SESSION['success_message'] = "Soutenance ajoutée avec succès";
        }
        header("Location: gestion_soutenances.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Erreur: " . $e->getMessage();
        header("Location: gestion_soutenances.php");
        exit();
    }
}

// Include the view file
require 'gestion_soutenances_view.php';