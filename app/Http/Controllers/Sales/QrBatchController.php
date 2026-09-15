<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Qr;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class QrBatchController extends Controller
{
    /**
     * Max QR codes a sales rep may have printed-but-not-yet-activated at once.
     * Prevents printing endless batches without ever activating them in the field.
     */
    protected const MAX_UNASSIGNED = 20;

    public function create(Request $request): Response
    {
        $user = $request->user();

        $unassignedCount = Qr::where('sales_id', $user->id)
            ->where('status', 'unassigned')
            ->count();

        $batches = Qr::where('sales_id', $user->id)
            ->whereNotNull('batch_reference')
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('batch_reference')
            ->map(function ($items, $reference) {
                return [
                    'reference' => $reference,
                    'created_at' => $items->first()->created_at?->format('d M Y H:i'),
                    'items' => $items->map(fn (Qr $qr) => [
                        'code' => $qr->code,
                        'status' => $qr->status,
                    ])->values(),
                ];
            })
            ->values();

        $justGeneratedRef = $request->session()->get('justGenerated');
        $justGeneratedBatch = null;

        if ($justGeneratedRef) {
            $qrs = Qr::where('batch_reference', $justGeneratedRef)->get();

            if ($qrs->isNotEmpty()) {
                $justGeneratedBatch = [
                    'reference' => $justGeneratedRef,
                    'paperSize' => $request->session()->get('paperSize', 'A6'),
                    'items' => $qrs->map(fn (Qr $qr) => [
                        'code' => $qr->code,
                        'status' => $qr->status,
                        'qr_image' => $this->buildQrImage($qr->code, 150),
                    ])->values(),
                ];
            }
        }

        return Inertia::render('Sales/BatchGenerate', [
            'batches' => $batches,
            'unassignedCount' => $unassignedCount,
            'maxUnassigned' => self::MAX_UNASSIGNED,
            'justGeneratedBatch' => $justGeneratedBatch,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:10', 'max:50'],
            'paper_size' => ['required', 'in:A5,A6'],
        ]);

        $user = $request->user();

        $unassignedCount = Qr::where('sales_id', $user->id)
            ->where('status', 'unassigned')
            ->count();

        if ($unassignedCount >= self::MAX_UNASSIGNED) {
            return back()->withErrors([
                'quantity' => "Kamu masih punya {$unassignedCount} QR yang belum aktif (limit ".self::MAX_UNASSIGNED."). "
                    ."Aktivasi dulu QR yang sudah dicetak sampai jumlahnya turun, baru bisa cetak batch baru.",
            ]);
        }

        $batchReference = 'BATCH-' . now()->format('Ymd-His') . '-' . $user->id;

        collect(range(1, $data['quantity']))->each(function () use ($batchReference, $user) {
            Qr::create([
                'code' => $this->generateUniqueCode(),
                'batch_reference' => $batchReference,
                'sales_id' => $user->id,
                'status' => 'unassigned',
            ]);
        });

        return redirect()
            ->route('sales.batch.create')
            ->with('justGenerated', $batchReference)
            ->with('paperSize', $data['paper_size']);
    }

    public function download(Request $request, string $batchReference)
    {
        $qrs = Qr::where('batch_reference', $batchReference)
            ->where('sales_id', $request->user()->id)
            ->get();

        abort_if($qrs->isEmpty(), 404, 'Batch tidak ditemukan.');

        $paperSize = $request->query('paper_size', 'A6');

        $items = $qrs->map(fn (Qr $qr) => [
            'code' => $qr->code,
            'qr_image' => $this->buildQrImage($qr->code, 300),
        ]);

        $pdf = Pdf::loadView('pdf.qr-batch', [
            'qrs' => $items,
            'paperSize' => $paperSize,
        ])->setPaper(strtolower($paperSize), 'portrait');

        return $pdf->download("qr-batch-{$batchReference}.pdf");
    }
    private function buildQrImage(string $code, int $size): string
    {
        $builder = new Builder(
            writer: new PngWriter(),
            data: route('qr.redirect', $code),
            encoding: new Encoding('UTF-8'),
            size: $size,
            margin: 10,
        );

        return $builder->build()->getDataUri();
    }

    private function generateUniqueCode(): string
    {
        // Unambiguous charset for a code that will be read off a small printed sticker.
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        do {
            $code = collect(range(1, 5))
                ->map(fn () => $chars[random_int(0, strlen($chars) - 1)])
                ->implode('');
        } while (Qr::where('code', $code)->exists());

        return $code;
    }
}
