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

// Guardar el nombre de usuario en una variable
$username = $_SESSION['username'];

// Obtener el user_id del usuario logueado
try {
    $sql = "SELECT id_user FROM users WHERE username = :username";
    $stmt = $con->prepare($sql);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $user_id = $result ? $result['id_user'] : null;

    // Verificar si se obtuvo el user_id
    if ($user_id === null) {
        die("Error: No se pudo encontrar el user_id para el usuario.");
    }
} catch (PDOException $e) {
    die("Error al obtener el user_id: " . $e->getMessage());
}

// Procesar el formulario para agregar un comentario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment']) && isset($_POST['topic_id'])) {
    $comment = trim($_POST['comment']);
    $topic_id = intval($_POST['topic_id']);

    if (!empty($comment)) {
        try {
            $sql = "INSERT INTO comments (user_id, topic_id, comment, created_at) 
                    VALUES (:user_id, :topic_id, :comment, NOW())";
            $stmt = $con->prepare($sql);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':topic_id', $topic_id, PDO::PARAM_INT);
            $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
            $stmt->execute();

            // Redirigir a la misma página para evitar el resubmit del formulario
            header("Location: forum.php");
            exit;
        } catch (PDOException $e) {
            die("Error al agregar el comentario: " . $e->getMessage());
        }
    } else {
        echo "El comentario no puede estar vacío.";
    }
}

// Consultar datos para mostrar
$sql = "SELECT t.*, c.id_comment, c.comment, u.username AS comment_user, t.user_id AS topic_user_id, c.user_id AS comment_user_id
        FROM topics t 
        LEFT JOIN comments c ON t.id_topic = c.topic_id
        LEFT JOIN users u ON c.user_id = u.id_user
        ORDER BY t.id_topic, c.created_at";
$cursor = $con->query($sql);
$all = $cursor->fetchAll(PDO::FETCH_OBJ);

// Organizar los comentarios por tema
$topics = [];
foreach ($all as $row) {
    $topic_id = $row->id_topic;

    if (!isset($topics[$topic_id])) {
        $topics[$topic_id] = [
            'title' => $row->title,
            'topic_user_id' => $row->topic_user_id, // Asegúrate de que este campo se asigna correctamente
            'comments' => []
        ];
    }

    if ($row->comment) {
        $topics[$topic_id]['comments'][] = [
            'id_comment' => $row->id_comment,
            'username' => $row->comment_user,
            'comment' => $row->comment,
            'created_at' => $row->created_at,
            'user_id' => $row->comment_user_id // Incluye el user_id del comentario para permitir la eliminación
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styleforum.css">
    <title>Foro</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $(".topics").click(function() {
                $(this).next(".comments").slideToggle();
            });
        });
    </script>
</head>
<body>
    <p><a href="logout.php"><?= htmlspecialchars($username) ?></a></p>
    <p><a href="topics.php">Crear Tema</a></p>
    <h1>Foro</h1>
    <?php foreach ($topics as $topic_id => $topic): ?>
        <div class="topics">
            <div class="title"><?= htmlspecialchars($topic['title']) ?></div>
            <?php if ($username === 'admin' || $user_id == $topic['topic_user_id']): ?>
                <a href="delete.php?id_topic=<?= htmlspecialchars($topic_id) ?>" onclick="return confirm('¿Estás seguro de que quieres borrar este tema y sus comentarios?')">Borrar tema</a>
            <?php endif; ?>
        </div>
        <div class="comments" style="display:none;">
            <?php foreach ($topic['comments'] as $comment): ?>
                <div class="comment">
                    <div class="username"><?= htmlspecialchars($comment['username']) ?></div>
                    <div class="text"><?= htmlspecialchars($comment['comment']) ?></div>
                    <div class="created_at"><?= htmlspecialchars($comment['created_at']) ?></div>
                    <?php if ($username === 'admin' || $user_id == $comment['user_id']): ?>
                        <a href="delete.php?id_comment=<?= htmlspecialchars($comment['id_comment']) ?>" onclick="return confirm('¿Estás seguro de que quieres borrar este comentario?')">Borrar comentario</a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <!-- Formulario para agregar comentarios -->
            <div class="comment-form">
                <form method="post" action="">
                    <input type="hidden" name="user_id" value="<?= htmlspecialchars($user_id) ?>">
                    <input type="hidden" name="topic_id" value="<?= htmlspecialchars($topic_id) ?>">
                    <textarea name="comment" placeholder="Escribe tu comentario aquí..." required></textarea>
                    <input type="submit" value="Agregar Comentario">
                </form>
            </div>
        </div>
    <?php endforeach; ?>
</body>
</html>
