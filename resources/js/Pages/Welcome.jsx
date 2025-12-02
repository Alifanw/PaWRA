import { Link } from '@inertiajs/react';

export default function Welcome() {
    return (
      <div className="min-h-screen bg-gradient-to-b from-slate-50 to-blue-50 dark:from-slate-900 dark:to-slate-950 flex flex-col transition-colors duration-300">
        {/* Header */}
        <header className="max-w-6xl mx-auto w-full px-6 py-8 flex flex-col md:flex-row items-center justify-between gap-4">
          <div className="flex items-center gap-3">
            <svg
              className="w-10 h-10 text-blue-600 dark:text-blue-500 flex-shrink-0"
              viewBox="0 0 50 52"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
              role="img"
              aria-hidden="true"
            >
              <path d="M49.9374 26.0002C49.9374 40.2931 38.668 51.8335 24.9688 51.8335C11.2695 51.8335 0.00012207 40.2931 0.00012207 26.0002C0.00012207 11.7073 11.2695 0.166809 24.9688 0.166809C38.668 0.166809 49.9374 11.7073 49.9374 26.0002Z" fill="currentColor"/>
              <path d="M25 15L30 25L25 35L20 25L25 15Z" fill="white"/>
            </svg>

            <div>
              <h1 className="text-lg md:text-xl font-bold text-slate-900 dark:text-white transition-colors">Walini Hot Spring</h1>
              <p className="text-sm text-slate-600 dark:text-slate-400 transition-colors">Sistem Absensi Modern</p>
            </div>
          </div>

          <nav className="flex items-center gap-3">
            <Link
              href={route('login')}
              aria-label="Masuk"
              className="text-sm px-4 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors shadow-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-400"
            >
              Masuk
            </Link>
            
          </nav>
        </header>

        {/* Main Content */}
        <main className="flex-1 flex items-center justify-center px-6">
          <div className="max-w-6xl w-full mx-auto">
            <div className="grid grid-cols-1 md:grid-cols-3 items-start gap-12">
              {/* Left Content */}
              <div className="md:col-span-2 flex flex-col justify-center space-y-6 text-center md:text-left">
                <h2 className="text-4xl md:text-6xl font-extrabold text-slate-900 dark:text-white transition-colors leading-tight">
                  Walini Hot Spring
                </h2>

                <p className="max-w-2xl mx-auto md:mx-0 text-base md:text-lg text-slate-600 dark:text-slate-300 transition-colors leading-relaxed">
                  Relaksasi alami di mata air panas Walini — hangat, tenang, dan melepaskan penat.
                  Kelola absensi karyawan dengan mudah dan efisien.
                </p>

              </div>

              {/* Right Preview */}
              <div className="md:col-span-1 flex justify-center md:justify-end">
                <div className="w-64 h-64 md:w-80 md:h-80 rounded-3xl shadow-2xl bg-white dark:bg-slate-800 overflow-hidden relative transition-transform transform hover:scale-105">
                  <div className="absolute inset-0 bg-gradient-to-br from-blue-400 to-purple-500"></div>
                  <span className="relative z-10 flex items-center justify-center h-full text-white text-xl md:text-2xl font-semibold drop-shadow-md">
                    Dashboard Preview
                  </span>
                </div>
              </div>
            </div>
          </div>
        </main>

        {/* Features */}
        <section className="max-w-6xl mx-auto px-6 pb-20">
          <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
            {/* Feature 1 */}
            <div className="p-6 bg-white dark:bg-slate-800 rounded-xl shadow-md hover:shadow-xl hover:-translate-y-1 transition-all border border-slate-200 dark:border-slate-700">
              <div className="flex items-start gap-4">
                <div className="flex-shrink-0 mt-1 text-red-500 text-2xl">●</div>
                <div>
                  <h3 className="font-semibold text-lg text-slate-900 dark:text-white mb-1 transition-colors">
                    Real-time Attendance
                  </h3>
                  <p className="text-slate-600 dark:text-slate-300 text-sm transition-colors">
                    Scan cepat & log langsung tersimpan di cloud.
                  </p>
                </div>
              </div>
            </div>

            {/* Feature 2 */}
            <div className="p-6 bg-white dark:bg-slate-800 rounded-xl shadow-md hover:shadow-xl hover:-translate-y-1 transition-all border border-slate-200 dark:border-slate-700">
              <div className="flex items-start gap-4">
                <div className="flex-shrink-0 mt-1 text-red-500 text-2xl">●</div>
                <div>
                  <h3 className="font-semibold text-lg text-slate-900 dark:text-white mb-1 transition-colors">
                    Secure Admin Tools
                  </h3>
                  <p className="text-slate-600 dark:text-slate-300 text-sm transition-colors">
                    Role, izin, dan kontrol akses yang aman.
                  </p>
                </div>
              </div>
            </div>

            {/* Feature 3 */}
            <div className="p-6 bg-white dark:bg-slate-800 rounded-xl shadow-md hover:shadow-xl hover:-translate-y-1 transition-all border border-slate-200 dark:border-slate-700">
              <div className="flex items-start gap-4">
                <div className="flex-shrink-0 mt-1 text-red-500 text-2xl">●</div>
                <div>
                  <h3 className="font-semibold text-lg text-slate-900 dark:text-white mb-1 transition-colors">
                    Analytics Insight
                  </h3>
                  <p className="text-slate-600 dark:text-slate-300 text-sm transition-colors">
                    Pantau statistik kehadiran dengan visual yang jelas.
                  </p>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
    );
}