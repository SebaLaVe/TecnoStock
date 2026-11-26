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
            id_producto,
            codigo,
            nombre,
            descripcion,
            precio,
            stock_minimo,
            estado
        FROM producto
        WHERE id_producto = ?";

$consulta = $conexion->prepare($sql);
$consulta->execute([$id_producto]);

$producto = $consulta->fetch(PDO::FETCH_ASSOC);

if (!$producto) {
    die("El producto no existe.");
}

$sql = "SELECT id_categoria, nombre
        FROM categoria
        WHERE estado = 'A'
        ORDER BY nombre ASC";

$consulta = $conexion->prepare($sql);
$consulta->execute();
$categorias = $consulta->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT id_categoria
        FROM producto_categoria
        WHERE id_producto = ?";

$consulta = $conexion->prepare($sql);
$consulta->execute([$id_producto]);
$categorias_producto = $consulta->fetchAll(PDO::FETCH_COLUMN);
$categorias_marcadas = $categorias_producto;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $codigo = trim($_POST['codigo'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');

    $precio = $_POST['precio'] ?? '';
    $stock_minimo = $_POST['stock_minimo'] ?? '';

    $categorias_seleccionadas = $_POST['categorias'] ?? [];
    $categorias_marcadas = is_array($categorias_seleccionadas)
    ? $categorias_seleccionadas
    : [];

    //validacion de los campos

    $errores = [];

    if ($codigo === '') {
        $errores[] = "El código es obligatorio.";
    }

    if ($nombre === '') {
        $errores[] = "El nombre es obligatorio.";
    }

    if (!is_numeric($precio) || $precio < 0) {
        $errores[] = "El precio debe ser un número mayor o igual a cero.";
    }

    if (filter_var($stock_minimo, FILTER_VALIDATE_INT) === false || $stock_minimo < 0) {
        $errores[] = "El stock mínimo debe ser un entero mayor o igual a cero.";
    }

    $sql = "SELECT id_producto
        FROM producto
        WHERE codigo = ?
        AND id_producto != ?";

    $consulta = $conexion->prepare($sql);
    $consulta->execute([$codigo, $id_producto]);

    if ($consulta->fetch()) {
        $errores[] = "El código ingresado ya pertenece a otro producto.";
    }

    if (!is_array($categorias_seleccionadas) || count($categorias_seleccionadas) === 0) {

        $errores[] = "Debes seleccionar al menos una categoría.";

        $categorias_seleccionadas = [];

    }

    $ids_categorias = [];

    foreach ($categorias_seleccionadas as $id_categoria) {

    if (!is_scalar($id_categoria) ||
        filter_var($id_categoria, FILTER_VALIDATE_INT) === false ||
        $id_categoria <= 0) {

        $errores[] = "Se recibió un identificador de categoría no válido.";
        break;
    }

        if (filter_var($id_categoria, FILTER_VALIDATE_INT) === false || $id_categoria <= 0) {
            $errores[] = "Se recibió un identificador de categoría no válido.";
            break;
        }

        $ids_categorias[] = (int) $id_categoria;
    }

    $ids_categorias = array_unique($ids_categorias);


    if (!empty($ids_categorias)) {

        $marcadores = implode(',', array_fill(0, count($ids_categorias), '?'));

        $sql = "SELECT id_categoria
                FROM categoria
                WHERE estado = 'A'
                AND id_categoria IN ($marcadores)";

        $consulta = $conexion->prepare($sql);
        $consulta->execute($ids_categorias);

        $categorias_validas = $consulta->fetchAll(PDO::FETCH_COLUMN);

        if (count($categorias_validas) !== count($ids_categorias)) {
            $errores[] = "Una o más categorías seleccionadas no existen o están inactivas.";
        }
    }

    // Si no hay errores, proceder a actualizar el producto
    if (empty($errores)) {

        try {

            $conexion->beginTransaction();

            $sql = "UPDATE producto
                    SET codigo = ?,
                        nombre = ?,
                        descripcion = ?,
                        precio = ?,
                        stock_minimo = ?
                    WHERE id_producto = ?";

            $consulta = $conexion->prepare($sql);

            $consulta->execute([
                $codigo,
                $nombre,
                $descripcion,
                $precio,
                $stock_minimo,
                $id_producto
            ]);

            $sql = "DELETE FROM producto_categoria
                    WHERE id_producto = ?";

            $consulta = $conexion->prepare($sql);
            $consulta->execute([$id_producto]);
            $sql = "INSERT INTO producto_categoria
                    (id_producto, id_categoria)
                    VALUES (?, ?)";

            $consulta = $conexion->prepare($sql);

            foreach ($ids_categorias as $id_categoria) {
                $consulta->execute([$id_producto, $id_categoria]);
            }

            $conexion->commit();

            $_SESSION['mensaje'] = "Producto actualizado correctamente.";

            header("Location: listar.php");
            exit();

        }
         catch (PDOException $e) {

            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }

            $errores[] = "No fue posible modificar el producto.";

        }

    }

}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Producto - TecnoStock</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@6.0.0-alpha.1/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/../layouts/navbar.php'; ?>
    

    <div class="container mt-4">

        <h2>Editar producto</h2>

        <?php if (!empty($mensaje)): ?>

            <div class="alert alert-success" role="alert">
                <?php echo htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'); ?>
            </div>

        <?php endif; ?>


        <?php if (!empty($errores)): ?>

            <div class="alert alert-danger" role="alert">
                <strong>No se pudo modificar el producto:</strong>

                <ul class="mb-0">
                    <?php foreach ($errores as $error): ?>
                        <li>
                            <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

        <?php endif; ?>

        <form method="POST">

            <div class="mb-3">
                <label class="form-label">Código</label>
                <input type="text" name="codigo"
                    class="form-control"
                    value="<?php echo htmlspecialchars($codigo ?? $producto['codigo'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Nombre</label>
                <input type="text" name="nombre"
                    class="form-control"
                    value="<?php echo htmlspecialchars($nombre ?? $producto['nombre'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Descripción</label>
                <textarea name="descripcion"
                        class="form-control"><?php echo htmlspecialchars($descripcion ?? $producto['descripcion'], ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Precio</label>
                <input type="number" name="precio"
                    class="form-control"
                    step="0.01"
                    value="<?php echo htmlspecialchars($precio ?? $producto['precio'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Stock mínimo</label>
                <input type="number" name="stock_minimo"
                    class="form-control"
                    value="<?php echo htmlspecialchars($stock_minimo ?? $producto['stock_minimo'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Categorías</label>

                <select name="categorias[]" class="form-select" multiple required>

                    <?php foreach ($categorias as $categoria): ?>
                        <option value="<?php echo $categoria['id_categoria']; ?>"
                            <?php echo in_array(
                                $categoria['id_categoria'],
                                $categorias_marcadas
                            ) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($categoria['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>


                </select>

                <div class="form-text">
                    Mantén presionada la tecla Ctrl para seleccionar varias categorías.
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                Guardar cambios
            </button>

            <a href="/TecnoStock/productos/listar.php"
            class="btn btn-secondary">
                Cancelar
            </a>

            

            

        </form>

    </div>
    <script type="module"
        src="https://cdn.jsdelivr.net/npm/bootstrap@6.0.0-alpha.1/dist/js/bootstrap.bundle.min.js"
        crossorigin="anonymous">
    </script>
</body>
</html>