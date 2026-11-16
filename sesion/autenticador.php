
<?php
session_start();
require_once "../config/conexion.php";

$correo = $_POST['correo'] ?? '';
$clave = $_POST['clave'] ?? '';

$sql = "SELECT * FROM Usuario WHERE Correo = ?";
$ejecutar = $conexion->prepare($sql);
$ejecutar->execute([$correo]);

$usuario = $ejecutar->fetch(PDO::FETCH_ASSOC);

if ($usuario && password_verify($clave, $usuario['Clave'])) {
    

    $_SESSION['usuario'] = $usuario['Correo'];

    header("Location: ../index.php");
    exit();
} else {
    $_SESSION['error'] = "Correo o clave incorrectos.";

    header("Location: ../sesion/login.php");
    exit();
}
