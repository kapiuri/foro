<?php
// Incluir la conexión a la base de datos
require('conexionBD.php');

// Iniciar la sesión
session_start();

// Activar la visualización de errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Comprobar si se han enviado los datos para el registro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener y limpiar los datos del formulario
    $name = trim($_POST['name']);
    $lastname = trim($_POST['lastname']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Verificar que no están vacíos
    if (empty($name) || empty($lastname) || empty($username) || empty($email) || empty($password)) {
        echo "Por favor, completa todos los campos.";
        exit();
    }

    try {
        // Verificar si el nombre de usuario o el email ya están en uso
        $stmt = $con->prepare("SELECT * FROM users WHERE username = :username OR email = :email");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->fetch()) {
            echo "El nombre de usuario o el email ya están en uso. Por favor, elige otro.";
            exit();
        }

        // Hashear la contraseña
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insertar el nuevo usuario en la base de datos
        $stmt = $con->prepare("INSERT INTO users (name, lastname, username, email, password, created_at) VALUES (:name, :lastname, :username, :email, :password, NOW())");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':lastname', $lastname);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->execute();

        echo "Usuario registrado exitosamente. Puedes iniciar sesión ahora.";

    } catch (PDOException $e) {
        echo "Error al registrar el usuario: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styleregister.css">
    <title>Registro de Usuario</title>
</head>
<body>
    <h1>Registro de Usuario</h1>
    <form method="post">
        <label for="name">Nombre:</label>
        <input type="text" id="name" name="name" required>
        <br>
        <label for="lastname">Apellidos:</label>
        <input type="text" id="lastname" name="lastname" required>
        <br>
        <label for="username">Nombre de Usuario:</label>
        <input type="text" id="username" name="username" required>
        <br>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
        <br>
        <label for="password">Contraseña:</label>
        <input type="password" id="password" name="password" required>
        <br>
        <input type="submit" value="Registrar">
    </form>
    <p><a href="login.php">¿Ya tienes una cuenta? Inicia sesión aquí.</a></p>
</body>
</html>
