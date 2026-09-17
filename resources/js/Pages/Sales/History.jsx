import { useState } from 'react';
import SalesLayout from '@/Layouts/SalesLayout';
import { Link, router } from '@inertiajs/react';

const FILTERS = [
    { value: 'all', label: 'Semua' },
    { value: 'ready', label: 'Siap Dikirim' },
    { value: 'sent', label: 'Sudah Dikirim' },
    { value: 'waiting', label: 'Menunggu' },
];

function sendReport(qr) {
    if (!qr.wa_report_link) return;
    window.open(qr.wa_report_link, '_blank', 'noopener,noreferrer');
    router.post(`/sales/qr/${qr.id}/mark-report-sent`, {}, { preserveScroll: true, preserveState: true });
}

function NfcCopyButton({ link }) {
    const [copied, setCopied] = useState(false);

    if (!link) return null;

    async function copyLink() {
        try {
            await navigator.clipboard.writeText(link);
        } catch (e) {
            // Fallback untuk browser lama / konteks non-https lokal.
            const textarea = document.createElement('textarea');
            textarea.value = link;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
        }
        setCopied(true);
        setTimeout(() => setCopied(false), 2000);
    }

    return (
        <button type="button" className="nfc-copy-btn" onClick={copyLink}>
            {copied ? '✓ Tersalin!' : 'Salin Link NFC'}
        </button>
    );
}

function ReportButton({ qr }) {
    if (qr.already_sent) {
        return <p className="report-note">Laporan sudah dikirim (sekali seumur hidup), tidak bisa dikirim lagi.</p>;
    }
    if (qr.can_send_report) {
        if (!qr.phone_number) {
            return <p className="report-note">Nomor WhatsApp toko belum tersedia.</p>;
        }
        return <button type="button" className="report-btn is-ready" onClick={() => sendReport(qr)}>Kirim Laporan WA</button>;
    }
    return (
        <>
            <button type="button" className="report-btn is-locked" disabled>Kirim Laporan WA</button>
            <p className="report-note">Baru bisa dikirim setelah genap 1 bulan aktivasi ({qr.eligible_date}).</p>
        </>
    );
}

export default function History({ qrs, reportFilter }) {
    return (
        <SalesLayout title="Riwayat Aktivasi">
            <section className="history-intro">
                <p className="section-kicker">TOKO TERHUBUNG</p>
                <h2>Riwayat aktivasi QR</h2>
                <p>Daftar toko yang QR review-nya telah aktif.</p>
            </section>

            <div className="report-filter-tabs">
                {FILTERS.map((f) => (
                    <Link
                        key={f.value}
                        href={`/sales/history?report_filter=${f.value}`}
                        className={`report-filter-tab${reportFilter === f.value ? ' is-active' : ''}`}
                        preserveScroll
                    >
                        {f.label}
                    </Link>
                ))}
            </div>

            <section className="sales-list-section">
                {qrs.data.length === 0 ? (
                    <div className="sales-empty">Tidak ada toko pada filter ini.</div>
                ) : (
                    qrs.data.map((qr) => (
                        <article className="sales-row report-row" key={qr.code}>
                            <span className="row-mark">✓</span>
                            <div>
                                <b>{qr.merchant_name}</b>
                                <small>{qr.code} · {new Date(qr.activated_at).toLocaleString('id-ID')}</small>
                            </div>
                            <span className="active-pill">Aktif</span>
                            <div className="report-actions">
                                <ReportButton qr={qr} />
                                <NfcCopyButton link={qr.nfc_link} />
                            </div>
                        </article>
                    ))
                )}
            </section>
        </SalesLayout>
    );
}
