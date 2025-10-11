<?php

declare(strict_types=1);

function indo_date(?string $date): string
{
    if (!$date) {
        return '-';
    }

    $timestamp = strtotime($date);
    if ($timestamp === false) {
        return $date;
    }

    $months = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    $day = (int) date('d', $timestamp);
    $month = (int) date('m', $timestamp);
    $year = date('Y', $timestamp);

    return sprintf('%02d %s %s', $day, $months[$month] ?? $month, $year);
}

function indo_datetime(?string $datetime): string
{
    if (!$datetime) {
        return '-';
    }

    $timestamp = strtotime($datetime);
    if ($timestamp === false) {
        return $datetime;
    }

    return indo_date(date('Y-m-d', $timestamp)) . ' ' . date('H:i', $timestamp);
}

function attendance_badge(?string $status): string
{
    $map = [
        'Hadir' => 'success',
        'Telat' => 'warning',
        'Terlambat' => 'warning',
        'Izin' => 'info',
        'Sakit' => 'primary',
        'Alfa' => 'danger',
        'Tidak Hadir' => 'danger',
    ];

    $badge = $map[$status] ?? 'secondary';
    return sprintf('<span class="badge badge-%s">%s</span>', $badge, sanitize($status ?? '-'));
}
