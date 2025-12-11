import ApplicationLogo from '@/Components/ApplicationLogo';
import { Link } from '@inertiajs/react';
import { useTheme } from '../ThemeProvider';

export default function GuestLayout({ children }) {
    const { theme, toggleTheme } = useTheme();
    return (
        <div className="flex min-h-screen flex-col items-center bg-bg pt-6 sm:justify-center sm:pt-0 transition-theme">
            <div className="flex items-center gap-4">
                <Link href="/">
                    <ApplicationLogo className="h-20 w-20 fill-current text-primary" />
                </Link>
                <button
                    aria-label="Toggle dark mode"
                    onClick={toggleTheme}
                    className="bg-card border border-border-color rounded-full w-10 h-10 flex items-center justify-center shadow-card transition-theme"
                >
                    {theme === 'dark' ? '🌙' : '☀️'}
                </button>
            </div>
            <div className="mt-6 w-full overflow-hidden bg-card px-6 py-4 shadow-card sm:max-w-md sm:rounded-lg transition-theme">
                {children}
            </div>
        </div>
    );
}
