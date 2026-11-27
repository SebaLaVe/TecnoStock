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
            producto.id_producto,
            producto.codigo,
            producto.nombre,
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

        WHERE 1=1 AND producto.estado = 'A'";
        

$parametros = [];

if ($buscar !== '') {
    $sql .= " AND (
        producto.codigo LIKE :codigo
        OR producto.nombre LIKE :nombre
        OR categoria.nombre LIKE :categoria
    )";

    $parametros[':codigo'] = "%$buscar%";
    $parametros[':nombre'] = "%$buscar%";
    $parametros[':categoria'] = "%$buscar%";
}

$sql .= " GROUP BY producto.id_producto ORDER BY
    CASE
        WHEN producto.estado = 'A' THEN 0
        ELSE 1
    END,
    producto.nombre ASC";

$consulta = $conexion->prepare($sql);
$consulta->execute($parametros);

$productos = $consulta->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-success" role="alert">
            <?php echo htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>
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

    <?php include '../layouts/navbar.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">

            <h2 class="mb-0">Selecciona un producto</h2>

        </div>

        <form method="GET" action="seleccionar_producto.php" class="mb-3">

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

        </form>

        <table class="table table-striped table-bordered mt-3">

            <thead>
                <tr>
                    <th>Código</th>
                    <th>Nombre</th>
                    <th>Precio</th>
                    <th>Categorías</th>
                    <th>Stock actual</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>

                <?php foreach ($productos as $producto): ?>

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

                        <td>
                            <a href="crear.php?id=<?php echo $producto['id_producto']; ?>"
                                class="btn btn-warning btn-sm btn-editar">
                                Seleccionar
                            </a>
                        </td>
                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>


        <a href="/TecnoStock/movimientos/listar.php"
            class="btn btn-secondary">
            Cancelar
        </a>

    </div>
<script type="module"
        src="https://cdn.jsdelivr.net/npm/bootstrap@6.0.0-alpha.1/dist/js/bootstrap.bundle.min.js"
        crossorigin="anonymous">
</script>
</body>
</html>