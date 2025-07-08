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

// Fetch all soutenances with student details
$stmt = $pdo->prepare("
    SELECT s.id_soutenance, s.date_soutenance, s.salle, s.theme, e.nom, e.prenom 
    FROM Soutenance s 
    JOIN Etudiant e ON s.id_etudiant = e.NCE
");
$stmt->execute();
$soutenances = $stmt->fetchAll();

// Fetch all teachers for the dropdown in the form
$enseignants = $pdo->query("SELECT matricule, nom_ens, prenom_ens FROM Enseignant")->fetchAll();

// Fetch existing jury assignments with all jurys per soutenance
$stmt = $pdo->prepare("
    SELECT j.id_jury, j.id_soutenance, j.matricule_enseignant, s.date_soutenance, s.salle, s.theme, 
           e.nom AS etudiant_nom, e.prenom AS etudiant_prenom, en.nom_ens, en.prenom_ens
    FROM Jury j
    JOIN Soutenance s ON j.id_soutenance = s.id_soutenance
    JOIN Etudiant e ON s.id_etudiant = e.NCE
    JOIN Enseignant en ON j.matricule_enseignant = en.matricule
    ORDER BY j.id_soutenance, j.id_jury
");
$stmt->execute();
$jury_assignments = $stmt->fetchAll();

// Fetch jury groups (all matricules per soutenance)
$stmt = $pdo->prepare("
    SELECT j.id_soutenance, GROUP_CONCAT(j.matricule_enseignant SEPARATOR ',') as matricules
    FROM Jury j
    GROUP BY j.id_soutenance
");
$stmt->execute();
$jury_groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle logout
if (isset($_GET['logout'])) {
    logout();
}

// Handle form submission for adding/editing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_jury = $_POST['id_jury'] ?? null;
    $id_soutenance = $_POST['id_soutenance'];
    $matricule_enseignants = $_POST['matricule_enseignant'] ?? [];

    try {
        // Count existing jurys for this soutenance
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM Jury WHERE id_soutenance = ?");
        $stmt->execute([$id_soutenance]);
        $existing_count = $stmt->fetchColumn();

        $new_count = $existing_count + count($matricule_enseignants);
        if ($new_count < 2 || $new_count > 3) {
            $_SESSION['error_message'] = "Une soutenance doit avoir entre 2 et 3 jurys. Actuellement : $new_count.";
        } else {
            if ($id_jury) {
                // Delete the existing jury entry to replace it
                $stmt = $pdo->prepare("DELETE FROM Jury WHERE id_jury = ?");
                $stmt->execute([$id_jury]);
                $existing_count--;
            }
            // Add new assignments
            foreach ($matricule_enseignants as $matricule) {
                $stmt = $pdo->prepare("INSERT INTO Jury (id_soutenance, matricule_enseignant) VALUES (?, ?)");
                $stmt->execute([$id_soutenance, $matricule]);
            }
            $_SESSION['success_message'] = "Attribution(s) de jury(s) mise(s) à jour avec succès.";
        }
        header("Location: assigner_jurys.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Erreur: " . $e->getMessage();
        header("Location: assigner_jurys.php");
        exit();
    }
}

// Handle deletion
if (isset($_GET['delete'])) {
    $id_jury = $_GET['delete'];
    try {
        $stmt = $pdo->prepare("SELECT id_soutenance FROM Jury WHERE id_jury = ?");
        $stmt->execute([$id_jury]);
        $soutenance = $stmt->fetch();
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM Jury WHERE id_soutenance = ?");
        $stmt->execute([$soutenance['id_soutenance']]);
        $current_count = $stmt->fetchColumn();

        if ($current_count > 2) {
            $stmt = $pdo->prepare("DELETE FROM Jury WHERE id_jury = ?");
            $stmt->execute([$id_jury]);
            $_SESSION['success_message'] = "Attribution de jury supprimée avec succès.";
        } else {
            $_SESSION['error_message'] = "Une soutenance doit avoir au moins 2 jurys.";
        }
        header("Location: assigner_jurys.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Erreur lors de la suppression: " . $e->getMessage();
    }
}

// Include the view file
require 'assigner_jurys_view.php';
?>