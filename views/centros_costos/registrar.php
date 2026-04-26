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

$mensaje = "";

/* =====================================
   GUARDAR CENTRO DE COSTO
===================================== */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $codigo = trim($_POST["codigo"]);
    $nombre = trim($_POST["nombre"]);

    /* VALIDAR DUPLICADO */
    $sql = "SELECT id FROM centros_costos WHERE codigo = :codigo LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ":codigo" => $codigo
    ]);

    if ($stmt->fetch()) {

        $mensaje = "⚠ Ya existe un centro de costo con ese código";
    } else {

        $sql = "INSERT INTO centros_costos (
                    codigo,
                    nombre,
                    estado
                ) VALUES (
                    :codigo,
                    :nombre,
                    'Activo'
                )";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ":codigo" => $codigo,
            ":nombre" => $nombre
        ]);

        $mensaje = "✅ Centro de costo registrado correctamente";
    }
}

/* LAYOUT */
include "../layouts/header.php";
include "../layouts/sidebar.php";
?>

<div class="content">

    <!-- NAVBAR -->
    <div class="navbar-custom d-flex justify-content-between align-items-center">
        <h5 class="m-0">🏢 Registrar Centro de Costo</h5>

        <a href="listar.php" class="btn btn-primary">
            Ver Listado
        </a>
    </div>

    <!-- CARD -->
    <div class="card-soft mt-3">

        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-info">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <div class="row g-3">

                <!-- CÓDIGO -->
                <div class="col-md-6">
                    <label class="form-label">
                        Código
                    </label>

                    <input
                        type="text"
                        name="codigo"
                        class="form-control"
                        placeholder="Ej: CC-001"
                        required>
                </div>

                <!-- NOMBRE -->
                <div class="col-md-6">
                    <label class="form-label">
                        Nombre del Centro de Costo
                    </label>

                    <input
                        type="text"
                        name="nombre"
                        class="form-control"
                        placeholder="Ej: Proyecto Talca"
                        required>
                </div>

            </div>

            <div class="mt-4">
                <button class="btn btn-success">
                    Guardar Centro de Costo
                </button>
            </div>

        </form>

    </div>

</div>

<?php include "../layouts/footer.php"; ?>