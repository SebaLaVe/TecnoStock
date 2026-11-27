<nav class="navbar bg-1 fg-2">
    <div class="container-fluid">

        <a class="navbar-brand" href="/TecnoStock/index.php">
            TecnoStock
        </a>

        <button class="btn-icon navbar-toggler"
                type="button"
                data-bs-toggle="drawer"
                data-bs-target="#menuTecnoStock"
                aria-controls="menuTecnoStock"
                aria-expanded="false"
                aria-label="Abrir menú">

            <span class="navbar-toggler-icon" aria-hidden="true"></span>
        </button>

        <dialog class="drawer drawer-end"
                id="menuTecnoStock"
                aria-labelledby="menuTecnoStockLabel">

            <div class="drawer-header">
                <h5 class="drawer-title" id="menuTecnoStockLabel">
                    TecnoStock
                </h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="drawer"
                        aria-label="Cerrar">
                </button>
            </div>

            <div class="drawer-body">
                <div class="mb-3">
                    <small class="text-body-secondary">Sesión iniciada como:</small>

                     <p class="fw-bold mb-0">
                        <?php echo htmlspecialchars($_SESSION['usuario'], ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                </div>
                <ul class="navbar-nav me-auto mb-2">

                    <li class="nav-item">
                        <a class="nav-link active" href="/TecnoStock/index.php">
                            Inicio
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="/TecnoStock/productos/listar.php">
                            Productos
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="/TecnoStock/movimientos/listar.php">
                            Movimientos
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="/TecnoStock/sesion/logout.php">
                            Cerrar sesión
                        </a>
                    </li>

                </ul>
            </div>

        </dialog>
    </div>
</nav>