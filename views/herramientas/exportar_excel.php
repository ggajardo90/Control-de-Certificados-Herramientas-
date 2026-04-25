<?php
session_start();
require_once "../../config/database.php";
require_once "../../vendor/autoload.php";

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
   CONSULTAR DATOS
========================= */
$sql = "SELECT 
            numero_inventario,
            nombre_herramienta,
            numero_serie,
            marca,
            modelo,
            fecha_certificacion,
            fecha_vencimiento,
            estado_certificacion,
            numero_certificado
        FROM herramientas
        ORDER BY id DESC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$herramientas = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   CREAR EXCEL
========================= */
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

/* =========================
   ENCABEZADOS
========================= */
$sheet->setCellValue('A1', 'N° Inventario');
$sheet->setCellValue('B1', 'Herramienta');
$sheet->setCellValue('C1', 'N° Serie');
$sheet->setCellValue('D1', 'Marca');
$sheet->setCellValue('E1', 'Modelo');
$sheet->setCellValue('F1', 'Fecha Certificación');
$sheet->setCellValue('G1', 'Fecha Vencimiento');
$sheet->setCellValue('H1', 'Estado');
$sheet->setCellValue('I1', 'N° Certificado');

/* =========================
   INSERTAR DATOS
========================= */
$fila = 2;

foreach ($herramientas as $item) {
    $sheet->setCellValue('A' . $fila, $item["numero_inventario"]);
    $sheet->setCellValue('B' . $fila, $item["nombre_herramienta"]);
    $sheet->setCellValue('C' . $fila, $item["numero_serie"]);
    $sheet->setCellValue('D' . $fila, $item["marca"]);
    $sheet->setCellValue('E' . $fila, $item["modelo"]);
    $sheet->setCellValue('F' . $fila, $item["fecha_certificacion"]);
    $sheet->setCellValue('G' . $fila, $item["fecha_vencimiento"]);
    $sheet->setCellValue('H' . $fila, $item["estado_certificacion"]);
    $sheet->setCellValue('I' . $fila, $item["numero_certificado"]);
    $fila++;
}

/* =========================
   AUTO AJUSTE COLUMNAS
========================= */
foreach (range('A', 'I') as $columna) {
    $sheet->getColumnDimension($columna)->setAutoSize(true);
}

/* =========================
   NOMBRE ARCHIVO
========================= */
$archivo = "reporte_herramientas.xlsx";

/* =========================
   DESCARGAR EXCEL
========================= */
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $archivo . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>