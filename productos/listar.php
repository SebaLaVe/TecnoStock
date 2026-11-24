<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: ../sesion/login.php");
    exit();
}

require_once __DIR__ . '/../config/conexion.php';
$sql = "SELECT
            id_producto,
            codigo,
            nombre,
            precio,
            stock_actual,
            stock_minimo,
            estado
        FROM producto
        ORDER BY nombre ASC";

$consulta = $conexion->prepare($sql);
$consulta->execute();

$productos = $consulta->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos - TecnoStock</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@6.0.0-alpha.1/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>

<body>

    <?php include '../layouts/navbar.php'; ?>

    <div class="container mt-4">

        <h2>Listado de productos</h2>

        <table class="table table-striped table-bordered mt-3">

            <thead>
                <tr>
                    <th>Código</th>
                    <th>Nombre</th>
                    <th>Precio</th>
                    <th>Stock actual</th>
                    <th>Stock mínimo</th>
                    <th>Estado</th>
                </tr>
            </thead>

            <tbody>

                <?php foreach ($productos as $producto): ?>

                    <tr>
                        <td><?php echo htmlspecialchars($producto['codigo']); ?></td>
                        <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                        <td>$<?php echo number_format($producto['precio'], 0, ',', '.'); ?></td>
                        <td>
                            <?php if ($producto['stock_actual'] < $producto['stock_minimo']): ?>

                                <span style="background-color: #dc3545; color: white; padding: 5px 10px; border-radius: 5px;">
                                    <?php echo $producto['stock_actual']; ?> - Stock bajo
                                </span>

                            <?php else: ?>

                                <span style="background-color: #198754; color: white; padding: 5px 10px; border-radius: 5px;">
                                    <?php echo $producto['stock_actual']; ?>
                                </span>

                            <?php endif; ?>
                        </td>
                        <td><?php echo $producto['stock_minimo']; ?></td>
                        <td><?php echo $producto['estado']; ?></td>
                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    </div>

</body>
</html>