<?php

declare(strict_types=1);

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

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

function read_spreadsheet_to_array(string $filePath): array
{
    $vendorAutoload = BASE_PATH . '/vendor/autoload.php';

    if (!class_exists(Spreadsheet::class)) {
        if (file_exists($vendorAutoload)) {
            require_once $vendorAutoload;
        }
    }

    if (class_exists(IOFactory::class)) {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = [];
        $headers = [];

        foreach ($sheet->getRowIterator() as $rowIndex => $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            $rowData = [];
            foreach ($cellIterator as $cell) {
                $rowData[] = trim((string) $cell->getValue());
            }

            if ($rowIndex === 1) {
                $headers = normalize_import_headers($rowData);
                continue;
            }

            if (row_is_empty($rowData)) {
                continue;
            }

            $rows[] = combine_row_with_headers($headers, $rowData);
        }

        return $rows;
    }

    $handle = fopen($filePath, 'r');
    if ($handle === false) {
        throw new RuntimeException('Tidak dapat membaca file upload.');
    }

    $rows = [];
    $headers = [];
    $rowIndex = 0;
    while (($data = fgetcsv($handle, 0, ',')) !== false) {
        $rowIndex++;
        $data = array_map(static fn ($value) => trim((string) $value), $data);
        if ($rowIndex === 1) {
            $headers = normalize_import_headers($data);
            continue;
        }

        if (row_is_empty($data)) {
            continue;
        }

        $rows[] = combine_row_with_headers($headers, $data);
    }

    fclose($handle);

    return $rows;
}

function normalize_import_headers(array $headers): array
{
    $normalized = [];
    foreach ($headers as $header) {
        $key = strtolower(trim((string) $header));
        $key = preg_replace('/[^a-z0-9]+/', '_', $key);
        $key = trim($key, '_');
        if ($key === '') {
            $key = 'kolom_' . (count($normalized) + 1);
        }
        $normalized[] = $key;
    }

    return $normalized;
}

function row_is_empty(array $row): bool
{
    foreach ($row as $value) {
        if ($value !== '' && $value !== null) {
            return false;
        }
    }

    return true;
}

function combine_row_with_headers(array $headers, array $row): array
{
    $combined = [];
    foreach ($headers as $index => $header) {
        $combined[$header] = $row[$index] ?? null;
    }

    return $combined;
}
