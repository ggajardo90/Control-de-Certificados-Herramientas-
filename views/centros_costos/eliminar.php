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
   VALIDAR SI EXISTE
===================================== */
$sql = "SELECT id FROM centros_costos WHERE id = :id LIMIT 1";
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
   ELIMINAR
===================================== */
$sql = "DELETE FROM centros_costos WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->execute([
    ":id" => $id
]);

/* REDIRECCIÓN */
header("Location: listar.php?eliminado=1");
exit;
