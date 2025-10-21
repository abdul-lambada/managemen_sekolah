<?php
$appSettings = app_settings();
$appName = $appSettings['app_name'] ?: APP_NAME;
$logoUrl = sanitize($logoUrl ?? '');
$targetUrl = sanitize($targetUrl ?? '');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>QR Pengaduan | <?= sanitize($appName) ?></title>
    <link href="<?= asset('vendor/fontawesome-free/css/all.min.css') ?>" rel="stylesheet" type="text/css">
    <link href="<?= asset('css/sb-admin-2.min.css') ?>" rel="stylesheet">
    <style>
        .qr-container { text-align:center; }
        #qrcode { position: relative; display: inline-block; }
        #logo { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); border-radius: 8px; width: 64px; height: 64px; object-fit: contain; background: #fff; padding: 6px; }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">QR Pengaduan</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Pindai QR ini untuk membuka formulir pengaduan:</p>
                    <div class="qr-container mb-3">
                        <div id="qrcode"></div>
                        <img id="logo" src="<?= $logoUrl ?>" alt="Logo" onerror="this.style.display='none'">
                    </div>
                    <div class="small text-monospace">URL: <?= $targetUrl ?></div>
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a class="btn btn-light" href="<?= route('pengaduan_form') ?>" target="_blank"><i class="fas fa-external-link-alt"></i> Buka Form</a>
                    <button class="btn btn-success" id="btnDownload"><i class="fas fa-download"></i> Unduh PNG</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    const url = "<?= $targetUrl ?>";
    const qrSize = 360;
    const qr = new QRCode(document.getElementById("qrcode"), {
        text: url,
        width: qrSize,
        height: qrSize,
        colorDark : "#000000",
        colorLight : "#ffffff",
        correctLevel : QRCode.CorrectLevel.H
    });

    // Download PNG including logo overlay
    document.getElementById('btnDownload').addEventListener('click', () => {
        const canvas = document.querySelector('#qrcode canvas');
        if (!canvas) return;

        const out = document.createElement('canvas');
        out.width = canvas.width; out.height = canvas.height;
        const ctx = out.getContext('2d');
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0,0,out.width,out.height);
        ctx.drawImage(canvas, 0, 0);

        const logo = document.getElementById('logo');
        if (logo && logo.complete && logo.naturalWidth) {
            const w = out.width * 0.2; // 20% of QR size
            const h = w;
            const x = (out.width - w)/2;
            const y = (out.height - h)/2;
            // draw white rounded rect behind logo
            const r = 10;
            ctx.fillStyle = '#ffffff';
            ctx.beginPath();
            ctx.moveTo(x+r, y);
            ctx.arcTo(x+w, y, x+w, y+h, r);
            ctx.arcTo(x+w, y+h, x, y+h, r);
            ctx.arcTo(x, y+h, x, y, r);
            ctx.arcTo(x, y, x+w, y, r);
            ctx.closePath();
            ctx.fill();
            // draw logo
            ctx.drawImage(logo, x, y, w, h);
        }

        const link = document.createElement('a');
        link.download = 'qr-pengaduan.png';
        link.href = out.toDataURL('image/png');
        link.click();
    });
</script>
</body>
</html>
