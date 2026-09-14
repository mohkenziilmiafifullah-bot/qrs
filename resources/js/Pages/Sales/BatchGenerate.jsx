import SalesLayout from '@/Layouts/SalesLayout';
import { useForm } from '@inertiajs/react';
import { useState } from 'react';

function StatusBadge({ status }) {
    const active = status === 'active';
    return <span className={`batch-status ${active ? 'is-active' : ''}`}>{active ? 'Aktif' : 'Belum aktif'}</span>;
}

export default function BatchGenerate({ batches = [], unassignedCount = 0, maxUnassigned = 20, justGeneratedBatch }) {
    const { data, setData, post, processing, errors } = useForm({ quantity: 20, paper_size: 'A6' });
    const [expandedBatch, setExpandedBatch] = useState(null);
    const limitReached = unassignedCount >= maxUnassigned;

    function generate(event) {
        event.preventDefault();
        post('/sales/batch', { preserveScroll: true });
    }

    return <SalesLayout title="Cetak Batch QR">
        <section className="batch-panel">
            <div className="batch-panel-heading"><div><p className="batch-eyebrow">BATCH BARU</p><h2>Buat QR cetak</h2><p className="batch-stock-inline"><strong>{unassignedCount}</strong> dari {maxUnassigned} QR siap dijual</p></div><span className="batch-step">01</span></div>
            <form onSubmit={generate}>
                <label className="batch-label" htmlFor="quantity">Jumlah QR</label>
                <div className="quantity-control"><button type="button" onClick={() => setData('quantity', Math.max(10, Number(data.quantity) - 1))} disabled={limitReached}>−</button><input id="quantity" type="number" min="10" max="50" value={data.quantity} onChange={(e) => setData('quantity', e.target.value)} disabled={limitReached} /><button type="button" onClick={() => setData('quantity', Math.min(50, Number(data.quantity) + 1))} disabled={limitReached}>+</button></div>
                {errors.quantity && <p className="batch-error">{errors.quantity}</p>}
                <label className="batch-label mt-5">Ukuran cetak</label>
                <div className="paper-options">{['A5', 'A6'].map((size) => <button type="button" key={size} disabled={limitReached} onClick={() => setData('paper_size', size)} className={data.paper_size === size ? 'selected' : ''}><b>{size}</b><span>{size === 'A6' ? 'Kartu ringkas' : 'Kartu besar'}</span></button>)}</div>
                <button type="submit" disabled={processing || limitReached} className="batch-submit">{limitReached ? 'Aktivasi QR terlebih dahulu' : processing ? 'Menyiapkan QR…' : 'Buat batch QR'}</button>
            </form>
        </section>

        {justGeneratedBatch && <section className="batch-success">
            <div><p className="batch-eyebrow">BATCH SIAP</p><h2>{justGeneratedBatch.items.length} QR berhasil dibuat</h2><p>{justGeneratedBatch.reference}</p></div>
            <a href={`/sales/batch/${justGeneratedBatch.reference}/download?paper_size=${justGeneratedBatch.paperSize}`} className="batch-download">Unduh PDF</a>
            <div className="batch-preview-grid">{justGeneratedBatch.items.map((item) => <div key={item.code} className="batch-preview"><img src={item.qr_image} alt={item.code} /><b>{item.code}</b><StatusBadge status={item.status} /></div>)}</div>
        </section>}

        {batches.length > 0 && <section className="batch-history">
            <div className="batch-history-title"><div><p className="batch-eyebrow">ARSIP</p><h2>Riwayat batch</h2></div><span>{batches.length} batch</span></div>
            {batches.filter((batch) => batch.reference !== justGeneratedBatch?.reference).map((batch) => {
                const activeCount = batch.items.filter((item) => item.status === 'active').length;
                const isExpanded = expandedBatch === batch.reference;
                return <article className="batch-item" key={batch.reference}>
                    <div className="batch-item-head"><div><h3>{batch.reference}</h3><p>{batch.created_at} · {activeCount}/{batch.items.length} aktif</p><small>{batch.items.length} QR dalam batch ini</small></div><div className="batch-item-actions"><button type="button" onClick={() => setExpandedBatch(isExpanded ? null : batch.reference)}>{isExpanded ? 'Sembunyikan QR' : 'Lihat QR'}</button><a href={`/sales/batch/${batch.reference}/download`}>Unduh PDF</a></div></div>
                    {isExpanded && <div className="batch-code-list">{batch.items.map((item) => <div key={item.code}><b>{item.code}</b><StatusBadge status={item.status} /></div>)}</div>}
                </article>;
            })}
        </section>}
    </SalesLayout>;
}
