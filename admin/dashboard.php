<?php
require_once '../includes/auth.php';
require_once '../includes/config.php';

// Sécurité
if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
    error_log("Unauthorized access to dashboard: user_id=" . ($_SESSION['user_id'] ?? 'null') . ", role=" . ($_SESSION['role'] ?? 'null'));
    redirectByRole();
}

// Handle logout (moved to auth.php, but kept here for consistency)
if (isset($_GET['logout'])) {
    logout();
}

// Données utilisateur connecté
$current_user = null;
try {
    if (isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("SELECT * FROM Administrateur WHERE id_admin = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $current_user = $stmt->fetch();
        if ($current_user) {
            error_log("Dashboard loaded for user_id=" . $_SESSION['user_id']);
        } else {
            error_log("No admin user found for user_id=" . $_SESSION['user_id']);
        }
    }
} catch (PDOException $e) {
    error_log("Error fetching admin user: " . $e->getMessage());
}

// Statistiques globales
$stats = [];
try {
    $stats = [
        'administrateurs' => $pdo->query("SELECT COUNT(*) FROM Administrateur")->fetchColumn(),
        'enseignants'     => $pdo->query("SELECT COUNT(*) FROM Enseignant")->fetchColumn(),
        'etudiants'       => $pdo->query("SELECT COUNT(*) FROM Etudiant")->fetchColumn(),
        'soutenances'     => $pdo->query("SELECT COUNT(*) FROM Soutenance")->fetchColumn()
    ];
} catch (PDOException $e) {
    error_log("Error fetching stats: " . $e->getMessage());
    $stats = ['administrateurs' => 0, 'enseignants' => 0, 'etudiants' => 0, 'soutenances' => 0]; // Fallback
}

// Dernières soutenances
$recentSoutenances = [];
try {
    $recentSoutenances = $pdo->query("
        SELECT s.id_soutenance, s.date_soutenance, e.nom, e.prenom 
        FROM Soutenance s
        JOIN Etudiant e ON s.id_etudiant = e.NCE
        ORDER BY s.date_soutenance DESC
        LIMIT 5
    ")->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching recent soutenances: " . $e->getMessage());
}

// Inclure le HTML
include 'dashboard_content.php';
?>