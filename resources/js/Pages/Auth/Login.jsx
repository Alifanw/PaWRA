import { Head, Link, useForm } from '@inertiajs/react';

export default function Login({ status, canResetPassword }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        username: '',
        password: '',
        remember: false,
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('login'), { onFinish: () => reset('password') });
    };

    return (
        <>
            <Head title="Login" />

            <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-50 to-blue-50 dark:from-slate-900 dark:to-slate-950 p-4 transition-colors duration-300">
                
                {/* Decorative elements */}
                <div className="absolute inset-0 overflow-hidden pointer-events-none">
                    <div className="absolute top-20 right-20 w-72 h-72 bg-blue-500 rounded-full mix-blend-multiply filter blur-3xl opacity-10 dark:opacity-5"></div>
                    <div className="absolute bottom-20 left-20 w-72 h-72 bg-blue-600 rounded-full mix-blend-multiply filter blur-3xl opacity-10 dark:opacity-5"></div>
                </div>

                {/* Main Container */}
                <div className="relative flex flex-col md:flex-row gap-0 max-w-4xl w-full rounded-3xl shadow-2xl overflow-hidden bg-white dark:bg-slate-900 transition-colors duration-300">

                    {/* LEFT PANEL - Brand Intro */}
                    <div className="hidden md:flex flex-col justify-center p-10 bg-gradient-to-br from-blue-500 to-blue-700 dark:from-blue-600 dark:to-blue-800 text-white md:w-2/5 relative overflow-hidden">
                        
                        <div className="relative z-10">
                            <div className="flex items-center gap-2 mb-8">
                                <svg className="w-10 h-10 text-white drop-shadow-lg" viewBox="0 0 50 52" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M49.9374 26.0002C49.9374 40.2931 38.668 51.8335 24.9688 51.8335C11.2695 51.8335 0.00012207 40.2931 0.00012207 26.0002C0.00012207 11.7073 11.2695 0.166809 24.9688 0.166809C38.668 0.166809 49.9374 11.7073 49.9374 26.0002Z" fill="white"/>
                                    <path d="M25 15L30 25L25 35L20 25L25 15Z" fill="#3B82F6"/>
                                </svg>
                            </div>
                            
                            <h1 className="text-3xl md:text-4xl font-bold leading-tight mb-3">
                                Selamat<br />Datang
                            </h1>
                            <p className="text-blue-100 dark:text-blue-200 text-base leading-relaxed mb-8">
                                Login dan lanjutkan aktivitas Anda di dashboard admin.
                            </p>

                            <div className="text-sm">
                                Belum punya akun?{' '}
                                <Link
                                    href={route('register')}
                                    className="underline font-semibold hover:text-blue-100 transition"
                                >
                                    Daftar sekarang
                                </Link>
                            </div>
                        </div>
                    </div>

                    {/* RIGHT PANEL - LOGIN FORM */}
                    <div className="p-10 w-full md:w-3/5 flex flex-col justify-center bg-white dark:bg-slate-900 transition-colors duration-300">
                        
                        {/* Mobile Logo */}
                        <div className="md:hidden flex items-center gap-2 mb-8">
                            <svg className="w-8 h-8 text-blue-500" viewBox="0 0 50 52" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M49.9374 26.0002C49.9374 40.2931 38.668 51.8335 24.9688 51.8335C11.2695 51.8335 0.00012207 40.2931 0.00012207 26.0002C0.00012207 11.7073 11.2695 0.166809 24.9688 0.166809C38.668 0.166809 49.9374 11.7073 49.9374 26.0002Z" fill="currentColor"/>
                            </svg>
                        </div>

                        <div className="mb-6">
                            <h2 className="text-2xl font-bold text-slate-900 dark:text-white mb-1 transition-colors">Login Akun</h2>
                        </div>

                        {status && (
                            <div className="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-sm text-green-700 dark:text-green-300 flex items-center gap-2 transition-colors">
                                <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
                                </svg>
                                {status}
                            </div>
                        )}

                        <form onSubmit={submit} className="space-y-6">
                            {/* Username Field */}
                            <div>
                                <label htmlFor="username" className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2 transition-colors">
                                    Username
                                </label>
                                <input
                                    id="username"
                                    type="text"
                                    value={data.username}
                                    onChange={(e) => setData('username', e.target.value)}
                                    className="w-full px-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none placeholder:text-slate-400 dark:placeholder:text-slate-500"
                                    autoComplete="username"
                                    autoFocus
                                />
                                {errors.username && (
                                    <p className="mt-1 text-sm text-red-500 dark:text-red-400">{errors.username}</p>
                                )}
                            </div>

                            {/* Password Field */}
                            <div>
                                <label htmlFor="password" className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2 transition-colors">
                                    Password
                                </label>
                                <input
                                    id="password"
                                    type="password"
                                    value={data.password}
                                    onChange={(e) => setData('password', e.target.value)}
                                    className="w-full px-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none placeholder:text-slate-400 dark:placeholder:text-slate-500"
                                    autoComplete="current-password"
                                />
                                {errors.password && (
                                    <p className="mt-1 text-sm text-red-500 dark:text-red-400">{errors.password}</p>
                                )}
                            </div>

                            {/* Remember Me */}
                            <label className="flex items-center gap-2 cursor-pointer text-sm text-slate-700 dark:text-slate-300 transition-colors">
                                <input
                                    type="checkbox"
                                    checked={data.remember}
                                    onChange={(e) => setData('remember', e.target.checked)}
                                    className="w-4 h-4 text-blue-600 border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 rounded focus:ring-blue-500 transition-colors"
                                />
                                Ingat saya
                            </label>

                            {/* Forgot Password & Submit Button */}
                            <div className="flex items-center justify-between gap-4 mt-6">
                                {canResetPassword && (
                                    <Link
                                        href={route('password.request')}
                                        className="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 underline font-medium transition-colors"
                                    >
                                        Lupa password?
                                    </Link>
                                )}

                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="px-6 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 dark:bg-blue-600 dark:hover:bg-blue-700 text-white shadow-md hover:shadow-lg transition-all active:scale-95 disabled:opacity-70 disabled:cursor-not-allowed font-medium"
                                >
                                    {processing ? 'Loading...' : 'LOGIN'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </>
    );
}