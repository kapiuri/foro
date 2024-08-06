<?php
// Incluir el archivo de conexión a la base de datos
require('conexionBD.php');

// Iniciar sesión
session_start();

// Activar la visualización de errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Comprobar si el usuario ha iniciado sesión
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}

// Obtener el user_id del usuario logueado
$username = $_SESSION['username'];
$sql = "SELECT id_user FROM users WHERE username = :username";
$stmt = $con->prepare($sql);
$stmt->bindParam(':username', $username, PDO::PARAM_STR);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$user_id = $result ? $result['id_user'] : null;

if (isset($_GET['id_topic'])) {
    $id_topic = $_GET['id_topic'];

    try {
        // Verificar si el usuario es admin o el propietario del tema
        $sql = "SELECT user_id FROM topics WHERE id_topic = :id_topic";
        $stmt = $con->prepare($sql);
        $stmt->bindParam(':id_topic', $id_topic, PDO::PARAM_INT);
        $stmt->execute();
        $topic = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($topic && $user_id === $topic['user_id'] || $username === 'admin') {
            // Eliminar los comentarios asociados
            $sql = "DELETE FROM comments WHERE topic_id = :id_topic";
            $stmt = $con->prepare($sql);
            $stmt->bindParam(':id_topic', $id_topic, PDO::PARAM_INT);
            $stmt->execute();

            // Eliminar el tema
            $sql = "DELETE FROM topics WHERE id_topic = :id_topic";
            $stmt = $con->prepare($sql);
            $stmt->bindParam(':id_topic', $id_topic, PDO::PARAM_INT);
            $stmt->execute();

            header("Location: forum.php");
            exit;
        } else {
            die("Error: No tienes permiso para eliminar este tema.");
        }
    } catch (PDOException $e) {
        die("Error al eliminar el tema y los comentarios: " . $e->getMessage());
    }
} elseif (isset($_GET['id_comment'])) {
    $id_comment = $_GET['id_comment'];

    try {
        // Verificar si el usuario es admin o el propietario del comentario
        $sql = "SELECT user_id FROM comments WHERE id_comment = :id_comment";
        $stmt = $con->prepare($sql);
        $stmt->bindParam(':id_comment', $id_comment, PDO::PARAM_INT);
        $stmt->execute();
        $comment = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($comment) {
            // Verificar permisos
            if ($user_id == $comment['user_id'] || $username === 'admin') {
                // Eliminar el comentario
                $sql = "DELETE FROM comments WHERE id_comment = :id_comment";
                $stmt = $con->prepare($sql);
                $stmt->bindParam(':id_comment', $id_comment, PDO::PARAM_INT);
                $stmt->execute();

                header("Location: forum.php");
                exit;
            } else {
                die("Error: No tienes permiso para eliminar este comentario.");
            }
        } else {
            die("Error: Comentario no encontrado.");
        }
    } catch (PDOException $e) {
        die("Error al eliminar el comentario: " . $e->getMessage());
    }
} else {
    die("Error: Parámetro no válido.");
}
?>
