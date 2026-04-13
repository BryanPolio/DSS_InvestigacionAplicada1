<?php
require_once 'config/database.php';
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['docente_id'])) {
    $stmt = $pdo->prepare("DELETE FROM estudiantes WHERE id = ? AND docente_id = ?");
    if ($stmt->execute([$_POST['id'], $_SESSION['docente_id']])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Error al eliminar']);
    }
}