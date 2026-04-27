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

/* =====================================
   FILTROS
===================================== */
$filtro_centro = isset($_GET["centro_costo"]) ? trim($_GET["centro_costo"]) : "";
$filtro_responsable = isset($_GET["responsable"]) ? trim($_GET["responsable"]) : "";

/* =====================================
   CONSULTA BASE
   SOLO ASIGNACIONES ACTIVAS
   + TRAER CÓDIGO DEL CENTRO DE COSTO
===================================== */
$sql = "
    SELECT 
        a.id,
        a.centro_costo,
        cc.codigo AS codigo_centro_costo,
        a.responsable,
        a.fecha_salida,
        a.fecha_retorno,
        a.estado,
        h.nombre_herramienta,
        h.numero_inventario,
        h.numero_serie,
        h.marca,
        h.modelo
    FROM asignaciones a

    INNER JOIN asignacion_detalle ad 
        ON a.id = ad.asignacion_id

    INNER JOIN herramientas h 
        ON ad.herramienta_id = h.id

    LEFT JOIN centros_costos cc
        ON a.centro_costo = cc.nombre

    WHERE a.estado = 'Activa'
";

$params = [];

/* =====================================
   FILTRO CENTRO DE COSTO
===================================== */
if (!empty($filtro_centro)) {
    $sql .= " 
        AND (
            a.centro_costo LIKE :centro_costo
            OR cc.codigo LIKE :centro_costo
        )
    ";
    $params[":centro_costo"] = "%$filtro_centro%";
}

/* =====================================
   FILTRO RESPONSABLE
===================================== */
if (!empty($filtro_responsable)) {
    $sql .= " AND a.responsable LIKE :responsable ";
    $params[":responsable"] = "%$filtro_responsable%";
}

/* =====================================
   ORDEN
===================================== */
$sql .= " ORDER BY a.id DESC ";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$asignaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =====================================
   LAYOUT
===================================== */
include "../layouts/header.php";
include "../layouts/sidebar.php";
?>

<div class="content">

    <!-- NAVBAR -->
    <div class="navbar-custom d-flex justify-content-between align-items-center">
        <h5 class="m-0">📑 Herramientas Asignadas</h5>

        <a href="nueva.php" class="btn btn-primary">
            + Nueva Asignación
        </a>
    </div>

    <!-- FILTROS -->
    <div class="card-soft mt-3">

        <form method="GET">

            <div class="row g-3 align-items-end">

                <!-- FILTRO CENTRO DE COSTO -->
                <div class="col-md-5">
                    <label class="form-label">
                        Filtrar por Centro de Costo
                    </label>

                    <input
                        type="text"
                        name="centro_costo"
                        class="form-control"
                        placeholder="Ej: CC-001"
                        value="<?php echo htmlspecialchars($filtro_centro); ?>">
                </div>

                <!-- FILTRO RESPONSABLE -->
                <div class="col-md-5">
                    <label class="form-label">
                        Filtrar por Responsable
                    </label>

                    <input
                        type="text"
                        name="responsable"
                        class="form-control"
                        placeholder="Ej: Juan Pérez"
                        value="<?php echo htmlspecialchars($filtro_responsable); ?>">
                </div>

                <!-- BOTONES -->
                <div class="col-md-2">

                    <button class="btn btn-primary w-100">
                        Filtrar
                    </button>

                    <a href="listar.php" class="btn btn-secondary w-100 mt-2">
                        Limpiar
                    </a>

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
                        <th>ID</th>
                        <th>Centro de Costo</th>
                        <th>Responsable</th>
                        <th>Herramienta</th>
                        <th>Inventario</th>
                        <th>N° Serie</th>
                        <th>Marca</th>
                        <th>Modelo</th>
                        <th>Fecha Salida</th>
                        <th>Fecha Retorno</th>
                        <th>Días Asignados</th>
                        <th>Estado</th>
                        <th>Devolver</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if (count($asignaciones) > 0): ?>

                        <?php foreach ($asignaciones as $fila): ?>

                            <?php
                            $hoy = date("Y-m-d");

                            if (
                                $fila["fecha_retorno"] < $hoy &&
                                $fila["estado"] == "Activa"
                            ) {
                                $estado_visual = "Atrasada";
                                $color = "danger";
                            } else {
                                $estado_visual = "Activa";
                                $color = "warning";
                            }
                            ?>

                            <?php
                            $hoy = date("Y-m-d");

                            /* Estado visual */
                            if (
                                $fila["fecha_retorno"] < $hoy &&
                                $fila["estado"] == "Activa"
                            ) {
                                $estado_visual = "Atrasada";
                                $color = "danger";
                            } else {
                                $estado_visual = "Activa";
                                $color = "warning";
                            }

                            /* Calcular días asignados */
                            $fecha_salida = new DateTime($fila["fecha_salida"]);
                            $fecha_retorno = new DateTime($fila["fecha_retorno"]);
                            $dias_asignados = $fecha_salida->diff($fecha_retorno)->days;
                            ?>

                            <tr>

                                <!-- ID -->
                                <td>
                                    <strong>
                                        #<?= $fila["id"] ?>
                                    </strong>
                                </td>

                                <!-- CENTRO DE COSTO (SOLO CÓDIGO) -->
                                <td>
                                    <strong>
                                        <?= !empty($fila["codigo_centro_costo"])
                                            ? htmlspecialchars($fila["codigo_centro_costo"])
                                            : "-" ?>
                                    </strong>
                                </td>

                                <!-- RESPONSABLE -->
                                <td>
                                    <?= htmlspecialchars($fila["responsable"]) ?>
                                </td>

                                <!-- HERRAMIENTA -->
                                <td>
                                    <?= htmlspecialchars($fila["nombre_herramienta"]) ?>
                                </td>

                                <!-- INVENTARIO -->
                                <td>
                                    <strong>
                                        <?= htmlspecialchars($fila["numero_inventario"]) ?>
                                    </strong>
                                </td>

                                <!-- SERIE -->
                                <td>
                                    <?= htmlspecialchars($fila["numero_serie"]) ?>
                                </td>

                                <!-- MARCA -->
                                <td>
                                    <?= htmlspecialchars($fila["marca"]) ?>
                                </td>

                                <!-- MODELO -->
                                <td>
                                    <?= htmlspecialchars($fila["modelo"]) ?>
                                </td>

                                <!-- FECHA SALIDA -->
                                <td>
                                    <?= $fila["fecha_salida"] ?>
                                </td>

                                <!-- FECHA RETORNO -->
                                <td>
                                    <?= $fila["fecha_retorno"] ?>
                                </td>

                                <td>
                                    <span class="badge bg-info">
                                        <?= $dias_asignados ?> días
                                    </span>
                                </td>

                                <!-- ESTADO -->
                                <td>
                                    <span class="badge bg-<?= $color ?>">
                                        <?= $estado_visual ?>
                                    </span>
                                </td>

                                <!-- DEVOLVER -->
                                <td>
                                    <a
                                        href="devolucion.php?id=<?= $fila["id"] ?>"
                                        class="btn btn-sm btn-success"
                                        title="Devolver herramienta"
                                        onclick="return confirm('¿Confirmar devolución de herramienta?')">

                                        <i class="bi bi-arrow-return-left"></i>

                                    </a>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="12" class="text-center">
                                No hay herramientas asignadas actualmente
                            </td>
                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<?php include "../layouts/footer.php"; ?>