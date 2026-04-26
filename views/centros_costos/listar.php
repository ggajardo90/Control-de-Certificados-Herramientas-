<?php
session_start();
require_once "../../config/database.php";

/* PROTECCIÓN */
if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

/* CONEXIÓN */
$db = new Database();
$conn = $db->getConnection();

/* CONSULTA */
$sql = "SELECT * FROM centros_costos ORDER BY id DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$centros = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* LAYOUT */
include "../layouts/header.php";
include "../layouts/sidebar.php";
?>

<div class="content">

    <!-- NAVBAR -->
    <div class="navbar-custom d-flex justify-content-between align-items-center">
        <h5 class="m-0">🏢 Listado Centros de Costo</h5>

        <a href="registrar.php" class="btn btn-primary">
            + Nuevo Centro
        </a>
    </div>

    <!-- CARD -->
    <div class="card-soft mt-3">

        <div class="table-responsive">

            <table class="table align-middle">

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Estado</th>
                        <th>Fecha Registro</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if (count($centros) > 0): ?>

                        <?php foreach ($centros as $fila): ?>

                            <tr>

                                <td>
                                    <strong>#<?= $fila["id"] ?></strong>
                                </td>

                                <td>
                                    <?= $fila["codigo"] ?>
                                </td>

                                <td>
                                    <?= $fila["nombre"] ?>
                                </td>

                                <td>
                                    <span class="badge bg-success">
                                        <?= $fila["estado"] ?>
                                    </span>
                                </td>

                                <td>
                                    <?= $fila["fecha_registro"] ?>
                                </td>

                                <td>

                                    <!-- EDITAR -->
                                    <a
                                        href="editar.php?id=<?= $fila["id"] ?>"
                                        class="btn btn-sm btn-warning text-dark">

                                        <i class="bi bi-pencil-square"></i>

                                    </a>

                                    <!-- ELIMINAR -->
                                    <a
                                        href="eliminar.php?id=<?= $fila["id"] ?>"
                                        class="btn btn-sm btn-danger"
                                        onclick="return confirm('¿Deseas eliminar este centro de costo?')">

                                        <i class="bi bi-trash"></i>

                                    </a>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="6" class="text-center">
                                No hay centros de costo registrados
                            </td>
                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<?php include "../layouts/footer.php"; ?>