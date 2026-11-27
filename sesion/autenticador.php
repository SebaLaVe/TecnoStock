
<?php
require_once "../config/conexion.php";

$correo = $_POST['correo'] ?? '';
$clave = $_POST['clave'] ?? '';

$sql = "SELECT * FROM usuario WHERE correo = ?";
$ejecutar = $conexion->prepare($sql);
$ejecutar->execute([$correo]);

$usuario = $ejecutar->fetch(PDO::FETCH_ASSOC);

if ($usuario && password_verify($clave, $usuario['clave_hash'])) {
    session_start();

    $_SESSION['usuario'] = $usuario['correo'];
    $_SESSION['nombre'] = $usuario['nombre'];

    header("Location: ../index.php");
    exit();
} else {
    echo "Usuario o contraseña incorrectos.";
}
