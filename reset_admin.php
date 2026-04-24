<?php
require_once "config/database.php";

$db = new Database();
$conn = $db->getConnection();

$nuevaClave = password_hash("Gusa281917", PASSWORD_DEFAULT);

$sql = "UPDATE usuarios SET password = :password WHERE correo = 'admin@empresa.com'";
$stmt = $conn->prepare($sql);
$stmt->bindParam(":password", $nuevaClave);

if ($stmt->execute()) {
    echo "Clave reseteada correctamente a Gusa281917";
} else {
    echo "Error al resetear clave";
}