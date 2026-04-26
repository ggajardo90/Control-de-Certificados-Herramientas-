<?php
session_start();
require_once "../../config/database.php";

/* =========================
   PROTECCIÓN
========================= */
if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

/* =========================
   VALIDAR ID
========================= */
if (!isset($_GET["id"]) || empty($_GET["id"])) {
    die("ID no válido");
}

$id = $_GET["id"];

/* =========================
   CONEXIÓN
========================= */
$db = new Database();
$conn = $db->getConnection();

/* =========================
   CONSULTAR HERRAMIENTA
========================= */
$sql = "SELECT * FROM herramientas WHERE id = :id LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->execute([
    ":id" => $id
]);

$herramienta = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$herramienta) {
    die("Herramienta no encontrada");
}

/* =========================
   DATOS
========================= */
$nombre      = $herramienta["nombre_herramienta"];
$serie       = $herramienta["numero_serie"];
$inventario  = $herramienta["numero_inventario"];
$pdf         = $herramienta["certificado_pdf"];

/* =====================================================
   QR QUE FUNCIONE DESDE CELULAR

   IMPORTANTE:
   NO usar localhost porque desde celular NO funciona.

   Debes cambiar:
   192.168.1.50

   por la IP real de tu PC en la red local
   (cmd → ipconfig → IPv4)
===================================================== */

$url_qr = "http://192.168.1.11/certificados_herramientas/uploads/pdf_certificados/" . $pdf;

/* QR */
$qr = "https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=" . urlencode($url_qr);

/* Código de barras */
$barcode = "https://barcode.tec-it.com/barcode.ashx?data=" . $inventario . "&code=Code128&dpi=96";
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Etiqueta Zebra Doble</title>

    <style>
        body {
            margin: 0;
            padding: 0;
            background: white;
            font-family: Arial, sans-serif;
        }

        /* =========================================
           HOJA COMPLETA
           103 mm x 50 mm
           2 etiquetas
           separación horizontal: 3 mm
        ========================================= */
        .pagina {
            width: 103mm;
            height: 50mm;
            display: flex;
            align-items: flex-start;
            justify-content: flex-start;
            gap: 3mm;
            padding: 0;
            margin: 0;
            box-sizing: border-box;
        }

        /* =========================================
           CADA ETIQUETA
           50 mm x 30 mm
        ========================================= */
        .etiqueta {
            width: 50mm;
            height: 30mm;
            padding: 2mm;
            box-sizing: border-box;
            overflow: hidden;
            background: white;
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
            width: 65%;
            text-align: center;
        }

        .barcode-box img {
            width: 100%;
            height: 9mm;
            object-fit: contain;
        }

        .inventario {
            font-size: 11px;
            font-weight: bold;
            margin-top: 1mm;
        }

        .qr-box {
            width: 30%;
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
            font-size: 14px;
            cursor: pointer;
        }

        .btn-print:hover {
            background: black;
        }

        /* =========================================
           IMPRESIÓN
        ========================================= */
        @media print {

            .acciones {
                display: none;
            }

            body {
                margin: 0;
                padding: 0;
            }

            @page {
                size: 103mm 50mm;
                margin: 0;
            }
        }
    </style>
</head>

<body>

    <div class="pagina">

        <!-- =====================================
         ETIQUETA 1
    ====================================== -->
        <div class="etiqueta">

            <div class="nombre">
                <?php echo $nombre; ?>
            </div>

            <div class="serie">
                <?php echo $serie; ?>
            </div>

            <div class="contenido">

                <div class="barcode-box">
                    <img src="<?php echo $barcode; ?>" alt="Código de barras">

                    <div class="inventario">
                        <?php echo $inventario; ?>
                    </div>
                </div>

                <div class="qr-box">
                    <img src="<?php echo $qr; ?>" alt="QR">
                </div>

            </div>

        </div>

        <!-- =====================================
         ETIQUETA 2 (COPIA)
    ====================================== -->
        <div class="etiqueta">

            <div class="nombre">
                <?php echo $nombre; ?>
            </div>

            <div class="serie">
                <?php echo $serie; ?>
            </div>

            <div class="contenido">

                <div class="barcode-box">
                    <img src="<?php echo $barcode; ?>" alt="Código de barras">

                    <div class="inventario">
                        <?php echo $inventario; ?>
                    </div>
                </div>

                <div class="qr-box">
                    <img src="<?php echo $qr; ?>" alt="QR">
                </div>

            </div>

        </div>

    </div>

    <div class="acciones">
        <button onclick="window.print()" class="btn-print">
            🖨 Imprimir 2 Etiquetas
        </button>
    </div>

</body>

</html>