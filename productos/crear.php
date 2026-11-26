
<?php
session_start();
$mensaje = $_SESSION['mensaje'] ?? '';

unset($_SESSION['mensaje']);

if (!isset($_SESSION['usuario'])) {
    header("Location: ../sesion/login.php");
    exit();
}

require_once __DIR__ . '/../config/conexion.php';
$sql = "SELECT id_categoria, nombre
        FROM categoria
        WHERE estado = 'A'
        ORDER BY nombre ASC";

$consulta = $conexion->prepare($sql);
$consulta->execute();

$categorias = $consulta->fetchAll(PDO::FETCH_ASSOC);

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = trim($_POST['codigo'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio = $_POST['precio'] ?? '';
    $stock_actual = $_POST['stock_actual'] ?? '';
    $stock_minimo = $_POST['stock_minimo'] ?? '';
    $categorias_seleccionadas = $_POST['categorias'] ?? [];

    if ($codigo === '') {
        $errores[] = "El código es obligatorio.";
    }

    if ($nombre === '') {
        $errores[] = "El nombre es obligatorio.";
    }

    if (!is_numeric($precio) || (float)$precio < 0) {
        $errores[] = "El precio debe ser un número mayor o igual a cero.";
    }

    if (filter_var($stock_actual, FILTER_VALIDATE_INT) === false || $stock_actual < 0) {
        $errores[] = "El stock actual debe ser un entero mayor o igual a cero.";
    }

    if (filter_var($stock_minimo, FILTER_VALIDATE_INT) === false || $stock_minimo < 0) {
        $errores[] = "El stock mínimo debe ser un entero mayor o igual a cero.";
    }

    if (!is_array($categorias_seleccionadas) || count($categorias_seleccionadas) === 0) {
        $errores[] = "Debes seleccionar al menos una categoría.";
    }

    $sql = "SELECT id_producto
        FROM producto
        WHERE codigo = ?";

    $consulta = $conexion->prepare($sql);
    $consulta->execute([$codigo]);

    if ($consulta->fetch()) {
        $errores[] = "Ya existe un producto con ese código.";
    }

    $ids_categorias = [];

    if (is_array($categorias_seleccionadas)) {

        foreach ($categorias_seleccionadas as $id) {

            $id_validado = filter_var($id, FILTER_VALIDATE_INT);

            if ($id_validado === false || $id_validado <= 0) {
                $errores[] = "Se recibió un identificador de categoría inválido.";
                break;
            }

            $ids_categorias[] = $id_validado;
        }

        // Eliminar identificadores repetidos
        $ids_categorias = array_unique($ids_categorias);

        if (count($ids_categorias) > 0) {

            $marcadores = implode(',', array_fill(0, count($ids_categorias), '?'));

            $sql = "SELECT COUNT(*)
                    FROM categoria
                    WHERE estado = 'A'
                    AND id_categoria IN ($marcadores)";

            $consulta = $conexion->prepare($sql);
            $consulta->execute(array_values($ids_categorias));

            $cantidad_encontrada = $consulta->fetchColumn();

            if ($cantidad_encontrada != count($ids_categorias)) {
                $errores[] = "Una o más categorías no existen o están inactivas.";
            }
        }
    }

    if (empty($errores)) {

        try {
            $conexion->beginTransaction();

            $sql = "INSERT INTO producto
                    (codigo, nombre, descripcion, precio,
                    stock_actual, stock_minimo, estado)
                    VALUES (?, ?, ?, ?, ?, ?, 'A')";

            $consulta = $conexion->prepare($sql);

            $consulta->execute([
                $codigo,
                $nombre,
                $descripcion,
                $precio,
                $stock_actual,
                $stock_minimo
            ]);

            $id_producto = $conexion->lastInsertId();
            $sql_categoria = "INSERT INTO producto_categoria
                  (id_producto, id_categoria)
                  VALUES (?, ?)";

            $consulta_categoria = $conexion->prepare($sql_categoria);

            foreach ($ids_categorias as $id_categoria) {

                $consulta_categoria->execute([
                    $id_producto,
                    $id_categoria
                ]);

            }

            $conexion->commit();

            $_SESSION['mensaje'] = "Producto registrado correctamente.";

            header("Location: crear.php");
            exit();

        } 
        
        catch (PDOException $e) {

            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }

            $errores[] = "No fue posible registrar el producto.";
        }
    }

}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar producto - TecnoStock</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@6.0.0-alpha.1/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>

<body>

    <?php include __DIR__ . '/../layouts/navbar.php'; ?>

    <main class="container mt-4">
        <h2>Registrar producto</h2>
        <p>Completa los datos para agregar un nuevo producto al inventario.</p>

        <?php if (!empty($mensaje)): ?>

            <div class="alert alert-success" role="alert">
                <?php echo htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'); ?>
            </div>

        <?php endif; ?>


        <?php if (!empty($errores)): ?>

            <div class="alert alert-danger" role="alert">
                <strong>No se pudo registrar el producto:</strong>

                <ul class="mb-0">
                    <?php foreach ($errores as $error): ?>
                        <li>
                            <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

        <?php endif; ?>

        
        <form action="" method="POST">

            <div class="mb-3">
                <label for="codigo" class="form-label">Código del producto</label>
                <input type="text" class="form-control"
                    id="codigo" name="codigo"
                    placeholder="Ej: TEC-MOU-002" required>
            </div>

            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" class="form-control"
                    id="nombre" name="nombre"
                    placeholder="Nombre del producto" required>
            </div>

            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción</label>
                <textarea class="form-control"
                        id="descripcion" name="descripcion"
                        rows="3"></textarea>
            </div>

            <div class="mb-3">
                <label for="precio" class="form-label">Precio</label>
                <input type="number" class="form-control"
                    id="precio" name="precio"
                    min="0" step="0.01" required>
            </div>

            <div class="mb-3">
                <label for="stock_actual" class="form-label">Stock actual</label>
                <input type="number" class="form-control"
                    id="stock_actual" name="stock_actual"
                    min="0" step="1" required>
            </div>

            <div class="mb-3">
                <label for="stock_minimo" class="form-label">Stock mínimo</label>
                <input type="number" class="form-control"
                    id="stock_minimo" name="stock_minimo"
                    min="0" step="1" required>
            </div>


            <div class="mb-3">
                <label for="categorias" class="form-label">
                    Categorías
                </label>

                <select class="form-select"
                        id="categorias"
                        name="categorias[]"
                        multiple
                        required>

                    <?php foreach ($categorias as $categoria): ?>
                        <option value="<?php echo $categoria['id_categoria']; ?>">
                            <?php echo htmlspecialchars($categoria['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>

                </select>

                <div class="form-text">
                    Puedes seleccionar varias categorías manteniendo presionada
                    la tecla Ctrl (Windows) mientras haces clic.
                </div>
            </div>


            <button type="submit" class="btn btn-primary">
                Registrar producto
            </button>

            <a href="/TecnoStock/productos/listar.php"
            class="btn btn-secondary">
                Cancelar
            </a>

        </form>


    </main>

    <!-- Bootstrap JS -->
    <script type="module"
            src="https://cdn.jsdelivr.net/npm/bootstrap@6.0.0-alpha.1/dist/js/bootstrap.bundle.min.js"
            crossorigin="anonymous">
    </script>

</body>
</html>
