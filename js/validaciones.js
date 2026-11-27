
document.addEventListener("DOMContentLoaded", () => {

    // Validación de formularios de productos
    const formulariosProducto = [
        document.getElementById("formProducto"),
        document.getElementById("formEditarProducto")
    ];

    formulariosProducto.forEach(formulario => {

        if (!formulario) return;

        formulario.addEventListener("submit", (evento) => {

            const codigo = formulario.querySelector('[name="codigo"]');
            const nombre = formulario.querySelector('[name="nombre"]');
            const precio = formulario.querySelector('[name="precio"]');
            const stockMinimo = formulario.querySelector('[name="stock_minimo"]');
            const categorias = formulario.querySelector('[name="categorias[]"]');

            if (!codigo.value.trim() || !nombre.value.trim()) {
                evento.preventDefault();
                alert("El código y el nombre son obligatorios.");
                return;
            }

            if (precio.value === "" || !Number.isFinite(Number(precio.value)) || Number(precio.value) < 0) {
                evento.preventDefault();
                alert("El precio debe ser un número mayor o igual a cero.");
                precio.focus();
                return;
            }

            if (stockMinimo) {
                const valor = Number(stockMinimo.value);

                if (stockMinimo.value === "" || !Number.isInteger(valor) || valor < 0) {
                    evento.preventDefault();
                    alert("El stock mínimo debe ser un entero mayor o igual a cero.");
                    stockMinimo.focus();
                    return;
                }
            }

            // Solo existe en el formulario de creación
            const stockActual = formulario.querySelector('[name="stock_actual"]');

            if (stockActual) {
                const valor = Number(stockActual.value);

                if (stockActual.value === "" || !Number.isInteger(valor) || valor < 0) {
                    evento.preventDefault();
                    alert("El stock actual debe ser un entero mayor o igual a cero.");
                    stockActual.focus();
                    return;
                }
            }

            if (!categorias || categorias.selectedOptions.length === 0) {
                evento.preventDefault();
                alert("Debes seleccionar al menos una categoría.");
                categorias?.focus();
            }

        });

    });


    // Validación del formulario de movimientos
    const formularioMovimiento = document.getElementById("formMovimiento");

    if (formularioMovimiento) {

        formularioMovimiento.addEventListener("submit", (evento) => {

            const cantidad = formularioMovimiento.querySelector('[name="cantidad"]');
            const valor = Number(cantidad.value);

            if (
                cantidad.value === "" ||
                !Number.isInteger(valor) ||
                valor <= 0
            ) {
                evento.preventDefault();
                alert("La cantidad debe ser un número entero mayor que cero.");
                cantidad.focus();
            }

        });

    }

});
