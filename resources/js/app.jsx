import './bootstrap';
import { createInertiaApp, router } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';
import { useEffect, useState } from 'react';

function PageLoadingIndicator() {
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        let timer;
        let visitInProgress = false;
        const removeStart = router.on('start', () => {
            visitInProgress = true;
            timer = window.setTimeout(() => {
                if (visitInProgress) setLoading(true);
            }, 150);
        });
        const removeFinish = router.on('finish', () => {
            visitInProgress = false;
            window.clearTimeout(timer);
            setLoading(false);
        });

        return () => {
            removeStart();
            removeFinish();
            window.clearTimeout(timer);
        };
    }, []);

    return <div className={`app-loading ${loading ? 'is-visible' : ''}`} aria-live="polite" aria-busy={loading}>
        <span className="app-loading-spinner" aria-hidden="true" />
        <span>Memuat…</span>
    </div>;
}

createInertiaApp({
    resolve: (name) => {
        const pages = import.meta.glob('./Pages/**/*.jsx', { eager: true });
        return pages[`./Pages/${name}.jsx`];
    },
    setup({ el, App, props }) {
        createRoot(el).render(<><App {...props} /><PageLoadingIndicator /></>);
    },
});

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(() => {
            // Silent fail: app still works without offline caching.
        });
    });
}
