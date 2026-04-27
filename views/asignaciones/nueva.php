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
   CARGAR CENTROS DE COSTO
===================================== */
$sql_centros = "SELECT * FROM centros_costos ORDER BY codigo ASC";
$stmt_centros = $conn->prepare($sql_centros);
$stmt_centros->execute();
$centros = $stmt_centros->fetchAll(PDO::FETCH_ASSOC);

/* =====================================
   GUARDAR ASIGNACIÓN MULTIPLE
===================================== */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $centro_costo  = trim($_POST["centro_costo"]);
    $responsable   = trim($_POST["responsable"]);
    $fecha_salida  = $_POST["fecha_salida"];
    $fecha_retorno = $_POST["fecha_retorno"];

    /* códigos separados por coma */
    $codigos = isset($_POST["codigos_barra"])
        ? $_POST["codigos_barra"]
        : "";

    $codigos = explode(",", $codigos);

    if (empty($centro_costo) || empty($responsable) || empty($fecha_salida) || empty($fecha_retorno)) {
        $mensaje = "❌ Complete todos los campos";
    } else {

        try {

            $conn->beginTransaction();

            /* =====================================
               CREAR ASIGNACIÓN PRINCIPAL
            ===================================== */
            $sql = "INSERT INTO asignaciones (
                        centro_costo,
                        responsable,
                        fecha_salida,
                        fecha_retorno,
                        estado
                    ) VALUES (
                        :centro,
                        :responsable,
                        :salida,
                        :retorno,
                        'Activa'
                    )";

            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ":centro" => $centro_costo,
                ":responsable" => $responsable,
                ":salida" => $fecha_salida,
                ":retorno" => $fecha_retorno
            ]);

            $asignacion_id = $conn->lastInsertId();

            $asignadas = 0;

            /* =====================================
               RECORRER CADA CÓDIGO ESCANEADO
            ===================================== */
            foreach ($codigos as $codigo_barra) {

                $codigo_barra = trim($codigo_barra);

                if (empty($codigo_barra)) {
                    continue;
                }

                /* buscar herramienta */
                $sql = "SELECT * FROM herramientas
                        WHERE numero_inventario = :codigo
                        LIMIT 1";

                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    ":codigo" => $codigo_barra
                ]);

                $herramienta = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$herramienta) {
                    continue;
                }

                /* validar certificación */
                if ($herramienta["estado_certificacion"] == "Vencida") {
                    continue;
                }

                /* validar disponibilidad */
                if ($herramienta["estado_operacional"] != "Disponible") {
                    continue;
                }

                /* guardar detalle */
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

                /* cambiar estado */
                $sql = "UPDATE herramientas
                        SET estado_operacional = 'Asignada en terreno'
                        WHERE id = :id";

                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    ":id" => $herramienta["id"]
                ]);

                $asignadas++;
            }

            if ($asignadas == 0) {
                $conn->rollBack();
                $mensaje = "⚠ No se pudo asignar ninguna herramienta";
            } else {
                $conn->commit();
                $mensaje = "✅ Se asignaron $asignadas herramientas correctamente";
            }
        } catch (Exception $e) {
            $conn->rollBack();
            $mensaje = "❌ Error al guardar asignación";
        }
    }
}

include "../layouts/header.php";
include "../layouts/sidebar.php";
?>

<div class="content">

    <div class="navbar-custom d-flex justify-content-between align-items-center">
        <h5 class="m-0">🚧 Nueva Asignación Múltiple</h5>
    </div>

    <div class="card-soft mt-3">

        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-info">
                <?= $mensaje ?>
            </div>
        <?php endif; ?>

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
                            Seleccionar centro de costo
                        </option>

                        <?php foreach ($centros as $c): ?>
                            <option value="<?= $c["nombre"] ?>">
                                <?= $c["codigo"] ?> - <?= $c["nombre"] ?>
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

                <!-- ESCANEO MÚLTIPLE -->
                <div class="col-md-12">
                    <label class="form-label">
                        Escanear Herramientas (lector barcode)
                    </label>

                    <input
                        type="text"
                        id="codigo_input"
                        class="form-control"
                        placeholder="Escanear y presionar Enter"
                        autofocus>

                    <small class="text-muted">
                        Cada escaneo se agregará automáticamente
                    </small>
                </div>

                <!-- LISTA -->
                <div class="col-md-12">
                    <label class="form-label">
                        Herramientas Escaneadas
                    </label>

                    <textarea
                        id="lista_codigos"
                        name="codigos_barra"
                        class="form-control"
                        rows="6"
                        readonly
                        required></textarea>
                </div>

            </div>

            <div class="mt-4">
                <button class="btn btn-primary">
                    Guardar Asignación
                </button>
            </div>

        </form>

    </div>

</div>

<script>
    document.getElementById("codigo_input").addEventListener("keypress", function(e) {
        if (e.key === "Enter") {
            e.preventDefault();

            let input = this.value.trim();
            let lista = document.getElementById("lista_codigos");

            if (input !== "") {
                if (lista.value === "") {
                    lista.value = input;
                } else {
                    lista.value += "," + input;
                }

                this.value = "";
            }
        }
    });
</script>

<?php include "../layouts/footer.php"; ?>