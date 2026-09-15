<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Batch QR Code</title>
    <style>
        /* Konfigurasi Ukuran Kertas Dinamis */
        @page {
            margin: 0;
            size: {{ strtolower($paperSize ?? 'a6') }} portrait;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            width: 100%;
            height: 100%;
            font-family: sans-serif;
            background-color: #ffffff;
        }

        .page-break {
            page-break-after: always;
        }

        /* Container utama satu halaman card */
        .card-container {
            position: relative;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        /* Background template utuh */
        .bg-template {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
        }

        /* Container QR Code tepat di dalam kotak putih template */
        .qr-wrapper {
            position: absolute;
            top: 37.8%;
            left: 23.8%;
            width: 52.4%;
            height: 35.2%;
            z-index: 2;
            display: table;
            text-align: center;
        }

        .qr-inner {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
        }

        .qr-inner img {
            max-width: 90%;
            max-height: 90%;
            width: auto;
            height: auto;
            margin: 0 auto;
            display: block;
        }
    </style>
</head>
<body>
    @foreach($qrs as $qr)
        <div class="card-container {{ !$loop->last ? 'page-break' : '' }}">
            <!-- Background Template -->
            <img src="{{ public_path('images/qr-template.png') }}" class="bg-template" alt="Template Background">

            <!-- QR Code Data URI dari Endroid -->
            <div class="qr-wrapper">
                <div class="qr-inner">
                    <img src="{{ $qr['qr_image'] }}" alt="QR Code {{ $qr['code'] }}">
                </div>
            </div>
        </div>
    @endforeach
</body>
</html>