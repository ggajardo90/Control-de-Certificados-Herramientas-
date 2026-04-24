<?php
session_start();

require_once "../../config/database.php";
include "../layouts/header.php";
include "../layouts/sidebar.php";

/* PROTECCIÓN DE SESIÓN */
if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$db = new Database();
$conn = $db->getConnection();

/* CONSULTAS */
$total = $conn->query("SELECT COUNT(*) FROM herramientas")->fetchColumn();

$vigentes = $conn->query("
    SELECT COUNT(*) FROM herramientas 
    WHERE estado_certificacion = 'Vigente'
")->fetchColumn();

$proximas = $conn->query("
    SELECT COUNT(*) FROM herramientas 
    WHERE estado_certificacion = 'Proxima a vencer'
")->fetchColumn();

$vencidas = $conn->query("
    SELECT COUNT(*) FROM herramientas 
    WHERE estado_certificacion = 'Vencida'
")->fetchColumn();
?>

<!-- CONTENIDO -->
<div class="content">

    <!-- NAVBAR -->
    <div class="navbar-custom d-flex justify-content-between align-items-center">
        <h5 class="m-0">📊 Dashboard</h5>

        <div>
            👤 Usuario: <strong><?php echo $_SESSION["nombre"]; ?></strong>
        </div>
    </div>

    <!-- CARDS -->
    <div class="row g-4">

        <!-- TOTAL -->
        <div class="col-md-3">
            <div class="card card-soft p-3">
                <h6>Total Herramientas</h6>
                <h2><?php echo $total; ?></h2>
            </div>
        </div>

        <!-- VIGENTES -->
        <div class="col-md-3">
            <div class="card card-soft p-3">
                <h6>Vigentes</h6>
                <h2 style="color:#22c55e;"><?php echo $vigentes; ?></h2>
            </div>
        </div>

        <!-- PROXIMAS -->
        <div class="col-md-3">
            <div class="card card-soft p-3">
                <h6>Por Vencer</h6>
                <h2 style="color:#facc15;"><?php echo $proximas; ?></h2>
            </div>
        </div>

        <!-- VENCIDAS -->
        <div class="col-md-3">
            <div class="card card-soft p-3">
                <h6>Vencidas</h6>
                <h2 style="color:#ef4444;"><?php echo $vencidas; ?></h2>
            </div>
        </div>

    </div>

</div>

<?php include "../layouts/footer.php"; ?>