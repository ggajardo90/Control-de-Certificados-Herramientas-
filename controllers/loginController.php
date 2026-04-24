<?php
session_start();

require_once "../models/Usuario.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $correo = trim($_POST["correo"]);
    $password = trim($_POST["password"]);

    $usuarioModel = new Usuario();
    $usuario = $usuarioModel->login($correo);

    if (!$usuario) {
        die("❌ Usuario no encontrado");
    }

    if (!password_verify($password, $usuario["password"])) {
        die("❌ Contraseña incorrecta");
    }

    $_SESSION["usuario_id"] = $usuario["id"];
    $_SESSION["nombre"] = $usuario["nombre"];
    $_SESSION["rol"] = $usuario["rol"];

    header("Location: ../views/dashboard/index.php");
    exit;
}
