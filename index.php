<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: sesion/login.php");
    exit();
}

?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TecnoStock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@6.0.0-alpha.1/dist/css/bootstrap.min.css"
      rel="stylesheet"
      integrity="sha384-B/GM4XqrwHnWXNOWMbloTmrYXZg10cakYGmpfsR/bbzQ6JAJI4ihuyADKLnBgrCe"
      crossorigin="anonymous">
    <meta charset="UTF-8">
</head>
<body>
    <?php include 'layouts/navbar.php'; ?>
    <script type="module"
        src="https://cdn.jsdelivr.net/npm/bootstrap@6.0.0-alpha.1/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-1a/pXj49ZQ1aHEmrJ+gMw1otqoVsYwlEnlD8mIfY2TV03r20Y0CN7uqx1tQogjPL"
        crossorigin="anonymous">
</script>
 <h2>Bienvenido a TecnoStock</h2>
</body>
</html>
