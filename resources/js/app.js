import { createInertiaApp } from '@inertiajs/inertia-react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ThemeProvider } from './ThemeProvider';

createInertiaApp({
  resolve: (name) => {
    if (!name || typeof name !== 'string') {
      // eslint-disable-next-line no-console
      console.error('Inertia page name is missing or invalid:', name);
      return import('./Pages/Errors/NotFound.jsx');
    }

    return resolvePageComponent(`./Pages/${name}.jsx`, import.meta.glob('./Pages/**/*.jsx'));
  },
  setup({ el, App, props }) {
    // Defensive: if el is missing, log and do nothing to avoid createRoot errors
    // in environments where the Inertia root isn't present.
    // eslint-disable-next-line no-console
    if (!el) return console.error('Inertia mount element is missing', { el, props });

    return React.createElement(ThemeProvider, {}, React.createElement(App, props));
  },
});
