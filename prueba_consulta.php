
<?php

require_once "config/conexion.php";

try {

    $consulta = $conexion->query(
        "SELECT Codigo, Nombre, Precio, Stock_Actual
         FROM Producto"
    );

    $productos = $consulta->fetchAll(PDO::FETCH_ASSOC);

    echo "<h1>Productos de TecnoStock</h1>";

    foreach ($productos as $producto) {

        echo "<p>";
        echo "Código: " . htmlspecialchars($producto["Codigo"]) . "<br>";
        echo "Nombre: " . htmlspecialchars($producto["Nombre"]) . "<br>";
        echo "Precio: $" . $producto["Precio"] . "<br>";
        echo "Stock: " . $producto["Stock_Actual"];
        echo "</p><hr>";

    }

} catch (PDOException $e) {

    echo "Error al consultar productos: " . $e->getMessage();

}
?>
