import '../css/app.css';
import './bootstrap';


import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot } from 'react-dom/client';
import { ThemeProvider } from './ThemeProvider';

const appName = import.meta.env.VITE_APP_NAME || 'Walini';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => {
        // Defensive: when Inertia provides a falsy/undefined name we should avoid
        // attempting to import "./Pages/undefined.jsx" which causes an ugly
        // runtime error. Instead provide a clear fallback page and a helpful
        // console message to aid debugging.
        if (!name || typeof name !== 'string') {
            // eslint-disable-next-line no-console
            console.error('Inertia page name is missing or invalid:', name);
            return import('./Pages/Errors/NotFound.jsx');
        }

        return resolvePageComponent(
            `./Pages/${name}.jsx`,
            import.meta.glob('./Pages/**/*.jsx'),
        );
    },
    setup({ el, App, props }) {
        // Defensive: ensure we have a valid mount element. If not, log useful
        // debugging information to help track where the server is not returning
        // a proper Inertia initial page.
        // eslint-disable-next-line no-console
        if (!el) {
            console.error('Inertia mount element is missing', { el, props });
            return;
        }

        const root = createRoot(el);

        root.render(
            <ThemeProvider>
                <App {...props} />
            </ThemeProvider>
        );
    },
    progress: {
        color: '#4B5563',
    },
});
