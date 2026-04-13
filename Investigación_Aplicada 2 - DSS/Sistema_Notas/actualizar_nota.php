<?php
require_once 'config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['docente_id'])) {
    echo json_encode(['success' => false, 'error' => 'Sesión expirada.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $nombre = trim($_POST['nombre']);
    $asignatura = trim($_POST['asignatura']);
    $nota = floatval($_POST['nota']);
    
    if (empty($nombre) || empty($asignatura) || !is_numeric($_POST['nota'])) {
        echo json_encode(['success' => false, 'error' => 'Todos los campos son obligatorios.']);
        exit();
    } elseif ($nota < 0 || $nota > 10) {
        echo json_encode(['success' => false, 'error' => 'La nota debe estar entre 0 y 10.']);
        exit();
    }

    $stmt = $pdo->prepare("UPDATE estudiantes SET nombre = ?, asignatura = ?, nota = ? WHERE id = ? AND docente_id = ?");
    if ($stmt->execute([$nombre, $asignatura, $nota, $id, $_SESSION['docente_id']])) {
        echo json_encode(['success' => true, 'message' => 'Nota actualizada correctamente.']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Error al actualizar.']);
    }
}
?>