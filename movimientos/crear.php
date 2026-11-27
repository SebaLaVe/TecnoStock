<?php

    session_start();

    if (!isset($_SESSION['usuario'])) {
        header("Location: ../sesion/login.php");
        exit();
    }

    require_once __DIR__ . '/../config/conexion.php';

    $id_producto = $_GET['id'] ?? null;

    if ($id_producto === null || !filter_var($id_producto, FILTER_VALIDATE_INT) || $id_producto <= 0) {
        die("ID de producto no válido.");
    }

    $sql = "SELECT
                producto.id_producto,
                producto.codigo,
                producto.nombre,
                producto.descripcion,
                producto.precio,
                producto.stock_actual,
                producto.stock_minimo,
                GROUP_CONCAT(
                    DISTINCT categoria.nombre
                    ORDER BY categoria.nombre
                    SEPARATOR ', '
                ) AS categorias
            FROM producto

            LEFT JOIN producto_categoria pc
                ON producto.id_producto = pc.id_producto

            LEFT JOIN categoria
                ON pc.id_categoria = categoria.id_categoria

            WHERE producto.id_producto = ?
            AND producto.estado = 'A'
            GROUP BY producto.id_producto";

            $consulta = $conexion->prepare($sql);
            $consulta->execute([$id_producto]);
            $producto = $consulta->fetch(PDO::FETCH_ASSOC);

            $errores = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $cantidad = $_POST['cantidad'] ?? '';
        $observacion = trim($_POST['observacion'] ?? '');
        $accion = $_POST['accion'] ?? '';

        // Validar acción
        if (!in_array($accion, ['entrada', 'salida'], true)) {
            $errores[] = "Tipo de movimiento no válido.";
        }

        // Validar cantidad
        $cantidad_validada = filter_var($cantidad, FILTER_VALIDATE_INT);

        if ($cantidad_validada === false || $cantidad_validada <= 0) {
            $errores[] = "La cantidad debe ser un número entero mayor que cero.";
        }

        if (empty($errores)) {

            try {
                $conexion->beginTransaction();

                // Bloquear el producto mientras procesamos el movimiento
                $sql = "SELECT stock_actual
                        FROM producto
                        WHERE id_producto = ?
                        AND estado = 'A'
                        FOR UPDATE";

                $consulta = $conexion->prepare($sql);
                $consulta->execute([$id_producto]);

                $producto_actual = $consulta->fetch(PDO::FETCH_ASSOC);

                if (!$producto_actual) {
                    throw new Exception("El producto no existe o está inhabilitado.");
                }

                $stock_actual = (int) $producto_actual['stock_actual'];

                if ($accion === 'salida' && $cantidad_validada > $stock_actual) {

                    throw new Exception("No hay stock suficiente para realizar esta salida.");

                }

                // Calcular el nuevo stock
                if ($accion === 'entrada') {
                    $stock_nuevo = $stock_actual + $cantidad_validada;
                    $tipo = 'ENTRADA';
                } else {
                    $stock_nuevo = $stock_actual - $cantidad_validada;
                    $tipo = 'SALIDA';
                }

                // Actualizar stock del producto
                $sql = "UPDATE producto
                        SET stock_actual = ?
                        WHERE id_producto = ?";

                $consulta = $conexion->prepare($sql);
                $consulta->execute([$stock_nuevo, $id_producto]);

                // Registrar movimiento
                $sql = "INSERT INTO movimiento
                        (tipo, cantidad, observacion, id_usuario, id_producto)
                        VALUES (?, ?, ?, 
                                (SELECT id_usuario FROM usuario WHERE correo = ?),
                                ?)";

                $consulta = $conexion->prepare($sql);
                $consulta->execute([
                    $tipo,
                    $cantidad_validada,
                    $observacion,
                    $_SESSION['usuario'],
                    $id_producto
                ]);

                $conexion->commit();

                $_SESSION['mensaje'] = "Movimiento registrado correctamente.";

                header("Location: listar.php");
                exit();

            } catch (Exception $e) {

                if ($conexion->inTransaction()) {
                    $conexion->rollBack();
                }

                $errores[] = $e->getMessage();
            }
        }

    }
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
        
        <style>
            /* Color normal */
            .btn-editar {
                background-color: #4d4d4d;
                border-color: #4d4d4d;
                color: #fff;
            }

            /* Color al pasar el mouse */
            .btn-editar:hover {
                background-color: #252525;
                border-color: #252525;
                color: #fff;
            }
        </style>
</head>

<body>

    <?php include __DIR__ . '/../layouts/navbar.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">

            <?php if (!empty($mensaje)): ?>
                <div class="alert alert-success" role="alert">
                    <?php echo htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($errores)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php foreach ($errores as $error): ?>
                        <p class="mb-0">
                            <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                        </p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <h2 class="mb-0">Producto seleccionado:</h2>

        </div>

        <table class="table table-striped table-bordered mt-3">

            <thead>
                <tr>
                    <th>Código</th>
                    <th>Nombre</th>
                    <th>Precio</th>
                    <th>Categorías</th>
                    <th>Stock actual</th>
                    <th>Stock mínimo</th>
                </tr>
            </thead>

            <tbody>

                <?php if ($producto): ?>

                    <tr>
                        <td><?php echo htmlspecialchars($producto['codigo']); ?></td>
                        <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                        <td>$<?php echo number_format($producto['precio'], 0, ',', '.'); ?></td>
                        <td><?php echo htmlspecialchars($producto['categorias'] ?? 'Sin categoría', ENT_QUOTES, 'UTF-8'); ?></td>
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
                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

        <form method="POST">

            <div class="mb-3">
                <label for="cantidad" class="form-label">Cantidad</label>
                <input
                    type="number"
                    class="form-control"
                    id="cantidad"
                    name="cantidad"
                    min="1"
                    required
                >
            </div>

            <div class="mb-3">
                <label for="observacion" class="form-label">Observación</label>
                <textarea
                    class="form-control"
                    id="observacion"
                    name="observacion"
                    rows="3"
                ></textarea>
            </div>

            <button
                type="submit"
                name="accion"
                value="entrada"
                class="btn btn-success">
                Registrar entrada
            </button>

            <button
                type="submit"
                name="accion"
                value="salida"
                class="btn btn-danger">
                Registrar salida
            </button>

            <a href="/TecnoStock/movimientos/seleccionar_producto.php"
                class="btn btn-secondary">
                Volver
            </a>

        </form>

    </div>
<script type="module"
        src="https://cdn.jsdelivr.net/npm/bootstrap@6.0.0-alpha.1/dist/js/bootstrap.bundle.min.js"
        crossorigin="anonymous">
</script>
</body>
</html>