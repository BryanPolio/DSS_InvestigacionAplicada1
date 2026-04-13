<?php
require_once 'config/database.php';
if (!isset($_SESSION['docente_id'])) { header('Location: login.php'); exit(); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Sistema de Notas</title>
    <link rel="stylesheet" href="css/estilos.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="header">
        <h1>Sistema de Notas</h1>
        <div>
            <span>Bienvenido, <strong><?php echo htmlspecialchars($_SESSION['docente_nombre']); ?></strong></span> | 
            <a href="logout.php">Cerrar Sesión</a>
        </div>
    </div>

    <div class="container">
        <div class="card">
            <h2 id="form-title">Nueva Nota</h2>
            <div id="ajax-msg" class="mensaje"></div>
            <form id="nota-form">
                <input type="hidden" id="nota_id" name="id">
                <label>Estudiante</label>
                <input type="text" id="nombre" name="nombre" required>
                <label>Asignatura</label>
                <input type="text" id="asignatura" name="asignatura" required>
                <label>Calificación</label>
                <input type="number" id="nota" name="nota" step="0.1" min="0" max="10" required>
                
                <button type="submit" id="btn-envio">Guardar Registro</button>
                <button type="button" id="btn-reset" class="btn-cancelar" style="display:none;">Cancelar Edición</button>
            </form>
        </div>

        <div class="card">
            <h2>Registro de Calificaciones</h2>
            <table>
                <thead>
                    <tr>
                        <th>Estudiante</th>
                        <th>Asignatura</th>
                        <th>Nota</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tabla-notas"></tbody>
            </table>
            <div class="promedio-box">
                Promedio de la Clase: <strong id="promedio-valor">0.00</strong>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            function cargarDatos() {
                $.get('listar_notas.php', function(data) {
                    $('#tabla-notas').html(data.html);
                    $('#promedio-valor').text(data.promedio);
                }, 'json');
            }

            cargarDatos();

            $('#nota-form').submit(function(e) {
                e.preventDefault();
                let destino = $('#nota_id').val() ? 'actualizar_nota.php' : 'guardar_nota.php';
                $.post(destino, $(this).serialize(), function(res) {
                    let msg = $('#ajax-msg');
                    msg.text(res.message || res.error).show().removeClass('success error')
                       .addClass(res.success ? 'success' : 'error');
                    if(res.success) {
                        $('#nota-form')[0].reset();
                        $('#nota_id').val('');
                        $('#form-title').text('Nueva Nota');
                        $('#btn-reset').hide();
                        cargarDatos();
                    }
                    setTimeout(() => msg.fadeOut(), 3000);
                }, 'json');
            });

            $(document).on('click', '.btn-editar', function() {
                $('#nota_id').val($(this).data('id'));
                $('#nombre').val($(this).data('nombre'));
                $('#asignatura').val($(this).data('asignatura'));
                $('#nota').val($(this).data('nota'));
                $('#form-title').text('Editando Registro');
                $('#btn-reset').show();
            });

            $(document).on('click', '.btn-eliminar', function() {
                if(confirm('¿Eliminar esta nota permanentemente?')) {
                    $.post('eliminar_nota.php', {id: $(this).data('id')}, function(res) {
                        if(res.success) cargarDatos();
                        else alert(res.error);
                    }, 'json');
                }
            });

            $('#btn-reset').click(function() {
                $('#nota-form')[0].reset();
                $('#nota_id').val('');
                $(this).hide();
                $('#form-title').text('Nueva Nota');
            });
        });
    </script>
</body>
</html>