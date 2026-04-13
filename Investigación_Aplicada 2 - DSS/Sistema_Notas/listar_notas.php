<?php
require_once 'config/database.php';
$stmt = $pdo->prepare("SELECT * FROM estudiantes WHERE docente_id = ? ORDER BY id DESC");
$stmt->execute([$_SESSION['docente_id']]);
$estudiantes = $stmt->fetchAll();

$html = '';
foreach($estudiantes as $e) {
    $html .= "<tr>
        <td>".htmlspecialchars($e['nombre'])."</td>
        <td>".htmlspecialchars($e['asignatura'])."</td>
        <td><strong>{$e['nota']}</strong></td>
        <td>
            <button class='btn-editar' data-id='{$e['id']}' data-nombre='".htmlspecialchars($e['nombre'])."' data-asignatura='".htmlspecialchars($e['asignatura'])."' data-nota='{$e['nota']}'>Editar</button>
            <button class='btn-eliminar' data-id='{$e['id']}'>Borrar</button>
        </td>
    </tr>";
}

$stmt = $pdo->prepare("SELECT AVG(nota) as prom FROM estudiantes WHERE docente_id = ?");
$stmt->execute([$_SESSION['docente_id']]);
$p = $stmt->fetch();

echo json_encode(['html' => $html ?: '<tr><td colspan="4">No hay registros.</td></tr>', 'promedio' => number_format($p['prom'] ?? 0, 2)]);