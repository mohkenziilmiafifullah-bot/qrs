import SalesLayout from '@/Layouts/SalesLayout';
import { Link } from '@inertiajs/react';

const rupiah = (value) => `Rp ${Number(value).toLocaleString('id-ID')}`;

function ScanIcon() {
    return <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true"><path d="M4 8V5a1 1 0 0 1 1-1h3M16 4h3a1 1 0 0 1 1 1v3M20 16v3a1 1 0 0 1-1 1h-3M8 20H5a1 1 0 0 1-1-1v-3"/><path d="M7 12h10M9 9v6M12 9v6M15 9v6"/></svg>;
}

function BatchIcon() {
    return <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true"><rect x="4" y="4" width="16" height="16" rx="2"/><path d="M8 8h.01M12 8h.01M16 8h.01M8 12h.01M12 12h.01M16 12h.01M8 16h.01M12 16h.01M16 16h.01"/></svg>;
}

export default function Dashboard({ walletBalance, activatedCount, unassignedCount, inactiveCount, latestQrStatus, recentActivations }) {
    const active = latestQrStatus?.status === 'active';
    return <SalesLayout title="Beranda">
        <section className="sales-overview"><div className="overview-balance"><span>Saldo tersedia</span><b>{rupiah(walletBalance)}</b><Link href="/sales/deposit">Top up</Link></div></section>
        <div className="sales-metrics"><div><b>{activatedCount}</b><span>Aktif</span></div><div><b>{unassignedCount}</b><span>Siap dijual</span></div><div><b>{inactiveCount}</b><span>Nonaktif</span></div></div>
        <div className="sales-actions"><Link href="/sales/scan"><i><ScanIcon /></i><span>Scan & aktivasi</span></Link><Link href="/sales/batch"><i><BatchIcon /></i><span>Cetak batch</span></Link></div>
        <section className="sales-list-section"><div className="section-head"><div><p className="section-kicker">AKTIVITAS</p><h2>Aktivasi terbaru</h2></div><Link href="/sales/history">Lihat semua</Link></div>{recentActivations.length === 0 ? <div className="sales-empty">Belum ada toko yang diaktivasi.</div> : recentActivations.map((qr) => <article className="sales-row" key={qr.code}><span className="row-mark">✓</span><div><b>{qr.merchant_name}</b><small>{qr.code}</small></div><time>{new Date(qr.activated_at).toLocaleDateString('id-ID')}</time></article>)}</section>
    </SalesLayout>;
}
