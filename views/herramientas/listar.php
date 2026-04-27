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

/* =========================
   CONSULTA SIN DUPLICADOS
   SOLO UNA FILA POR HERRAMIENTA
========================= */
$sql = "
    SELECT 
        h.*,
        MAX(a.estado) AS estado_asignacion,
        MAX(cc.codigo) AS codigo_centro_costo
    FROM herramientas h

    LEFT JOIN asignacion_detalle ad 
        ON h.id = ad.herramienta_id

    LEFT JOIN asignaciones a 
        ON ad.asignacion_id = a.id
        AND a.estado = 'Activa'

    LEFT JOIN centros_costos cc
        ON a.centro_costo = cc.nombre
";

$params = [];

/* =========================
   SI HAY BÚSQUEDA
========================= */
if (!empty($buscar)) {
    $sql .= "
        WHERE
            h.nombre_herramienta LIKE :buscar
            OR h.numero_serie LIKE :buscar
            OR h.numero_inventario LIKE :buscar
    ";

    $params[":buscar"] = "%$buscar%";
}

/* =========================
   GROUP BY + ORDEN
========================= */
$sql .= "
    GROUP BY h.id
    ORDER BY h.id DESC
";

$stmt = $conn->prepare($sql);
$stmt->execute($params);

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

            <a href="reporte_pdf.php" target="_blank" class="btn btn-danger btn-square">
                PDF
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

            <table class="table table-bordered align-middle text-center">

                <thead>
                    <tr>
                        <th>Inventario</th>
                        <th>Herramienta</th>
                        <th>N° Serie</th>
                        <th>Marca</th>
                        <th>Modelo</th>
                        <th>Fecha Vencimiento</th>
                        <th>Días</th>
                        <th>Certificación</th>
                        <th>Estado Operacional</th>
                        <th>Centro de Costo</th>
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
                                $estado_cert = "Vencida";
                            } elseif ($dias <= 30) {
                                $texto_dias = $dias;
                                $color_dias = "warning";
                                $estado_cert = "Por Vencer";
                            } else {
                                $texto_dias = $dias;
                                $color_dias = "success";
                                $estado_cert = "Vigente";
                            }

                            /* =========================
                               ESTADO OPERACIONAL
                            ========================= */
                            if ($fila["estado_operacional"] == "Asignada en terreno") {
                                $estado_op_color = "danger";
                                $estado_op = "Asignada";
                            } else {
                                $estado_op_color = "success";
                                $estado_op = "Disponible";
                            }
                            ?>

                            <tr>

                                <!-- INVENTARIO -->
                                <td>
                                    <strong>
                                        <?= $fila["numero_inventario"]; ?>
                                    </strong>
                                </td>

                                <!-- HERRAMIENTA -->
                                <td>
                                    <?= $fila["nombre_herramienta"]; ?>
                                </td>

                                <!-- SERIE -->
                                <td>
                                    <?= $fila["numero_serie"]; ?>
                                </td>

                                <!-- MARCA -->
                                <td>
                                    <?= $fila["marca"]; ?>
                                </td>

                                <!-- MODELO -->
                                <td>
                                    <?= $fila["modelo"]; ?>
                                </td>

                                <!-- FECHA -->
                                <td>
                                    <?= $fila["fecha_vencimiento"]; ?>
                                </td>

                                <!-- DÍAS -->
                                <td>
                                    <span class="badge bg-<?= $color_dias; ?>">
                                        <?= $texto_dias; ?>
                                    </span>
                                </td>

                                <!-- CERTIFICACIÓN -->
                                <td>
                                    <span class="badge bg-<?= $color_dias; ?>">
                                        <?= $estado_cert; ?>
                                    </span>
                                </td>

                                <!-- ESTADO OPERACIONAL -->
                                <td>
                                    <span class="badge bg-<?= $estado_op_color; ?>">
                                        <?= $estado_op; ?>
                                    </span>
                                </td>

                                <!-- CENTRO DE COSTO -->
                                <td>
                                    <strong>
                                        <?= !empty($fila["codigo_centro_costo"])
                                            ? $fila["codigo_centro_costo"]
                                            : "-" ?>
                                    </strong>
                                </td>

                                <!-- CERTIFICADO -->
                                <td>
                                    <?= $fila["numero_certificado"]; ?>
                                </td>

                                <!-- PDF -->
                                <td>
                                    <?php if (!empty($fila["certificado_pdf"])): ?>

                                        <a
                                            href="../../uploads/pdf_certificados/<?= $fila["certificado_pdf"]; ?>"
                                            target="_blank"
                                            class="btn btn-sm btn-info text-white"
                                            title="Ver Certificado PDF">
                                            📄
                                        </a>

                                    <?php else: ?>

                                        <span class="text-muted">—</span>

                                    <?php endif; ?>
                                </td>

                                <!-- ACCIONES -->
                                <td>

                                    <a
                                        href="editar.php?id=<?= $fila["id"]; ?>"
                                        class="btn btn-sm btn-warning"
                                        title="Editar">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>

                                    <a
                                        href="eliminar.php?id=<?= $fila["id"]; ?>"
                                        class="btn btn-sm btn-danger"
                                        title="Eliminar"
                                        onclick="return confirm('¿Deseas eliminar este registro?')">
                                        <i class="bi bi-trash-fill"></i>
                                    </a>

                                    <a
                                        href="etiqueta.php?id=<?= $fila['id']; ?>"
                                        target="_blank"
                                        class="btn btn-sm btn-dark"
                                        title="Imprimir Etiqueta">
                                        <i class="bi bi-printer-fill"></i>
                                    </a>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="13" class="text-center">
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