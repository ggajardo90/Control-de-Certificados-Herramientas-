<?php
session_start();
require_once "../../config/database.php";

$db = new Database();
$conn = $db->getConnection();

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $correo = trim($_POST["correo"]);
    $password = trim($_POST["password"]);

    $sql = "SELECT * FROM usuarios WHERE correo = :correo LIMIT 1";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":correo", $correo);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (password_verify($password, $usuario["password"])) {

            $_SESSION["usuario_id"] = $usuario["id"];
            $_SESSION["nombre"] = $usuario["nombre"];
            $_SESSION["rol"] = $usuario["rol"];

            header("Location: ../dashboard/index.php");
            exit;
        } else {
            $error = "Contraseña incorrecta";
        }
    } else {
        $error = "Usuario no encontrado";
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Sistema</title>

    <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/app.css">
</head>

<body>

    <div class="login-split">

        <!-- LADO IZQUIERDO -->
        <div class="login-left">
            <img src="../../assets/img/logo.png" alt="Logo Empresa" class="login-logo">
        </div>

        <!-- LADO DERECHO -->
        <div class="login-right">

            <div class="login-box">

                <h2 class="login-title">
                    Iniciar Sesión
                </h2>

                <?php if (!empty($error)) : ?>
                    <div class="alert alert-danger">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST">

                    <div class="mb-3">
                        <input
                            type="email"
                            name="correo"
                            class="form-control login-input"
                            placeholder="Correo"
                            required>
                    </div>

                    <div class="mb-4">
                        <input
                            type="password"
                            name="password"
                            class="form-control login-input"
                            placeholder="Contraseña"
                            required>
                    </div>

                    <button type="submit" class="btn login-btn">
                        INGRESAR
                    </button>

                </form>

            </div>

        </div>

    </div>

</body>

</html>