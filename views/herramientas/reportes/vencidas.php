<!-- vencidas.php -->

<?php
session_start();

require_once __DIR__ . "/../../../config/database.php";

/* PROTECCIÓN */
if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../../auth/login.php");
    exit;
}

/* LAYOUT */
include __DIR__ . "/../../layouts/header.php";
include __DIR__ . "/../../layouts/sidebar.php";

/* CONEXIÓN */
$db = new Database();
$conn = $db->getConnection();

/* FECHA ACTUAL */
$hoy = date('Y-m-d');

/* CONSULTA VENCIDAS */
$sql = "
    SELECT * FROM herramientas
    WHERE fecha_vencimiento < :hoy
    ORDER BY fecha_vencimiento ASC
";

$stmt = $conn->prepare($sql);
$stmt->execute([
    ":hoy" => $hoy
]);

$herramientas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content">

    <!-- NAVBAR -->
    <div class="navbar-custom d-flex justify-content-between align-items-center">
        <h5 class="m-0">🔴 Herramientas Vencidas</h5>

        <a href="../../dashboard/index.php" class="btn btn-secondary btn-sm">
            Volver
        </a>
    </div>

    <!-- CARD -->
    <div class="card-soft mt-3">

        <div class="table-responsive">

            <table class="table align-middle">

                <thead>
                    <tr>
                        <th>Inventario</th>
                        <th>Herramienta</th>
                        <th>Serie</th>
                        <th>Marca</th>
                        <th>Modelo</th>
                        <th>Fecha Vencimiento</th>
                        <th>Estado</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if (count($herramientas) > 0): ?>

                        <?php foreach ($herramientas as $h): ?>

                            <tr>
                                <td>
                                    <strong><?= $h["numero_inventario"] ?></strong>
                                </td>

                                <td><?= $h["nombre_herramienta"] ?></td>

                                <td><?= $h["numero_serie"] ?></td>

                                <td><?= $h["marca"] ?></td>

                                <td><?= $h["modelo"] ?></td>

                                <td><?= $h["fecha_vencimiento"] ?></td>

                                <td>
                                    <span class="badge bg-danger">
                                        Vencida
                                    </span>
                                </td>
                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="7" class="text-center">
                                No hay herramientas vencidas
                            </td>
                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<?php include __DIR__ . "/../../layouts/footer.php"; ?>