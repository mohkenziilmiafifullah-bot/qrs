export default function QrInactive() {
    return (
        <div className="min-h-screen flex flex-col items-center justify-center bg-paper px-6 text-center">
            <h1 className="text-xl font-bold text-ink mb-2">QR Belum Aktif</h1>
            <p className="text-ink/60 max-w-xs">
                Kode QR ini belum diaktivasi atau sedang tidak tersedia. Hubungi pemilik toko atau tim Sales kami.
            </p>
        </div>
    );
}
