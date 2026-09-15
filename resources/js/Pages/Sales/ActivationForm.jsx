import { useMemo, useState } from 'react';
import SalesLayout from '@/Layouts/SalesLayout';
import Modal from '@/Components/Modal';
import { useForm } from '@inertiajs/react';

const rupiah = (value) => `Rp ${Number(value).toLocaleString('id-ID')}`;

const PLACE_ID_FINDER_URL = 'https://developers.google.com/maps/documentation/javascript/examples/places-placeid-finder';

const buildReviewLink = (placeId) => `https://search.google.com/local/writereview?placeid=${placeId.trim()}`;

export default function ActivationForm({ qr, fee, walletBalance }) {
    const { data, setData, post, processing, errors } = useForm({
        code: qr.code,
        merchant_name: '',
        google_place_id: '',
        target_url: '',
    });

    const [useManualLink, setUseManualLink] = useState(false);
    const [showFinder, setShowFinder] = useState(false);

    const insufficient = walletBalance < fee;

    const reviewLinkPreview = useMemo(() => {
        return data.google_place_id.trim() ? buildReviewLink(data.google_place_id) : '';
    }, [data.google_place_id]);

    const switchToManualLink = () => {
        setUseManualLink(true);
        setData('google_place_id', '');
    };

    const switchToPlaceId = () => {
        setUseManualLink(false);
        setData('target_url', '');
    };

    return (
        <SalesLayout title="Aktivasi Toko">
            <section className="activation-code">
                <p className="section-kicker">QR TERPILIH</p>
                <b>{qr.code}</b>
                <span>Pastikan kode ini sesuai dengan QR fisik.</span>
            </section>

            <form
                className="sales-form"
                onSubmit={(e) => {
                    e.preventDefault();
                    post('/sales/activate');
                }}
            >
                <label>
                    Nama toko
                    <input
                        className="sales-input"
                        value={data.merchant_name}
                        onChange={(e) => setData('merchant_name', e.target.value)}
                        placeholder="Contoh: Warung Bu Siti"
                        required
                    />
                </label>
                {errors.merchant_name && <p className="form-error">{errors.merchant_name}</p>}

                {!useManualLink && (
                    <>
                        <label>
                            Google Place ID
                            <input
                                className="sales-input"
                                value={data.google_place_id}
                                onChange={(e) => setData('google_place_id', e.target.value)}
                                placeholder="Contoh: ChIJN1t_tDeuEmsRUsoyG83frY4"
                                required={!useManualLink}
                            />
                        </label>
                        <p className="field-hint">
                            QR akan langsung membuka pop-up beri bintang &amp; ulasan toko ini, bukan halaman Maps.
                        </p>

                        <button type="button" className="finder-trigger" onClick={() => setShowFinder(true)}>
                            🔍 Cari Place ID toko ini
                        </button>

                        {reviewLinkPreview && (
                            <div className="review-link-preview">
                                <span>Link review yang akan dipakai:</span>
                                <b>{reviewLinkPreview}</b>
                            </div>
                        )}

                        {errors.google_place_id && <p className="form-error">{errors.google_place_id}</p>}

                        <button type="button" className="link-toggle" onClick={switchToManualLink}>
                            Tidak ketemu Place ID? Pakai link Google Maps manual
                        </button>
                    </>
                )}

                {useManualLink && (
                    <>
                        <label>
                            Link Google Maps
                            <input
                                type="url"
                                className="sales-input"
                                value={data.target_url}
                                onChange={(e) => setData('target_url', e.target.value)}
                                placeholder="https://maps.app.goo.gl/..."
                                required={useManualLink}
                            />
                        </label>
                        <p className="field-hint">
                            Catatan: dengan link Maps biasa, QR akan mengarah ke halaman toko di Maps, bukan langsung ke form ulasan.
                        </p>
                        {errors.target_url && <p className="form-error">{errors.target_url}</p>}

                        <button type="button" className="link-toggle" onClick={switchToPlaceId}>
                            ← Kembali pakai Google Place ID (disarankan)
                        </button>
                    </>
                )}

                <div className="fee-card">
                    <span>Biaya aktivasi</span>
                    <b>{rupiah(fee)}</b>
                    <small>Saldo Anda: {rupiah(walletBalance)}</small>
                </div>

                {insufficient ? (
                    <p className="form-error">Saldo tidak mencukupi. Silakan top up terlebih dahulu.</p>
                ) : (
                    <button disabled={processing} className="sales-button" type="submit">
                        Aktifkan QR
                    </button>
                )}
            </form>

            <Modal show={showFinder} maxWidth="xl" onClose={() => setShowFinder(false)}>
                <div className="finder-modal-body">
                    <p className="section-kicker">GOOGLE PLACE ID FINDER</p>
                    <h2>Cari Place ID Toko</h2>
                    <ol className="finder-steps">
                        <li>Ketik nama toko di kotak pencarian pada widget di bawah.</li>
                        <li>Klik toko yang sesuai pada hasil/peta yang muncul.</li>
                        <li>Salin (copy) <b>Place ID</b> yang ditampilkan.</li>
                        <li>Kembali ke form ini dan tempel Place ID tersebut.</li>
                    </ol>

                    <a
                        href={PLACE_ID_FINDER_URL}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="finder-open-cta"
                    >
                        🗺️ Buka Google Place ID Finder
                    </a>
                    <p className="finder-fallback">
                        Google tidak mengizinkan halaman ini ditampilkan langsung di dalam aplikasi,
                        jadi widget-nya dibuka di tab baru. Setelah Anda salin Place ID di sana, kembali ke tab ini untuk menempelkannya.
                    </p>

                    <button type="button" className="sales-button finder-done" onClick={() => setShowFinder(false)}>
                        Selesai, tempel Place ID
                    </button>
                </div>
            </Modal>
        </SalesLayout>
    );
}
