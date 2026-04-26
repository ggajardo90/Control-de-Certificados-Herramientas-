<?php
session_start();
require_once "../../config/database.php";

/* PROTECCIÓN */
if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

/* VALIDAR ID */
if (!isset($_GET["id"]) || empty($_GET["id"])) {
    header("Location: listar.php");
    exit;
}

$id = intval($_GET["id"]);

/* CONEXIÓN */
$db = new Database();
$conn = $db->getConnection();

$mensaje = "";

/* =====================================
   OBTENER CENTRO DE COSTO
===================================== */
$sql = "SELECT * FROM centros_costos WHERE id = :id LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->execute([
    ":id" => $id
]);

$centro = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$centro) {
    header("Location: listar.php");
    exit;
}

/* =====================================
   ACTUALIZAR
===================================== */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $codigo = trim($_POST["codigo"]);
    $nombre = trim($_POST["nombre"]);
    $estado = trim($_POST["estado"]);

    /* VALIDAR CÓDIGO DUPLICADO */
    $sql = "SELECT id 
            FROM centros_costos 
            WHERE codigo = :codigo 
            AND id != :id
            LIMIT 1";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ":codigo" => $codigo,
        ":id" => $id
    ]);

    if ($stmt->fetch()) {

        $mensaje = "⚠ Ya existe otro centro de costo con ese código";
    } else {

        $sql = "UPDATE centros_costos SET
                    codigo = :codigo,
                    nombre = :nombre,
                    estado = :estado
                WHERE id = :id";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ":codigo" => $codigo,
            ":nombre" => $nombre,
            ":estado" => $estado,
            ":id" => $id
        ]);

        $mensaje = "✅ Centro de costo actualizado correctamente";

        /* RECARGAR DATOS */
        $sql = "SELECT * FROM centros_costos WHERE id = :id LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ":id" => $id
        ]);
        $centro = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

/* LAYOUT */
include "../layouts/header.php";
include "../layouts/sidebar.php";
?>

<div class="content">

    <!-- NAVBAR -->
    <div class="navbar-custom d-flex justify-content-between align-items-center">
        <h5 class="m-0">✏ Editar Centro de Costo</h5>

        <a href="listar.php" class="btn btn-secondary">
            Volver
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
                <div class="col-md-4">
                    <label class="form-label">
                        Código
                    </label>

                    <input
                        type="text"
                        name="codigo"
                        class="form-control"
                        value="<?php echo htmlspecialchars($centro["codigo"]); ?>"
                        required>
                </div>

                <!-- NOMBRE -->
                <div class="col-md-5">
                    <label class="form-label">
                        Nombre del Centro de Costo
                    </label>

                    <input
                        type="text"
                        name="nombre"
                        class="form-control"
                        value="<?php echo htmlspecialchars($centro["nombre"]); ?>"
                        required>
                </div>

                <!-- ESTADO -->
                <div class="col-md-3">
                    <label class="form-label">
                        Estado
                    </label>

                    <select
                        name="estado"
                        class="form-control"
                        required>

                        <option value="Activo"
                            <?php if ($centro["estado"] == "Activo") echo "selected"; ?>>
                            Activo
                        </option>

                        <option value="Inactivo"
                            <?php if ($centro["estado"] == "Inactivo") echo "selected"; ?>>
                            Inactivo
                        </option>

                    </select>
                </div>

            </div>

            <div class="mt-4">
                <button class="btn btn-success">
                    Guardar Cambios
                </button>
            </div>

        </form>

    </div>

</div>

<?php include "../layouts/footer.php"; ?>