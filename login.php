<?php
// Comprobar la conexión a la base de datos (conexionBD.php)
require('conexionBD.php'); // Asegúrate de que esta línea se ajuste a cómo estás configurando tu conexión PDO

// Iniciar la sesión
session_start();

// Comprobar si se han enviado los datos para el inicio de sesión
if (isset($_POST['username'], $_POST['password'])) {
    // Obtener y limpiar los datos del formulario
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Verificar que no están vacíos
    if (empty($username) || empty($password)) {
        echo "Por favor, introduce un nombre de usuario y una contraseña.";
        exit();
    }

    // Verificar el usuario admin
    if ($username === 'admin' && $password === 'adminadmin') {
        $_SESSION['username'] = $username;
        header("Location: admin.php");
        exit();
    }

    try {
        // Preparar y ejecutar la consulta usando PDO
        $stmt = $con->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        // Fetch del usuario como objeto
        if ($user = $stmt->fetchObject()) {
            // Comparar la contraseña de forma segura
            if (password_verify($password, $user->password)) {
                $_SESSION['username'] = $user->username;
                header("Location: forum.php");
                exit();
            } else {
                echo "Nombre de usuario o contraseña incorrectos.";
            }
        } else {
            echo "Nombre de usuario o contraseña incorrectos.";
        }
    } catch (PDOException $e) {
        echo "Error en la consulta: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="stylelogin.css">
    <title>Login</title>
</head>
<body>
    <form method="post">
        <input type="text" name="username" placeholder="Usuario">
        <input type="password" name="password" placeholder="Contraseña">
        <input type="submit" value="Login">
    </form>
</body>
</html>
