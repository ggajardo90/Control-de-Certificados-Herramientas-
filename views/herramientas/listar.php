<?php
session_start();
require_once "../../config/database.php";

if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

/* =========================
   CONEXIÓN
========================= */
$db = new Database();
$conn = $db->getConnection();

/* =========================
   FILTRO DE BÚSQUEDA
========================= */
$buscar = isset($_GET["buscar"]) ? trim($_GET["buscar"]) : "";

if (!empty($buscar)) {
    $sql = "SELECT * FROM herramientas 
            WHERE nombre_herramienta LIKE :buscar
            OR numero_serie LIKE :buscar
            OR numero_inventario LIKE :buscar
            ORDER BY id DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ":buscar" => "%$buscar%"
    ]);
} else {
    $sql = "SELECT * FROM herramientas ORDER BY id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
}

$herramientas = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   LAYOUTS
========================= */
include "../layouts/header.php";
include "../layouts/sidebar.php";
?>

<div class="content">

    <!-- NAVBAR -->
    <div class="navbar-custom d-flex justify-content-between align-items-center flex-wrap gap-3">
        <h5 class="m-0">📋 Listado de Herramientas</h5>

        <div class="d-flex gap-2">

            <a href="registrar.php" class="btn btn-primary">
                + Nueva Herramienta
            </a>

            <a href="exportar_excel.php" class="btn btn-success btn-square">
                Excel
            </a>

        </div>
    </div>

    <!-- FILTRO -->
    <div class="card-soft mt-3 p-4">

        <form method="GET">

            <div class="row g-3 align-items-end">

                <div class="col-md-10">
                    <label class="form-label">
                        Buscar por nombre, serie o inventario
                    </label>

                    <input
                        type="text"
                        name="buscar"
                        class="form-control"
                        placeholder="Ej: Taladro / 12345 / 1001"
                        value="<?php echo htmlspecialchars($buscar); ?>">
                </div>

                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        Buscar
                    </button>
                </div>

            </div>

        </form>

    </div>

    <!-- TABLA -->
    <div class="card-soft mt-3">

        <div class="table-responsive">

            <table class="table align-middle">

                <thead>
                    <tr>
                        <th>Inventario</th>
                        <th>Herramienta</th>
                        <th>N° Serie</th>
                        <th>Marca</th>
                        <th>Modelo</th>
                        <th>Fecha Vencimiento</th>
                        <th>Días</th>
                        <th>Estado</th>
                        <th>N° Certificado</th>
                        <th>PDF</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if (count($herramientas) > 0): ?>

                        <?php foreach ($herramientas as $fila): ?>

                            <?php
                            $fecha_venc = new DateTime($fila["fecha_vencimiento"]);
                            $hoy = new DateTime();

                            $diferencia = $hoy->diff($fecha_venc);
                            $dias = (int)$diferencia->format("%r%a");

                            if ($dias < 0) {
                                $texto_dias = abs($dias);
                                $color_dias = "danger";
                                $estado = "Vencida";
                            } elseif ($dias <= 30) {
                                $texto_dias = $dias;
                                $color_dias = "warning";
                                $estado = "Por Vencer";
                            } else {
                                $texto_dias = $dias;
                                $color_dias = "success";
                                $estado = "Vigente";
                            }
                            ?>

                            <tr>

                                <!-- INVENTARIO -->
                                <td>
                                    <strong>
                                        <?php echo $fila["numero_inventario"]; ?>
                                    </strong>
                                </td>

                                <!-- HERRAMIENTA -->
                                <td>
                                    <?php echo $fila["nombre_herramienta"]; ?>
                                </td>

                                <!-- SERIE -->
                                <td>
                                    <?php echo $fila["numero_serie"]; ?>
                                </td>

                                <!-- MARCA -->
                                <td>
                                    <?php echo $fila["marca"]; ?>
                                </td>

                                <!-- MODELO -->
                                <td>
                                    <?php echo $fila["modelo"]; ?>
                                </td>

                                <!-- FECHA -->
                                <td>
                                    <?php echo $fila["fecha_vencimiento"]; ?>
                                </td>

                                <!-- DÍAS -->
                                <td>
                                    <span class="badge bg-<?php echo $color_dias; ?>">
                                        <?php echo $texto_dias; ?>
                                    </span>
                                </td>

                                <!-- ESTADO -->
                                <td>
                                    <span class="badge bg-<?php echo $color_dias; ?>">
                                        <?php echo $estado; ?>
                                    </span>
                                </td>

                                <!-- CERTIFICADO -->
                                <td>
                                    <?php echo $fila["numero_certificado"]; ?>
                                </td>

                                <!-- PDF CON ICONO -->
                                <td>
                                    <?php if (!empty($fila["certificado_pdf"])): ?>

                                        <a
                                            href="../../uploads/pdf_certificados/<?php echo $fila["certificado_pdf"]; ?>"
                                            target="_blank"
                                            class="btn btn-sm btn-info text-white"
                                            title="Ver Certificado PDF">
                                            📄
                                        </a>

                                    <?php else: ?>

                                        <span class="text-muted">
                                            —
                                        </span>

                                    <?php endif; ?>
                                </td>

                                <!-- ACCIONES -->
                                <td>

                                    <a
                                        href="editar.php?id=<?php echo $fila["id"]; ?>"
                                        class="btn btn-sm btn-warning text-dark">
                                        Editar
                                    </a>

                                    <a
                                        href="eliminar.php?id=<?php echo $fila["id"]; ?>"
                                        class="btn btn-sm btn-danger"
                                        onclick="return confirm('¿Deseas eliminar este registro?')">
                                        Eliminar
                                    </a>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="11" class="text-center">
                                No hay herramientas registradas
                            </td>
                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<?php include "../layouts/footer.php"; ?>