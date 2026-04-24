<?php
session_start();
require_once "../../config/database.php";

if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

/* CONEXIÓN */
$db = new Database();
$conn = $db->getConnection();

/* =========================
   INVENTARIO AUTOMÁTICO
========================= */
$stmt = $conn->query("SELECT MAX(numero_inventario) AS maximo FROM herramientas");
$result = $stmt->fetch(PDO::FETCH_ASSOC);

$numero_inventario = $result["maximo"]
    ? $result["maximo"] + 1
    : 1000;

/* =========================
   GUARDAR HERRAMIENTA
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

    /* SUBIR PDF */
    $pdf = null;

    if (!empty($_FILES["pdf"]["name"])) {
        $pdf = time() . "_" . $_FILES["pdf"]["name"];

        move_uploaded_file(
            $_FILES["pdf"]["tmp_name"],
            "../../uploads/pdf_certificados/" . $pdf
        );
    }

    /* INSERT */
    $sql = "INSERT INTO herramientas (
        numero_inventario,
        numero_certificado,
        nombre_herramienta,
        numero_serie,
        marca,
        modelo,
        fecha_certificacion,
        fecha_vencimiento,
        estado_certificacion,
        certificado_pdf
    ) VALUES (
        :inv,
        :cert,
        :nom,
        :serie,
        :marca,
        :modelo,
        :fec1,
        :fec2,
        :estado,
        :pdf
    )";

    $stmt = $conn->prepare($sql);

    $stmt->execute([
        ":inv" => $numero_inventario,
        ":cert" => $numero_certificado,
        ":nom" => $nombre,
        ":serie" => $serie,
        ":marca" => $marca,
        ":modelo" => $modelo,
        ":fec1" => $fecha_cert,
        ":fec2" => $fecha_venc,
        ":estado" => $estado,
        ":pdf" => $pdf
    ]);

    echo "<script>
        alert('Herramienta registrada correctamente');
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

<!-- =========================
     CONTENIDO
========================= -->
<div class="content">

    <!-- NAVBAR -->
    <div class="navbar-custom d-flex justify-content-between align-items-center">
        <h5 class="m-0">➕ Registrar Herramienta</h5>

        <span>
            Inventario:
            <strong><?php echo $numero_inventario; ?></strong>
        </span>
    </div>

    <!-- CARD PRINCIPAL -->
    <div class="card-soft mt-3">

        <form method="POST" enctype="multipart/form-data">

            <div class="row g-3">

                <!-- NOMBRE -->
                <div class="col-md-6">
                    <label class="form-label">
                        Nombre de la herramienta
                    </label>
                    <input
                        type="text"
                        name="nombre"
                        class="form-control"
                        required>
                </div>

                <!-- NUMERO DE SERIE -->
                <div class="col-md-6">
                    <label class="form-label">
                        Número de serie
                    </label>
                    <input
                        type="text"
                        name="serie"
                        class="form-control"
                        required>
                </div>

                <!-- MARCA -->
                <div class="col-md-6">
                    <label class="form-label">
                        Marca
                    </label>
                    <input
                        type="text"
                        name="marca"
                        class="form-control">
                </div>

                <!-- MODELO -->
                <div class="col-md-6">
                    <label class="form-label">
                        Modelo
                    </label>
                    <input
                        type="text"
                        name="modelo"
                        class="form-control">
                </div>

                <!-- FECHA CERTIFICACION -->
                <div class="col-md-6">
                    <label class="form-label">
                        Fecha Certificación
                    </label>
                    <input
                        type="date"
                        name="fecha_cert"
                        class="form-control"
                        required>
                </div>

                <!-- FECHA VENCIMIENTO -->
                <div class="col-md-6">
                    <label class="form-label">
                        Fecha Vencimiento
                    </label>
                    <input
                        type="date"
                        name="fecha_venc"
                        class="form-control"
                        required>
                </div>

                <!-- NUMERO CERTIFICADO -->
                <div class="col-md-6">
                    <label class="form-label">
                        Número de Certificado
                    </label>
                    <input
                        type="text"
                        name="numero_certificado"
                        class="form-control"
                        required>
                </div>

                <!-- PDF -->
                <div class="col-md-6">
                    <label class="form-label">
                        Certificado PDF
                    </label>
                    <input
                        type="file"
                        name="pdf"
                        class="form-control"
                        accept="application/pdf">
                </div>

            </div>

            <!-- BOTÓN -->
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    Guardar Herramienta
                </button>
            </div>

        </form>

    </div>

</div>

<?php include "../layouts/footer.php"; ?>