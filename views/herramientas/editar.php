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
   VALIDAR ID
========================= */
if (!isset($_GET["id"]) || empty($_GET["id"])) {
    header("Location: listar.php");
    exit;
}

$id = $_GET["id"];

/* =========================
   OBTENER DATOS ACTUALES
========================= */
$sql = "SELECT * FROM herramientas WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->execute([
    ":id" => $id
]);

$herramienta = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$herramienta) {
    echo "<script>alert('Registro no encontrado'); window.location='listar.php';</script>";
    exit;
}

/* =========================
   ACTUALIZAR
========================= */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nombre = $_POST["nombre"];
    $serie = $_POST["serie"];
    $marca = $_POST["marca"];
    $modelo = $_POST["modelo"];
    $fecha_cert = $_POST["fecha_cert"];
    $fecha_venc = $_POST["fecha_venc"];
    $numero_certificado = $_POST["numero_certificado"];

    /* ESTADO AUTOMÁTICO */
    $hoy = date("Y-m-d");

    if ($fecha_venc < $hoy) {
        $estado = "Vencida";
    } elseif ($fecha_venc <= date("Y-m-d", strtotime("+30 days"))) {
        $estado = "Proxima a vencer";
    } else {
        $estado = "Vigente";
    }

    /* PDF OPCIONAL */
    $pdf = $herramienta["certificado_pdf"];

    if (!empty($_FILES["pdf"]["name"])) {
        $pdf = time() . "_" . $_FILES["pdf"]["name"];

        move_uploaded_file(
            $_FILES["pdf"]["tmp_name"],
            "../../uploads/pdf_certificados/" . $pdf
        );
    }

    /* UPDATE */
    $sql = "UPDATE herramientas SET
        nombre_herramienta = :nom,
        numero_serie = :serie,
        marca = :marca,
        modelo = :modelo,
        fecha_certificacion = :fec1,
        fecha_vencimiento = :fec2,
        estado_certificacion = :estado,
        numero_certificado = :cert,
        certificado_pdf = :pdf
        WHERE id = :id";

    $stmt = $conn->prepare($sql);

    $stmt->execute([
        ":nom" => $nombre,
        ":serie" => $serie,
        ":marca" => $marca,
        ":modelo" => $modelo,
        ":fec1" => $fecha_cert,
        ":fec2" => $fecha_venc,
        ":estado" => $estado,
        ":cert" => $numero_certificado,
        ":pdf" => $pdf,
        ":id" => $id
    ]);

    echo "<script>
        alert('Herramienta actualizada correctamente');
        window.location='listar.php';
    </script>";
    exit;
}

/* =========================
   LAYOUTS
========================= */
include "../layouts/header.php";
include "../layouts/sidebar.php";
?>

<div class="content">

    <!-- NAVBAR -->
    <div class="navbar-custom d-flex justify-content-between align-items-center">
        <h5 class="m-0">✏ Editar Herramienta</h5>

        <span>
            Inventario:
            <strong><?php echo $herramienta["numero_inventario"]; ?></strong>
        </span>
    </div>

    <!-- CARD -->
    <div class="card-soft mt-3">

        <form method="POST" enctype="multipart/form-data">

            <div class="row g-3">

                <!-- NOMBRE -->
                <div class="col-md-6">
                    <label class="form-label">Nombre de la herramienta</label>
                    <input
                        type="text"
                        name="nombre"
                        class="form-control"
                        required
                        value="<?php echo $herramienta["nombre_herramienta"]; ?>">
                </div>

                <!-- SERIE -->
                <div class="col-md-6">
                    <label class="form-label">Número de serie</label>
                    <input
                        type="text"
                        name="serie"
                        class="form-control"
                        required
                        value="<?php echo $herramienta["numero_serie"]; ?>">
                </div>

                <!-- MARCA -->
                <div class="col-md-6">
                    <label class="form-label">Marca</label>
                    <input
                        type="text"
                        name="marca"
                        class="form-control"
                        value="<?php echo $herramienta["marca"]; ?>">
                </div>

                <!-- MODELO -->
                <div class="col-md-6">
                    <label class="form-label">Modelo</label>
                    <input
                        type="text"
                        name="modelo"
                        class="form-control"
                        value="<?php echo $herramienta["modelo"]; ?>">
                </div>

                <!-- FECHA CERT -->
                <div class="col-md-6">
                    <label class="form-label">Fecha Certificación</label>
                    <input
                        type="date"
                        name="fecha_cert"
                        class="form-control"
                        required
                        value="<?php echo $herramienta["fecha_certificacion"]; ?>">
                </div>

                <!-- FECHA VENC -->
                <div class="col-md-6">
                    <label class="form-label">Fecha Vencimiento</label>
                    <input
                        type="date"
                        name="fecha_venc"
                        class="form-control"
                        required
                        value="<?php echo $herramienta["fecha_vencimiento"]; ?>">
                </div>

                <!-- NUMERO CERTIFICADO -->
                <div class="col-md-6">
                    <label class="form-label">Número de Certificado</label>
                    <input
                        type="text"
                        name="numero_certificado"
                        class="form-control"
                        required
                        value="<?php echo $herramienta["numero_certificado"]; ?>">
                </div>

                <!-- PDF -->
                <div class="col-md-6">
                    <label class="form-label">Nuevo Certificado PDF (opcional)</label>
                    <input
                        type="file"
                        name="pdf"
                        class="form-control"
                        accept="application/pdf">
                </div>

            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    Actualizar Herramienta
                </button>

                <a href="listar.php" class="btn btn-secondary">
                    Cancelar
                </a>
            </div>

        </form>

    </div>

</div>

<?php include "../layouts/footer.php"; ?>