<?php
require_once 'config/database.php';

if (isset($_SESSION['docente_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmar_password = $_POST['confirmar_password'];
    
    // Validaciones
    if (empty($nombre) || empty($email) || empty($password)) {
        $error = "Todos los campos son obligatorios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "El email no es válido.";
    } elseif (strlen($password) < 6) {
        $error = "La contraseña debe tener al menos 6 caracteres.";
    } elseif ($password !== $confirmar_password) {
        $error = "Las contraseñas no coinciden.";
    } else {
        // verificar que el email ya este
        $stmt = $pdo->prepare("SELECT id FROM docentes WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            $error = "Este email ya está registrado. <a href='login.php'>Iniciar sesión</a>";
        } else {
            // para el nuevo docente
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO docentes (nombre, email, password) VALUES (?, ?, ?)");
            
            if ($stmt->execute([$nombre, $email, $password_hash])) {
                $success = "Registro exitoso. <a href='login.php'>Iniciar sesión</a>";
            } else {
                $error = "Error al registrar. Intente nuevamente.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registro de Docente</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <h1>Registro de Docente</h1>
    
    <?php if ($error): ?>
        <p style="color: red;"><strong><?php echo $error; ?></strong></p>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <p style="color: green;"><strong><?php echo $success; ?></strong></p>
    <?php else: ?>
        <form method="POST">
            <label>Nombre Completo:</label><br>
            <input type="text" name="nombre" value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>" required><br><br>
            
            <label>Email:</label><br>
            <input type="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required><br><br>
            
            <label>Contraseña (mínimo 6 caracteres):</label><br>
            <input type="password" name="password" required><br><br>
            
            <label>Confirmar Contraseña:</label><br>
            <input type="password" name="confirmar_password" required><br><br>
            
            <input type="submit" value="Registrarse">
        </form>
    <?php endif; ?>
    
    <p><a href="login.php">Volver al Login</a></p>
</body>
</html>