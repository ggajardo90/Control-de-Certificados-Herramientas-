<?php
session_start();
require_once "../../config/database.php";
require_once "../../fpdf/fpdf.php";

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
   CONSULTA
========================= */
$sql = "SELECT * FROM herramientas ORDER BY id DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$herramientas = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   PDF
========================= */
class PDF extends FPDF
{
    function Header()
    {
        /* =========================
           LOGO (CORRECTO)
        ========================= */
        $logo = $_SERVER['DOCUMENT_ROOT'] . '/certificados_herramientas/assets/img/logo.png';
        $this->Image($logo, 10, 8, 25);

        /* =========================
           TITULO
        ========================= */
        $this->SetFont('Arial', 'B', 15);
        $this->SetTextColor(40, 40, 40);

        $this->Cell(0, 10, utf8_decode('REPORTE DE CERTIFICADOS DE HERRAMIENTAS'), 0, 1, 'C');

        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 6, 'Fecha: ' . date('d/m/Y H:i'), 0, 1, 'C');

        if (isset($_SESSION["nombre"])) {
            $this->Cell(0, 6, utf8_decode('Generado por: ') . utf8_decode($_SESSION["nombre"]), 0, 1, 'C');
        }

        $this->Ln(5);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(120, 120, 120);

        $this->Cell(0, 10, utf8_decode('Página ') . $this->PageNo(), 0, 0, 'C');
    }
}

/* =========================
   CREAR PDF
========================= */
$pdf = new PDF('L', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 15);

/* =========================
   ENCABEZADO TABLA
========================= */
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetFillColor(52, 73, 94);
$pdf->SetTextColor(255, 255, 255);

$pdf->Cell(25, 10, 'Inventario', 1, 0, 'C', true);
$pdf->Cell(45, 10, utf8_decode('Herramienta'), 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Serie', 1, 0, 'C', true);
$pdf->Cell(25, 10, 'Marca', 1, 0, 'C', true);
$pdf->Cell(25, 10, 'Modelo', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Vencimiento', 1, 0, 'C', true);
$pdf->Cell(35, 10, 'Estado', 1, 0, 'C', true);
$pdf->Cell(35, 10, 'Certificado', 1, 1, 'C', true);

/* =========================
   DATOS
========================= */
$pdf->SetFont('Arial', '', 8);
$pdf->SetTextColor(0, 0, 0);

foreach ($herramientas as $fila) {

    $estado = $fila["estado_certificacion"];

    if ($estado == "Vigente") {
        $pdf->SetFillColor(212, 237, 218);
    } elseif ($estado == "Proxima a vencer") {
        $pdf->SetFillColor(255, 243, 205);
    } else {
        $pdf->SetFillColor(248, 215, 218);
    }

    $pdf->Cell(25, 8, $fila["numero_inventario"], 1, 0, 'C', true);
    $pdf->Cell(45, 8, utf8_decode($fila["nombre_herramienta"]), 1, 0, 'L', true);
    $pdf->Cell(30, 8, utf8_decode($fila["numero_serie"]), 1, 0, 'C', true);
    $pdf->Cell(25, 8, utf8_decode($fila["marca"]), 1, 0, 'C', true);
    $pdf->Cell(25, 8, utf8_decode($fila["modelo"]), 1, 0, 'C', true);
    $pdf->Cell(30, 8, $fila["fecha_vencimiento"], 1, 0, 'C', true);
    $pdf->Cell(35, 8, utf8_decode($estado), 1, 0, 'C', true);
    $pdf->Cell(35, 8, utf8_decode($fila["numero_certificado"]), 1, 1, 'C', true);
}

/* =========================
   SALIDA
========================= */
$pdf->Output("I", "Reporte_Herramientas.pdf");
exit;
