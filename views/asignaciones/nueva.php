<?php
session_start();
require_once "../../config/database.php";

if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$db = new Database();
$conn = $db->getConnection();

$mensaje = "";

/* =====================================
   CARGAR CENTROS DE COSTO ACTIVOS
===================================== */
$sql_centros = "SELECT * 
                FROM centros_costos 
                WHERE estado = 'Activo'
                ORDER BY nombre ASC";

$stmt_centros = $conn->prepare($sql_centros);
$stmt_centros->execute();
$centros_costos = $stmt_centros->fetchAll(PDO::FETCH_ASSOC);

/* =====================================
   GUARDAR ASIGNACIÓN + ESCANEO
===================================== */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $centro_costo  = trim($_POST["centro_costo"]);
    $responsable   = trim($_POST["responsable"]);
    $fecha_salida  = $_POST["fecha_salida"];
    $fecha_retorno = $_POST["fecha_retorno"];
    $codigo_barra  = trim($_POST["codigo_barra"]);

    /* =====================================
       BUSCAR HERRAMIENTA POR INVENTARIO
    ===================================== */
    $sql = "SELECT * FROM herramientas
            WHERE numero_inventario = :codigo
            LIMIT 1";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ":codigo" => $codigo_barra
    ]);

    $herramienta = $stmt->fetch(PDO::FETCH_ASSOC);

    /* =====================================
       VALIDACIONES
    ===================================== */
    if (!$herramienta) {

        $mensaje = "❌ Herramienta no encontrada";
    } else {

        /*
           Se permite:
           - Vigente
           - Próxima a vencer

           No se permite:
           - Vencida
        */
        if ($herramienta["estado_certificacion"] == "Vencida") {

            $mensaje = "⚠ La herramienta está vencida y no puede asignarse";
        } elseif ($herramienta["estado_operacional"] != "Disponible") {

            $mensaje = "⚠ La herramienta no está disponible para asignación";
        } else {

            /* =====================================
               CREAR ASIGNACIÓN
            ===================================== */
            $sql = "INSERT INTO asignaciones (
                        centro_costo,
                        responsable,
                        fecha_salida,
                        fecha_retorno
                    ) VALUES (
                        :centro,
                        :responsable,
                        :salida,
                        :retorno
                    )";

            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ":centro" => $centro_costo,
                ":responsable" => $responsable,
                ":salida" => $fecha_salida,
                ":retorno" => $fecha_retorno
            ]);

            $asignacion_id = $conn->lastInsertId();

            /* =====================================
               GUARDAR DETALLE
            ===================================== */
            $sql = "INSERT INTO asignacion_detalle (
                        asignacion_id,
                        herramienta_id,
                        codigo_barra
                    ) VALUES (
                        :asignacion,
                        :herramienta,
                        :codigo
                    )";

            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ":asignacion" => $asignacion_id,
                ":herramienta" => $herramienta["id"],
                ":codigo" => $codigo_barra
            ]);

            /* =====================================
               CAMBIAR ESTADO OPERACIONAL
            ===================================== */
            $sql = "UPDATE herramientas
                    SET estado_operacional = 'Asignada en terreno'
                    WHERE id = :id";

            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ":id" => $herramienta["id"]
            ]);

            $mensaje = "✅ Herramienta asignada correctamente";
        }
    }
}

include "../layouts/header.php";
include "../layouts/sidebar.php";
?>

<div class="content">

    <!-- NAVBAR -->
    <div class="navbar-custom d-flex justify-content-between align-items-center">
        <h5 class="m-0">🚧 Nueva Asignación de Herramientas</h5>
    </div>

    <!-- CARD -->
    <div class="card-soft mt-3">

        <!-- MENSAJE -->
        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-info">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <!-- FORMULARIO -->
        <form method="POST">

            <div class="row g-3">

                <!-- CENTRO DE COSTO -->
                <div class="col-md-6">
                    <label class="form-label">
                        Centro de Costo
                    </label>

                    <select
                        name="centro_costo"
                        class="form-control"
                        required>

                        <option value="">
                            Seleccionar Centro de Costo
                        </option>

                        <?php foreach ($centros_costos as $centro): ?>
                            <option value="<?= $centro["nombre"] ?>">
                                <?= $centro["codigo"] ?> - <?= $centro["nombre"] ?>
                            </option>
                        <?php endforeach; ?>

                    </select>
                </div>

                <!-- RESPONSABLE -->
                <div class="col-md-6">
                    <label class="form-label">
                        Responsable
                    </label>

                    <input
                        type="text"
                        name="responsable"
                        class="form-control"
                        required>
                </div>

                <!-- FECHA SALIDA -->
                <div class="col-md-6">
                    <label class="form-label">
                        Fecha de Salida
                    </label>

                    <input
                        type="date"
                        name="fecha_salida"
                        class="form-control"
                        required>
                </div>

                <!-- FECHA RETORNO -->
                <div class="col-md-6">
                    <label class="form-label">
                        Fecha de Retorno
                    </label>

                    <input
                        type="date"
                        name="fecha_retorno"
                        class="form-control"
                        required>
                </div>

                <!-- ESCANEO BARCODE -->
                <div class="col-md-12">
                    <label class="form-label">
                        Escanear Herramienta (Código de Barra)
                    </label>

                    <input
                        type="text"
                        name="codigo_barra"
                        class="form-control"
                        placeholder="Escanear con pistola barcode"
                        autofocus
                        required>
                </div>

            </div>

            <!-- BOTÓN -->
            <div class="mt-4">
                <button class="btn btn-primary">
                    Guardar Asignación
                </button>
            </div>

        </form>

    </div>

</div>

<?php include "../layouts/footer.php"; ?>