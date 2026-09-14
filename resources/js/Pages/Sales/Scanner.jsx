import SalesLayout from '@/Layouts/SalesLayout';
import { router } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import { Html5Qrcode } from 'html5-qrcode';

export default function Scanner() {
    const scannerRef = useRef(null); const [error, setError] = useState(null); const [manualCode, setManualCode] = useState('');
    useEffect(() => { const scanner = new Html5Qrcode('qr-reader'); scannerRef.current = scanner; scanner.start({ facingMode: 'environment' }, { fps: 10, qrbox: 240 }, (text) => { const code = text.trim().split('/').pop().toUpperCase(); scanner.stop().catch(() => {}); router.visit(`/sales/activate/${code}`); }, () => {}).catch(() => setError('Kamera tidak dapat diakses. Masukkan kode QR secara manual.')); return () => scannerRef.current?.stop().catch(() => {}); }, []);
    const submit = (event) => { event.preventDefault(); if (manualCode.trim()) router.visit(`/sales/activate/${manualCode.trim().toUpperCase()}`); };
    return <SalesLayout title="Scan QR"><section className="scan-intro"><p className="section-kicker">AKTIVASI TOKO</p><h2>Arahkan kamera ke QR</h2><p>Scan QR yang akan dipasang di toko, kemudian lengkapi informasi bisnisnya.</p></section><section className="sales-card scan-card"><div id="qr-reader" />{error && <p className="form-error">{error}</p>}</section><section className="sales-card manual-card"><p className="section-kicker">ALTERNATIF</p><h3>Masukkan kode secara manual</h3><form onSubmit={submit}><input className="sales-input uppercase" placeholder="CONTOH: AB3K9" value={manualCode} onChange={(e) => setManualCode(e.target.value)} maxLength={5}/><button className="sales-button" type="submit">Lanjutkan</button></form></section></SalesLayout>;
}
