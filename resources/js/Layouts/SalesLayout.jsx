import { router, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';

function SettingsIcon() { return <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-2.12 2.12-.06-.06a1.7 1.7 0 0 0-1.88-.34 1.7 1.7 0 0 0-1.04 1.56V20.3h-3v-.08A1.7 1.7 0 0 0 10.66 18.66a1.7 1.7 0 0 0-1.88.34l-.06.06-2.12-2.12.06-.06A1.7 1.7 0 0 0 7 15a1.7 1.7 0 0 0-1.56-1.04H5.3v-3h.14A1.7 1.7 0 0 0 7 9.92a1.7 1.7 0 0 0-.34-1.88L6.6 7.98l2.12-2.12.06.06a1.7 1.7 0 0 0 1.88.34A1.7 1.7 0 0 0 11.7 4.7v-.08h3v.08a1.7 1.7 0 0 0 1.04 1.56 1.7 1.7 0 0 0 1.88-.34l.06-.06 2.12 2.12-.06.06A1.7 1.7 0 0 0 19.4 9.92a1.7 1.7 0 0 0 1.56 1.04h.14v3h-.14A1.7 1.7 0 0 0 19.4 15Z"/></svg>; }
function ThemeIcon({ dark }) { return dark ? <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg> : <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round"><path d="M20.5 15.5A8.5 8.5 0 0 1 8.5 3.5 8.5 8.5 0 1 0 20.5 15.5Z"/></svg>; }
function LogoutIcon() { return <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round"><path d="M10 17l5-5-5-5M15 12H3"/><path d="M21 19V5a2 2 0 0 0-2-2h-6"/></svg>; }

export default function SalesLayout({ children, title }) {
    const { props } = usePage();
    const flashSuccess = props.flash?.success;
    const [settingsOpen, setSettingsOpen] = useState(false);
    const [isDark, setIsDark] = useState(() => typeof window !== 'undefined' && localStorage.getItem('sales-theme') === 'dark');

    useEffect(() => { if (typeof window !== 'undefined') localStorage.setItem('sales-theme', isDark ? 'dark' : 'light'); }, [isDark]);

    return <div className={`sales-shell min-h-screen flex flex-col bg-paper ${isDark ? 'sales-dark' : ''}`}>
        <header className="sales-header px-5 py-3 sticky top-0 z-10"><div className="flex items-center justify-between gap-3 max-w-lg mx-auto"><div className="flex items-center gap-3"><div className="sales-logo-wrap"><img src="/images/qr-review-logo.png" alt="QR Review" className="sales-logo" /></div><div><p className="text-[10px] tracking-[0.18em] font-bold text-emerald-700 uppercase">QR Review</p><h1 className="sales-page-title text-lg font-bold text-slate-900 leading-tight">{title}</h1></div></div><div className="settings-wrap"><button type="button" className="settings-trigger" onClick={() => setSettingsOpen((open) => !open)} aria-expanded={settingsOpen} aria-controls="sales-settings-menu" aria-label="Buka pengaturan"><SettingsIcon /></button>{settingsOpen && <div id="sales-settings-menu" className="settings-menu"><div className="settings-menu-title">Pengaturan</div><button type="button" className="settings-menu-item" onClick={() => setIsDark((dark) => !dark)}><span className="settings-menu-icon"><ThemeIcon dark={isDark} /></span><span>{isDark ? 'Mode siang' : 'Mode malam'}</span><span className="settings-switch" data-active={isDark} aria-hidden="true"><i /></span></button><button type="button" className="settings-menu-item settings-logout" onClick={() => router.post('/logout')}><span className="settings-menu-icon"><LogoutIcon /></span><span>Logout</span></button></div>}</div></div></header>
        {flashSuccess && <div className="mx-4 mt-3 rounded bg-field/10 border border-field text-field-dark px-4 py-3 text-sm font-medium">{flashSuccess}</div>}
        <main className="sales-content flex-1 px-4 py-5 pb-8 w-full mx-auto">{children}</main>
    </div>;
}
