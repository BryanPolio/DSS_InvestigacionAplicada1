<?php
require_once 'config/database.php';

if (isset($_SESSION['docente_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM docentes WHERE email = ?");
    $stmt->execute([$email]);
    $docente = $stmt->fetch();
    
    if (!$docente) {
        $error = "El email no está registrado. Por favor, regístrese primero.";
    } else {
        if (password_verify($password, $docente['password'])) {
            $_SESSION['docente_id'] = $docente['id'];
            $_SESSION['docente_nombre'] = $docente['nombre'];
            header('Location: dashboard.php');
            exit();
        } else {
            $error = "Contraseña incorrecta.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
   <title>Login Docente</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <h1>Acceso Docente</h1>
    
    <?php if ($error): ?>
        <p style="color: red;"><strong><?php echo $error; ?></strong></p>
    <?php endif; ?>
    
    <form method="POST">
        <label>Email:</label><br>
        <input type="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required><br><br>
        
        <label>Contraseña:</label><br>
        <input type="password" name="password" required><br><br>
        
        <input type="submit" value="Ingresar">
    </form>
    
    <p>¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a></p>
    
    <hr>
    <p><strong>Nota:</strong> Si no tienes cuenta, regístrate primero.</p>
</body>
</html>