<?php
require_once 'includes/config.php';

header('Content-Type: application/json');

$id_soutenance = $_POST['id_soutenance'] ?? null;
$id_jury = $_POST['id_jury'] ?? null;

try {
    $count = 0;
    if ($id_soutenance) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM Jury WHERE id_soutenance = ?");
        $stmt->execute([$id_soutenance]);
        $count = $stmt->fetchColumn();

        if ($id_jury) {
            // For edit, exclude the current jury from the count
            $stmt = $pdo->prepare("SELECT id_soutenance FROM Jury WHERE id_jury = ?");
            $stmt->execute([$id_jury]);
            $existing = $stmt->fetch();
            if ($existing && $existing['id_soutenance'] == $id_soutenance) {
                $count--; // Exclude the current jury
            }
        }
    }

    echo json_encode(['count' => $count]);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
    exit();
}
?>