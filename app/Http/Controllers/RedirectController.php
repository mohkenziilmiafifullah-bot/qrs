<?php

namespace App\Http\Controllers;

use App\Jobs\LogQrScan;
use App\Models\Qr;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class RedirectController extends Controller
{
    /**
     * GET /q/{code}
     *
     * Ultra-fast public redirect: cache-first lookup, 302 to the merchant's
     * Google Maps link, analytics logged asynchronously via queue so it never
     * adds latency to the redirect itself.
     */
    public function __invoke(Request $request, string $code): RedirectResponse
    {
        $qr = Qr::cachedLookup($code);

        if (! $qr || $qr['status'] !== 'active' || ! $qr['target_url']) {
            // Unassigned, suspended, or unknown code — send to a friendly landing page
            // instead of a raw 404, since this is a physical sticker someone just scanned.
            return redirect()->route('qr.inactive');
        }

        LogQrScan::dispatch(
            $qr['id'],
            $request->ip(),
            $request->userAgent(),
            now()->toDateTimeString(),
        );

        return redirect()->away($qr['target_url']);
    }
}
