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

/* =====================================
   BUSCAR ASIGNACIÓN + HERRAMIENTA
===================================== */
$sql = "
    SELECT 
        a.id,
        a.estado,
        ad.herramienta_id
    FROM asignaciones a
    INNER JOIN asignacion_detalle ad
        ON a.id = ad.asignacion_id
    WHERE a.id = :id
    LIMIT 1
";

$stmt = $conn->prepare($sql);
$stmt->execute([
    ":id" => $id
]);

$asignacion = $stmt->fetch(PDO::FETCH_ASSOC);

/* VALIDAR EXISTENCIA */
if (!$asignacion) {
    header("Location: listar.php");
    exit;
}

/* EVITAR DOBLE DEVOLUCIÓN */
if ($asignacion["estado"] == "Devuelta") {
    header("Location: listar.php");
    exit;
}

/* =====================================
   DEVOLVER ASIGNACIÓN
===================================== */

/* 1. cambiar asignación */
$sql = "
    UPDATE asignaciones
    SET estado = 'Devuelta'
    WHERE id = :id
";

$stmt = $conn->prepare($sql);
$stmt->execute([
    ":id" => $id
]);

/* 2. liberar herramienta */
$sql = "
    UPDATE herramientas
    SET estado_operacional = 'Disponible'
    WHERE id = :herramienta_id
";

$stmt = $conn->prepare($sql);
$stmt->execute([
    ":herramienta_id" => $asignacion["herramienta_id"]
]);

/* REDIRECCIÓN */
header("Location: listar.php?devuelta=1");
exit;
