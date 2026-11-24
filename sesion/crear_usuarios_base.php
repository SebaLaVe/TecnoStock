<?php
require_once __DIR__ . '/../config/conexion.php';

// Usuarios iniciales de prueba
$usuarios = [
    [
        'nombre' => 'Pedro',
        'correo' => 'pedro@tecnostock.cl',
        'clave' => 'Pedro_Tecno123!'
    ],
    [
        'nombre' => 'Ana',
        'correo' => 'ana@tecnostock.cl',
        'clave' => 'Ana_Inventario456!'
    ],
    [
        'nombre' => 'Carlos',
        'correo' => 'carlos@tecnostock.cl',
        'clave' => 'Carlos_Stock789!'
    ]
];

try {

    $sql = "INSERT INTO usuario (nombre, correo, clave_hash, estado)
            VALUES (?, ?, ?, ?)";

    $ejecutar = $conexion->prepare($sql);

    foreach ($usuarios as $usuario) {

        // Comprobar si el correo ya existe
        $consulta = $conexion->prepare(
            "SELECT id_usuario FROM usuario WHERE correo = ?"
        );

        $consulta->execute([$usuario['correo']]);

        if ($consulta->fetch()) {
            echo "El usuario {$usuario['correo']} ya existe.<br>";
            continue;
        }

        // Generar hash de la contraseña
        $hash = password_hash(
            $usuario['clave'],
            PASSWORD_DEFAULT
        );

        // Insertar usuario
        $ejecutar->execute([
            $usuario['nombre'],
            $usuario['correo'],
            $hash,
            'A'
        ]);

        echo "Usuario {$usuario['nombre']} creado correctamente.<br>";
    }

} catch (PDOException $e) {
    echo "Error al crear usuarios: " . $e->getMessage();
}

?>