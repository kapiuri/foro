<?php
session_start();
require('conexionBD.php');

// Verificar si el usuario está autenticado
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Obtener el nombre de usuario desde la sesión
$username = $_SESSION['username'];

// Obtener user_id de la base de datos usando el nombre de usuario
try {
    $sql = "SELECT id_user FROM users WHERE username = :username";
    $stmt = $con->prepare($sql);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $user_id = $result ? $result['id_user'] : null;
} catch (PDOException $e) {
    echo "Error al obtener el user_id: " . $e->getMessage();
    $user_id = null;
}

// Verificar si los datos del formulario han sido enviados
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["title"])) {
    $title = trim($_POST["title"]);
    $created_at = date('Y-m-d H:i:s'); // Obtener la fecha y hora actuales

    // Validar y sanitizar las entradas
    if (!empty($title) && $user_id !== null) { // Verificar que user_id esté definido
        try {
            // Usar consultas preparadas para prevenir inyecciones SQL
            $sql = "INSERT INTO topics (user_id, title, created_at) VALUES (:user_id, :title, :created_at)";
            $stmt = $con->prepare($sql);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':title', $title, PDO::PARAM_STR);
            $stmt->bindParam(':created_at', $created_at, PDO::PARAM_STR);

            // Ejecutar la consulta
            if ($stmt->execute()) {
                echo "Tema agregado con éxito.";
            } else {
                echo "Error al agregar el tema.";
            }
        } catch (PDOException $e) {
            // Mostrar errores de SQL
            echo "Error: " . $e->getMessage();
        }
    } else {
        // Manejo de errores si las entradas no son válidas
        echo "Por favor, complete todos los campos y asegúrese de que user_id esté disponible.";
    }
}

// Consulta segura para obtener los temas
try {
    $sql = "SELECT * FROM topics";
    $stmt = $con->prepare($sql);
    $stmt->execute();
    $topics = $stmt->fetchAll(PDO::FETCH_OBJ);
} catch (PDOException $e) {
    echo "Error al obtener los temas: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styletopics.css">
    <title>Crear Topic</title>
</head>
<body>
    <a href="forum.php">Volver al foro</a>
    <h1>Agregar un Nuevo Tema</h1>
    <form method="post" action="topics.php">
        <!-- Mantener el campo oculto para user_id -->
        <input type="hidden" name="user_id" value="<?= htmlspecialchars($user_id) ?>">
        <label for="title">Nombre del Tema:</label>
        <input type="text" id="title" name="title" required>
        <br>
        <input type="submit" value="Agregar Tema">
    </form>

    <h2>Temas Existentes</h2>
    <ul>
        <?php if (isset($topics) && is_array($topics)): ?>
            <?php foreach ($topics as $topic): ?>
                <li>
                    <?= htmlspecialchars($topic->title) ?>
                    <?php if ($topic->user_id == $user_id): ?>
                        <!-- Mostrar el enlace de eliminación solo si el usuario es el creador -->
                        <a href="delete.php?id_topic=<?= htmlspecialchars($topic->id_topic) ?>" onclick="return confirm('¿Estás seguro de que quieres borrar este tema?')">X</a>
                    <?php endif; ?>
                    (Creado el <?= htmlspecialchars($topic->created_at) ?>)
                </li>
            <?php endforeach; ?>
        <?php else: ?>
            <li>No hay temas disponibles.</li>
        <?php endif; ?>
    </ul>
</body>
</html>
