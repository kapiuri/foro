<?php
$usuario = 'root';
$contraseña = '';
$servidor = 'localhost';
$bd = 'forum'; // Nombre de la base de datos que se va a utilizar
$dsn = "mysql:host=$servidor;dbname=$bd";

try {
    $con = new PDO($dsn, $usuario, $contraseña);
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Configurar el modo de error
} catch (PDOException $ex) {
    exit('No se ha podido conectar con la BD:<br/>' . $ex->getMessage());
}
?>
