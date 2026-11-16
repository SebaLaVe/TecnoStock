
<?php
session_start();

if (isset($_SESSION['usuario'])) {
    header("Location: ../index.php");
    exit();
}

$error = $_SESSION['error'] ?? '';

unset($_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="es">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Tecno Stock - Login</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
<body>
    <div class="min-vh-100 d-flex justify-content-center align-items-center">
        <section class="card shadow-sm" style="max-width: 320px; width: 100%">
            <div class="card-body">
                <h4 class="card-title text-center mb-4">Tecno Stock</h4>
                <form action="autenticador.php" method="POST" class="formulario">
                    <div class="mb-3">
                        <label for="correo" class="form-label">Correo</label>
                        <input
                            type="email"
                            class="form-control"
                            id="correo"
                            name="correo"
                            required
                        >
                    </div>
                    <div class="mb-3">
                        <label for="clave" class="form-label">Contraseña</label>
                        <input
                            type="password"
                            class="form-control"
                            id="clave"
                            name="clave"
                            required
                        >
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary w-100">
                            Iniciar sesión
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </div>
    <?php if ($error): ?>

        <div class="modal fade" id="errorModal" tabindex="-1" aria-hidden="true">

            <div class="modal-dialog modal-dialog-centered">

                <div class="modal-content">

                    <div class="modal-header border-0">
                        <h5 class="modal-title text-danger">
                            Error de autenticación
                        </h5>
                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Cerrar">
                        </button>
                    </div>
                    <div class="modal-body text-center">
                        <p class="mb-0">
                            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                        </p>
                    </div>

                    <div class="modal-footer border-0 justify-content-center">
                        <button
                            type="button"
                            class="btn btn-danger px-4"
                            data-bs-dismiss="modal">
                            Entendido
                        </button>
                    </div>
                </div>
            </div>
        </div>

    <?php endif; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const errorModal = document.getElementById('errorModal');

            if (errorModal) {
                const modal = new bootstrap.Modal(errorModal);
                modal.show();
            }

        });
    </script>
</body>
</html>
