<?php
session_start();
require_once "../../config/database.php";

/* =========================
   VALIDAR SESIÓN
========================= */
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
    echo "<script>
        alert('Registro no válido');
        window.location='listar.php';
    </script>";
    exit;
}

$id = $_GET["id"];

/* =========================
   BUSCAR PDF ANTES DE ELIMINAR
========================= */
$sqlBuscar = "SELECT certificado_pdf FROM herramientas WHERE id = :id";
$stmtBuscar = $conn->prepare($sqlBuscar);
$stmtBuscar->execute([
    ":id" => $id
]);

$herramienta = $stmtBuscar->fetch(PDO::FETCH_ASSOC);

/* =========================
   SI NO EXISTE
========================= */
if (!$herramienta) {
    echo "<script>
        alert('La herramienta no existe');
        window.location='listar.php';
    </script>";
    exit;
}

/* =========================
   ELIMINAR PDF DEL SERVIDOR
========================= */
if (!empty($herramienta["certificado_pdf"])) {
    $rutaPdf = "../../uploads/pdf_certificados/" . $herramienta["certificado_pdf"];

    if (file_exists($rutaPdf)) {
        unlink($rutaPdf);
    }
}

/* =========================
   ELIMINAR REGISTRO
========================= */
$sqlEliminar = "DELETE FROM herramientas WHERE id = :id";
$stmtEliminar = $conn->prepare($sqlEliminar);

$stmtEliminar->execute([
    ":id" => $id
]);

/* =========================
   REDIRECCIÓN FINAL
========================= */
echo "<script>
    alert('Herramienta eliminada correctamente');
    window.location='listar.php';
</script>";
exit;
