<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 5mm; }
        body { margin: 0; font-family: DejaVu Sans, sans-serif; }
        .print-page { width: 93mm; height: 136mm; position: relative; page-break-after: always; }
        .print-page:last-child { page-break-after: auto; }
        .card { position: absolute; inset: 0; border: .6mm solid #dfe3e8; border-radius: 7mm; overflow: hidden; background: #fff; }
        .corner-blue { position: absolute; z-index: 2; left: 0; top: 0; width: 31mm; height: 20mm; background: #4285f4; border-radius: 0 0 28mm 0; }
        .corner-yellow { position: absolute; z-index: 1; left: 23mm; top: 0; width: 13mm; height: 17mm; background: #fbbc04; border-radius: 0 0 0 13mm; }
        .corner-red { position: absolute; z-index: 2; right: 0; bottom: 0; width: 25mm; height: 17mm; background: #ea4335; border-radius: 23mm 0 0 0; }
        .corner-green { position: absolute; z-index: 1; right: 17mm; bottom: 0; width: 14mm; height: 13mm; background: #34a853; border-radius: 0 0 15mm 0; }
        .pin { position: absolute; z-index: 2; top: 12mm; left: 41.5mm; width: 10mm; height: 12mm; border-radius: 55% 55% 55% 0; transform: rotate(-45deg); background: linear-gradient(135deg, #4285f4 0 32%, #ea4335 32% 47%, #fbbc04 47% 64%, #34a853 64%); }
        .pin-hole { position: absolute; left: 3mm; top: 3mm; width: 4mm; height: 4mm; border-radius: 50%; background: #fff; }
        .google { position: absolute; z-index: 2; top: 26mm; left: 0; width: 100%; margin: 0; text-align: center; font-size: 14pt; font-weight: bold; letter-spacing: -.7mm; }.b { color: #4285f4; }.r { color: #ea4335; }.y { color: #fbbc04; }.g { color: #34a853; }.maps { color: #444c59; font-weight: normal; }
        .caption { position: absolute; z-index: 2; top: 34mm; left: 0; width: 100%; margin: 0; text-align: center; color: #606977; font-size: 8pt; }
        .qr-frame { position: absolute; z-index: 2; top: 42mm; left: 19mm; width: 54mm; height: 54mm; padding: 2mm; border: 1.4mm solid #4285f4; border-radius: 6mm; background: #fff; }
        .qr-frame img { display: block; width: 54mm; height: 54mm; }
        .stripe { position: absolute; z-index: 3; left: -1.4mm; width: 1.4mm; height: 8mm; }.stripe-red { top: 16mm; background: #ea4335; }.stripe-yellow { bottom: 8mm; background: #fbbc04; }.stripe-green { left: auto; right: -1.4mm; bottom: 8mm; background: #34a853; }
        .scan { position: absolute; z-index: 2; top: 108mm; left: 20mm; width: 53mm; padding: 2mm 0; text-align: center; border-radius: 8mm; background: #f1f3f5; color: #303744; font-size: 7.5pt; white-space: nowrap; }.scan-dot { color: #34a853; font-size: 11pt; }.divider { padding: 0 2mm; color: #858c97; }
        .thanks { position: absolute; z-index: 2; top: 122mm; left: 0; width: 100%; margin: 0; text-align: center; font-size: 6.5pt; letter-spacing: 1.8mm; font-weight: bold; color: #657080; }
    </style>
</head>
<body>
    @foreach ($items as $item)
        <div class="print-page"><div class="card">
            <div class="corner-blue"></div><div class="corner-yellow"></div><div class="corner-red"></div><div class="corner-green"></div>
            <div class="pin"><div class="pin-hole"></div></div>
            <p class="google"><span class="b">G</span><span class="r">o</span><span class="y">o</span><span class="b">g</span><span class="g">l</span><span class="r">e</span> <span class="maps">Maps</span></p><p class="caption">Untuk melihat lokasi kami</p>
            <div class="qr-frame"><span class="stripe stripe-red"></span><span class="stripe stripe-yellow"></span><span class="stripe stripe-green"></span><img src="{{ $item['qr_image'] }}" alt="QR {{ $item['code'] }}"></div>
            <div class="scan"><span class="scan-dot">●</span><span class="divider">│</span> Scan di Google Maps</div><p class="thanks">— TERIMA KASIH —</p>
        </div></div>
    @endforeach
</body>
</html>
