<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: ../sesion/login.php");
    exit();
}

require_once __DIR__ . '/../config/conexion.php';
$buscar = trim($_GET['buscar'] ?? '');

$sql = "SELECT
            producto.id_producto,
            producto.codigo,
            producto.nombre,
            producto.precio,
            producto.stock_actual,
            producto.stock_minimo,
            producto.estado,
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
        WHERE 1=1";
        
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

    $sql .= " GROUP BY producto.id_producto
            ORDER BY producto.nombre ASC";

    $consulta = $conexion->prepare($sql);
    $consulta->execute($parametros);
    $productos = $consulta->fetchAll(PDO::FETCH_ASSOC);

    $errores = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $productos_seleccionados = $_POST['productos'] ?? [];
        $accion = $_POST['accion'] ?? '';

            if (!in_array($accion, ['inhabilitar', 'habilitar'], true)) {
                $errores[] = "La acción solicitada no es válida.";
            }

            if (!is_array($productos_seleccionados) || count($productos_seleccionados) === 0) {
                $errores[] = "Debes seleccionar al menos un producto.";
            }

            $ids_productos = [];

            foreach ($productos_seleccionados as $id_producto) {

                if (
                    !is_scalar($id_producto) ||
                    filter_var($id_producto, FILTER_VALIDATE_INT) === false ||
                    $id_producto <= 0
                ) {
                    $errores[] = "Se recibió un identificador de producto no válido.";
                    break;
                }

                $ids_productos[] = (int) $id_producto;
            }

            $ids_productos = array_unique($ids_productos);

            if (empty($errores)) {

                try {

                    $conexion->beginTransaction();

                    if ($accion === 'inhabilitar') {

                        $sql = "UPDATE producto
                                SET estado = 'I'
                                WHERE id_producto = ?
                                AND estado = 'A'";

                    } 
                    else {

                        $sql = "UPDATE producto
                                SET estado = 'A'
                                WHERE id_producto = ?
                                AND estado = 'I'";

                    }

                    $consulta = $conexion->prepare($sql);

                    foreach ($ids_productos as $id_producto) {
                        $consulta->execute([$id_producto]);
                    }

                    $conexion->commit();

                    $_SESSION['mensaje'] = "Estados de productos actualizados correctamente.";

                    header("Location: listar.php");
                    exit();

                } 
                catch (PDOException $e) {

                    if ($conexion->inTransaction()) {
                        $conexion->rollBack();
                    }

                    $errores[] = "No fue posible modificar el estado de los productos.";
                }

            }

    }


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
        <title>Productos - TecnoStock</title>

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@6.0.0-alpha.1/dist/css/bootstrap.min.css"
            rel="stylesheet">
    </head>

    <body>

        <?php include '../layouts/navbar.php'; ?>

        <div class="container mt-4">

            <?php if (!empty($errores)): ?>
                <div class="alert alert-danger" role="alert">
                    <ul class="mb-0">
                        <?php foreach ($errores as $error): ?>
                            <li>
                                <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <div class="d-flex justify-content-between align-items-center mb-4">

                <h2 class="mb-0">Inhabilitar/Habilitar Productos</h2>

                <div class="d-flex align-items-center gap-2 mb-3">
                    <input
                        type="checkbox"
                        id="seleccionar_todos"
                        class="form-check-input seleccion-producto"
                    >
                    <label for="seleccionar_todos" class="form-check-label">
                        Seleccionar todos
                    </label>
                </div>

            </div>

            <form method="GET" class="mb-3">

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

            <form method="POST">

                <table class="table table-striped table-bordered mt-3">

                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Nombre</th>
                            <th>Precio</th>
                            <th>Categorías</th>
                            <th>Stock actual</th>
                            <th>Stock mínimo</th>
                            <th>Estado</th>
                            <th>Seleccionar</th>
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
                                <td><?php echo $producto['stock_minimo']; ?></td>
                                <td><?php echo $producto['estado']; ?></td>
                                <td class="text-center">
                                    <input
                                        type="checkbox"
                                        name="productos[]"
                                        value="<?php echo $producto['id_producto']; ?>"
                                        class="form-check-input seleccion-producto"
                                    >
                                </td>
                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

                <div class="d-flex justify-content-end gap-2 mt-3">

                    <button type="submit" name="accion" value="inhabilitar"
                            class="btn btn-danger">
                        Inhabilitar
                    </button>

                    <button type="submit" name="accion" value="habilitar"
                            class="btn btn-success">
                        Habilitar
                    </button>

                    <a href="/TecnoStock/productos/listar.php"
                    class="btn btn-secondary">
                        Cancelar
                    </a>

                </div>
                
            </form>

        </div>
        <script type="module"
                src="https://cdn.jsdelivr.net/npm/bootstrap@6.0.0-alpha.1/dist/js/bootstrap.bundle.min.js"
                crossorigin="anonymous">
        </script>

        

        <script>
            const seleccionarTodos = document.getElementById('seleccionar_todos');

            const casillasProductos = document.querySelectorAll('.seleccion-producto');

            seleccionarTodos.addEventListener('change', function() {
                casillasProductos.forEach(function(casilla) {
                    casilla.checked = seleccionarTodos.checked;
                });
            });

            casillasProductos.forEach(function(casilla) {
                casilla.addEventListener('change', function() {
                    seleccionarTodos.checked =
                        [...casillasProductos].every(function(elemento) {
                            return elemento.checked;
                        });
                });
            });
        </script>
    </body>
</html>