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
    die("ID no válido");
}

$id = intval($_GET["id"]);

/* CONEXIÓN */
$db = new Database();
$conn = $db->getConnection();

/* CONSULTAR HERRAMIENTA */
$sql = "SELECT * FROM herramientas WHERE id = :id LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->execute([
    ":id" => $id
]);

$herramienta = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$herramienta) {
    die("Herramienta no encontrada");
}

/* DATOS */
$nombre     = strtoupper($herramienta["nombre_herramienta"]);
$serie      = strtoupper($herramienta["numero_serie"]);
$inventario = $herramienta["numero_inventario"];
$pdf        = $herramienta["certificado_pdf"];

/*
IMPORTANTE:
Cambiar por IP real del servidor
NO usar localhost
*/
$url_qr = "http://192.168.1.11/certificados_herramientas/uploads/pdf_certificados/" . $pdf;

/* QR */
$qr = "https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=" . urlencode($url_qr);

/* CÓDIGO DE BARRAS */
$barcode = "https://barcode.tec-it.com/barcode.ashx?data=" . $inventario . "&code=Code128&dpi=96";
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Etiqueta Zebra</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: white;
            font-family: Arial, sans-serif;
        }

        /*
        HOJA COMPLETA:
        103 mm ancho
        30 mm alto
        */
        .pagina {
            width: 103mm;
            height: 30mm;
            display: flex;
            flex-direction: row;
            gap: 3mm;
            padding: 0;
            margin: 0;
            overflow: hidden;
        }

        /*
        CADA ETIQUETA:
        50 x 30 mm
        */
        .etiqueta {
            width: 50mm;
            height: 30mm;
            padding: 2mm;
            background: white;
            overflow: hidden;
        }

        .nombre {
            text-align: center;
            font-size: 10px;
            font-weight: bold;
            line-height: 1.1;
            margin-bottom: 1mm;
        }

        .serie {
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 2mm;
        }

        .contenido {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .barcode-box {
            width: 68%;
            text-align: center;
        }

        .barcode-box img {
            width: 100%;
            height: 13mm;
            object-fit: contain;
        }

        .barcode-grande {
            transform: scale(1.08);
            transform-origin: center;
        }

        .inventario {
            font-size: 11px;
            font-weight: bold;
            margin-top: 1mm;
        }

        .qr-box {
            width: 28%;
            text-align: center;
        }

        .qr-box img {
            width: 14mm;
            height: 14mm;
        }

        .acciones {
            margin-top: 20px;
            text-align: center;
        }

        .btn-print {
            background: #111827;
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
        }

        @media print {

            .acciones {
                display: none;
            }

            body {
                margin: 0;
                padding: 0;
            }

            @page {
                size: 103mm 30mm;
                margin: 0;
            }
        }
    </style>
</head>

<body>

    <div class="pagina">

        <?php for ($i = 1; $i <= 2; $i++): ?>

            <div class="etiqueta">

                <div class="nombre">
                    <?= htmlspecialchars($nombre) ?>
                </div>

                <div class="serie">
                    <?= htmlspecialchars($serie) ?>
                </div>

                <div class="contenido">

                    <div class="barcode-box">
                        <img src="<?= $barcode ?>" alt="Código de barras" class="barcode-grande">

                        <div class="inventario">
                            <?= htmlspecialchars($inventario) ?>
                        </div>
                    </div>

                    <div class="qr-box">
                        <img src="<?= $qr ?>" alt="QR">
                    </div>

                </div>

            </div>

        <?php endfor; ?>

    </div>

    <div class="acciones">
        <button onclick="window.print()" class="btn-print">
            🖨 Imprimir 2 Etiquetas
        </button>
    </div>

</body>

</html>