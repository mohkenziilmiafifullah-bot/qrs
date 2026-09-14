import { Head, Link, useForm, usePage } from '@inertiajs/react';

export default function Login() {
    const { errors } = usePage().props;
    const { data, setData, post, processing } = useForm({ email: '', password: '' });

    return (
        <div className="login-page min-h-screen flex items-center justify-center px-6">
            <Head title="Masuk" />
            <main className="login-card w-full max-w-sm">
                <img src="/images/qr-review-logo.png" alt="QR Review" className="login-logo" />
                <p className="login-brand">QR REVIEW</p>
                <h1>Masuk ke akun Anda</h1>
                <p className="login-copy">Masuk dengan email dan password Anda.</p>

                {errors.email && <p className="login-error">{errors.email}</p>}

                <form
                    onSubmit={(event) => {
                        event.preventDefault();
                        post('/login');
                    }}
                    className="login-form"
                >
                    <label>
                        Email
                        <input
                            type="email"
                            value={data.email}
                            onChange={(event) => setData('email', event.target.value)}
                            autoComplete="email"
                            required
                        />
                    </label>
                    <label>
                        Password
                        <input
                            type="password"
                            value={data.password}
                            onChange={(event) => setData('password', event.target.value)}
                            autoComplete="current-password"
                            required
                        />
                    </label>
                    <button type="submit" disabled={processing} className="login-submit">
                        {processing ? 'Memproses…' : 'Masuk'}
                    </button>
                </form>

                <p className="login-register">
                    Belum punya akun? <Link href="/register">Daftar sekarang</Link>
                </p>
            </main>
        </div>
    );
}
