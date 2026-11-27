<?php
    session_start();

    if (!isset($_SESSION['usuario'])) {
        header("Location: ../sesion/login.php");
        exit();
    }

    $mensaje = $_SESSION['mensaje'] ?? '';
    unset($_SESSION['mensaje']);

    require_once __DIR__ . '/../config/conexion.php';
    $buscar = trim($_GET['buscar'] ?? '');

    $sql = "SELECT
        movimiento.id_movimiento,
        movimiento.fecha_hora,
        movimiento.tipo,
        movimiento.cantidad,
        movimiento.observacion,
        usuario.nombre AS nombre_usuario,
        producto.nombre AS nombre_producto,
        producto.codigo AS codigo_producto
    FROM movimiento
    INNER JOIN usuario
        ON movimiento.id_usuario = usuario.id_usuario
    INNER JOIN producto
        ON movimiento.id_producto = producto.id_producto
    ORDER BY movimiento.fecha_hora DESC";

    $consulta = $conexion->prepare($sql);
    $consulta->execute();

    $movimientos = $consulta->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Movimientos - TecnoStock</title>

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@6.0.0-alpha.1/dist/css/bootstrap.min.css"
            rel="stylesheet">
    </head>

    <body>

        <?php include '../layouts/navbar.php'; ?>

        <div class="container mt-4">
            <div class="d-flex justify-content-between align-items-center mb-4">

                <?php if (!empty($mensaje)): ?>
                    <div class="alert alert-success" role="alert">
                        <?php echo htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

                <h2 class="mb-0">Listado de movimientos</h2>

                <div class="d-flex gap-2">

                    <a href="/TecnoStock/movimientos/seleccionar_producto.php"
                    class="btn btn-primary">
                        + Realizar movimiento
                    </a>
                </div>

            </div>

            <!-- <form method="GET" action="listar.php" class="mb-3">

                <div class="input-group">
                    <input
                        type="text"
                        name="buscar"
                        class="form-control"
                        placeholder="Buscar por código, nombre o categoría..."
                        value="<?php echo htmlspecialchars($buscar, ENT_QUOTES, 'UTF-8'); ?>"
                    >

                    <button type="submit" class="btn btn-primary">
                        Buscar
                    </button>
                </div>

            </form> -->

            <table class="table table-striped table-bordered mt-3">

                <thead>
                    <tr>
                        <th>Fecha y hora</th>
                        <th>Tipo</th>
                        <th>Cantidad</th>
                        <th>Observacion</th>
                        <th>Usuario</th>
                        <th>Producto</th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($movimientos as $movimiento): ?>

                        <tr>
                            <td><?php echo htmlspecialchars($movimiento['fecha_hora']); ?></td>
                            <td><?php echo htmlspecialchars($movimiento['tipo']); ?></td>
                            <td><?php echo htmlspecialchars($movimiento['cantidad']); ?></td>
                            <td><?php echo htmlspecialchars($movimiento['observacion']); ?></td>
                            <td><?php echo htmlspecialchars($movimiento['nombre_usuario']); ?></td>
                            <td><?php echo htmlspecialchars($movimiento['codigo_producto']); ?></td>
                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>
    <script type="module"
            src="https://cdn.jsdelivr.net/npm/bootstrap@6.0.0-alpha.1/dist/js/bootstrap.bundle.min.js"
            crossorigin="anonymous">
    </script>
    </body>
</html>