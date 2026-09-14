import { Head, Link, useForm } from '@inertiajs/react';

export default function Register() {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    return (
        <div className="login-page min-h-screen flex items-center justify-center px-6 py-8">
            <Head title="Daftar" />
            <main className="login-card w-full max-w-sm">
                <img src="/images/qr-review-logo.png" alt="QR Review" className="login-logo" />
                <p className="login-brand">QR REVIEW</p>
                <h1>Buat akun sales</h1>
                <p className="login-copy">Lengkapi data berikut untuk daftar sebagai sales.</p>

                <form
                    onSubmit={(event) => {
                        event.preventDefault();
                        post('/register');
                    }}
                    className="login-form"
                >
                    <label>
                        Nama
                        <input
                            value={data.name}
                            onChange={(event) => setData('name', event.target.value)}
                            autoComplete="name"
                            required
                        />
                        {errors.name && <small>{errors.name}</small>}
                    </label>
                    <label>
                        Email
                        <input
                            type="email"
                            value={data.email}
                            onChange={(event) => setData('email', event.target.value)}
                            autoComplete="email"
                            required
                        />
                        {errors.email && <small>{errors.email}</small>}
                    </label>
                    <label>
                        Password
                        <input
                            type="password"
                            value={data.password}
                            onChange={(event) => setData('password', event.target.value)}
                            autoComplete="new-password"
                            required
                        />
                        {errors.password && <small>{errors.password}</small>}
                    </label>
                    <label>
                        Konfirmasi password
                        <input
                            type="password"
                            value={data.password_confirmation}
                            onChange={(event) => setData('password_confirmation', event.target.value)}
                            autoComplete="new-password"
                            required
                        />
                    </label>
                    <button type="submit" disabled={processing} className="login-submit">
                        {processing ? 'Memproses…' : 'Daftar sebagai sales'}
                    </button>
                </form>

                <p className="login-register">
                    Sudah punya akun? <Link href="/login">Masuk</Link>
                </p>
            </main>
        </div>
    );
}
