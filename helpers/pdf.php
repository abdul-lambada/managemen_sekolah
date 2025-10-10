<?php

declare(strict_types=1);

use Dompdf\Dompdf;

function export_array_to_pdf(string $filename, string $title, array $headers, array $rows, string $orientation = 'portrait'): void
{
    $vendorAutoload = BASE_PATH . '/vendor/autoload.php';

    if (!class_exists(Dompdf::class)) {
        if (file_exists($vendorAutoload)) {
            require_once $vendorAutoload;
        }
    }

    if (!class_exists(Dompdf::class)) {
        header('Content-Type: text/plain; charset=UTF-8');
        echo "PDF export membutuhkan Dompdf. Instal terlebih dahulu dengan perintah:\ncomposer require dompdf/dompdf";
        exit;
    }

    $safeFilename = preg_replace('/[^A-Za-z0-9_\-]/', '_', $filename);
    $dompdf = new Dompdf([
        'isRemoteEnabled' => true,
    ]);

    $tableHeaders = '';
    foreach ($headers as $header) {
        $tableHeaders .= '<th style="padding:6px;border:1px solid #ddd;background:#f2f2f2;">' . htmlspecialchars($header, ENT_QUOTES, 'UTF-8') . '</th>';
    }

    $tableRows = '';
    foreach ($rows as $row) {
        $tableRows .= '<tr>';
        foreach ($row as $cell) {
            $tableRows .= '<td style="padding:6px;border:1px solid #ddd;">' . htmlspecialchars((string) $cell, ENT_QUOTES, 'UTF-8') . '</td>';
        }
        $tableRows .= '</tr>';
    }

    $html = '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>' .
        htmlspecialchars($title, ENT_QUOTES, 'UTF-8') .
        '</title><style>body{font-family:DejaVu Sans,Helvetica,Arial,sans-serif;font-size:12px;color:#333;}h1{text-align:center;margin-bottom:20px;}table{width:100%;border-collapse:collapse;}thead{font-weight:bold;}tr:nth-child(even){background:#fafafa;}}</style></head><body>' .
        '<h1>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h1>' .
        '<table><thead><tr>' . $tableHeaders . '</tr></thead><tbody>' . $tableRows . '</tbody></table>' .
        '</body></html>';

    $dompdf->loadHtml($html, 'UTF-8');
    $dompdf->setPaper('A4', $orientation);
    $dompdf->render();
    $dompdf->stream($safeFilename . '.pdf', ['Attachment' => true]);
    exit;
}
