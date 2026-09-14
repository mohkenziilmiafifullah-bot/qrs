<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class GoogleAuthenticatedSessionController extends Controller
{
    public function redirect(Request $request): RedirectResponse
    {
        if (! config('services.google.client_id') || ! config('services.google.client_secret')) {
            return redirect()->route('login')->withErrors([
                'google' => 'Login Google belum dikonfigurasi. Isi GOOGLE_CLIENT_ID dan GOOGLE_CLIENT_SECRET terlebih dahulu.',
            ]);
        }

        $state = Str::random(40);
        $request->session()->put('google_oauth_state', $state);

        $query = http_build_query([
            'client_id' => config('services.google.client_id'),
            'redirect_uri' => route('google.callback'),
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'access_type' => 'online',
            'prompt' => 'select_account',
        ]);

        return redirect()->away("https://accounts.google.com/o/oauth2/v2/auth?{$query}");
    }

    public function callback(Request $request): RedirectResponse
    {
        if (! hash_equals((string) $request->session()->pull('google_oauth_state'), (string) $request->string('state'))) {
            return redirect()->route('login')->withErrors(['google' => 'Sesi login Google tidak valid. Silakan coba lagi.']);
        }

        if ($request->filled('error')) {
            return redirect()->route('login')->withErrors(['google' => 'Login Google dibatalkan.']);
        }

        try {
            $token = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'code' => $request->string('code'),
                'client_id' => config('services.google.client_id'),
                'client_secret' => config('services.google.client_secret'),
                'redirect_uri' => route('google.callback'),
                'grant_type' => 'authorization_code',
            ])->throw()->json('access_token');

            $googleUser = Http::withToken($token)
                ->get('https://openidconnect.googleapis.com/v1/userinfo')
                ->throw()
                ->json();
        } catch (\Throwable) {
            return redirect()->route('login')->withErrors(['google' => 'Google tidak dapat memverifikasi akun Anda. Silakan coba lagi.']);
        }

        if (empty($googleUser['email']) || empty($googleUser['email_verified'])) {
            return redirect()->route('login')->withErrors(['google' => 'Gunakan akun Google dengan email yang sudah terverifikasi.']);
        }

        $user = User::firstOrCreate(
            ['email' => $googleUser['email']],
            [
                'name' => $googleUser['name'] ?? $googleUser['email'],
                'password' => Hash::make(Str::random(40)),
                'role' => 'sales',
                'status' => 'active',
                'email_verified_at' => now(),
            ],
        );

        if ($user->status !== 'active') {
            return redirect()->route('login')->withErrors(['google' => 'Akun Anda belum aktif atau diblokir. Hubungi admin.']);
        }

        $user->forceFill(['google_id' => $googleUser['sub']])->save();
        Auth::login($user, true);
        $request->session()->regenerate();

        return $user->isAdmin() ? redirect()->intended('/admin') : redirect()->intended('/sales');
    }
}
