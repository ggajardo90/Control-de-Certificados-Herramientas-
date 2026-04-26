<?php
session_start();
require_once "../../config/database.php";

/* =========================
   PROTECCIÓN
========================= */
if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

/* =========================
   LAYOUT
========================= */
include "../layouts/header.php";
include "../layouts/sidebar.php";

/* =========================
   CONEXIÓN
========================= */
$db = new Database();
$conn = $db->getConnection();

/* =========================
   FECHAS
========================= */
$hoy = date("Y-m-d");
$hoy_30 = date("Y-m-d", strtotime("+30 days"));

/* =========================
   CONTADORES
========================= */
$total = $conn->query("
    SELECT COUNT(*) 
    FROM herramientas
")->fetchColumn();

$vigentes = $conn->query("
    SELECT COUNT(*) 
    FROM herramientas
    WHERE estado_certificacion = 'Vigente'
")->fetchColumn();

$proximas = $conn->query("
    SELECT COUNT(*) 
    FROM herramientas
    WHERE estado_certificacion = 'Proxima a vencer'
")->fetchColumn();

$vencidas = $conn->query("
    SELECT COUNT(*) 
    FROM herramientas
    WHERE estado_certificacion = 'Vencida'
")->fetchColumn();

/* =========================
   ALERTAS REALES
========================= */

/* VENCIDAS */
$stmt = $conn->prepare("
    SELECT COUNT(*) 
    FROM herramientas
    WHERE fecha_vencimiento < :hoy
");

$stmt->execute([
    ":hoy" => $hoy
]);

$vencidas_real = $stmt->fetchColumn();

/* PRÓXIMAS */
$stmt = $conn->prepare("
    SELECT COUNT(*) 
    FROM herramientas
    WHERE fecha_vencimiento BETWEEN :hoy AND :hoy30
");

$stmt->execute([
    ":hoy" => $hoy,
    ":hoy30" => $hoy_30
]);

$proximas_real = $stmt->fetchColumn();
?>

<div class="content">

    <!-- NAVBAR -->
    <div class="navbar-custom d-flex justify-content-between align-items-center">
        <h5 class="m-0">📊 Dashboard</h5>

        <div>
            👤 Usuario:
            <strong><?= $_SESSION["nombre"]; ?></strong>
        </div>
    </div>

    <!-- ALERTAS -->
    <div class="row g-3 mt-3">

        <!-- VENCIDAS -->
        <div class="col-md-6">
            <a href="../herramientas/reportes/vencidas.php" style="text-decoration: none;">
                <div class="card text-white bg-danger shadow-sm border-0">
                    <div class="card-body text-center py-3">
                        <h6 class="m-0">🔴 Certificaciones Vencidas</h6>
                        <h2 class="m-0"><?= $vencidas_real ?></h2>
                    </div>
                </div>
            </a>
        </div>

        <!-- PRÓXIMAS -->
        <div class="col-md-6">
            <a href="../herramientas/reportes/proximas.php" style="text-decoration: none;">
                <div class="card bg-warning shadow-sm border-0">
                    <div class="card-body text-center py-3">
                        <h6 class="m-0 text-dark">🟡 Próximas a Vencer</h6>
                        <h2 class="m-0 text-dark"><?= $proximas_real ?></h2>
                    </div>
                </div>
            </a>
        </div>

    </div>

    <!-- CARDS PRINCIPALES -->
    <div class="row g-4 mt-4">

        <div class="col-md-3">
            <div class="card card-soft p-3">
                <h6>Total Herramientas</h6>
                <h2><?= $total ?></h2>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-soft p-3">
                <h6>Vigentes</h6>
                <h2 style="color:#22c55e;">
                    <?= $vigentes ?>
                </h2>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-soft p-3">
                <h6>Por Vencer</h6>
                <h2 style="color:#facc15;">
                    <?= $proximas ?>
                </h2>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-soft p-3">
                <h6>Vencidas</h6>
                <h2 style="color:#ef4444;">
                    <?= $vencidas ?>
                </h2>
            </div>
        </div>

    </div>

</div>

<?php include "../layouts/footer.php"; ?>