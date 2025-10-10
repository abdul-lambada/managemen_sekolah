<?php

declare(strict_types=1);

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

function export_array_to_excel(string $filename, array $headers, array $rows): void
{
    $vendorAutoload = BASE_PATH . '/vendor/autoload.php';

    if (!class_exists(Spreadsheet::class)) {
        if (file_exists($vendorAutoload)) {
            require_once $vendorAutoload;
        }
    }

    if (!class_exists(Spreadsheet::class)) {
        header('Content-Type: text/plain; charset=UTF-8');
        echo "Excel export membutuhkan PhpSpreadsheet. Instal terlebih dahulu dengan perintah:\ncomposer require phpoffice/phpspreadsheet";
        exit;
    }

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    $columnIndex = 1;
    foreach ($headers as $header) {
        $sheet->setCellValueByColumnAndRow($columnIndex, 1, $header);
        $columnIndex++;
    }

    $rowNumber = 2;
    foreach ($rows as $row) {
        $columnIndex = 1;
        foreach ($row as $cell) {
            $sheet->setCellValueByColumnAndRow($columnIndex, $rowNumber, $cell);
            $columnIndex++;
        }
        $rowNumber++;
    }

    foreach (range(1, count($headers)) as $col) {
        $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
    }

    $safeFilename = preg_replace('/[^A-Za-z0-9_\-]/', '_', $filename);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $safeFilename . '.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}
