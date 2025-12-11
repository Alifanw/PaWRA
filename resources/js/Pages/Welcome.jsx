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
            <path
              d="M49.9374 26.0002C49.9374 40.2931 38.668 51.8335 24.9688 51.8335C11.2695 51.8335 0.00012207 40.2931 0.00012207 26.0002C0.00012207 11.7073 11.2695 0.166809 24.9688 0.166809C38.668 0.166809 49.9374 11.7073 49.9374 26.0002Z"
              fill="currentColor"
            />
            <path d="M25 15L30 25L25 35L20 25L25 15Z" fill="white" />
          </svg>

          <div>
            <h1 className="text-lg md:text-xl font-bold text-slate-900 dark:text-white transition-colors">
              Walini Hot Spring
            </h1>
            <p className="text-sm text-slate-600 dark:text-slate-400 transition-colors">
              Sistem Absensi Modern
            </p>
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
      <main className="flex-1 flex items-center justify-center px-6 py-12">
        <div className="max-w-7xl w-full mx-auto">
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">
            {/* Left Content */}
            <div className="flex flex-col justify-center space-y-6">
              <div>
                <h2 className="text-4xl md:text-5xl lg:text-6xl font-extrabold text-slate-900 dark:text-white transition-colors leading-tight mb-4">
                  Air Panas Walini
                </h2>
                <p className="text-base md:text-lg text-slate-600 dark:text-slate-300 transition-colors max-w-lg">
                  Relaksasi alami di mata air panas Walini — hangat, tenang, dan melepaskan penat. Kelola absensi karyawan dengan mudah dan efisien.
                </p>
              </div>

              <div className="flex flex-wrap gap-4 pt-4">
                <Link
                  href={route('login')}
                  className="px-6 py-3 rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-700 transition-colors shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-400"
                >
                  Mulai Sekarang
                </Link>
                <button className="px-6 py-3 rounded-lg border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 font-semibold hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-400">
                  Pelajari Lebih Lanjut
                </button>
              </div>
            </div>

            {/* Right Preview – Modern Dashboard Mockup */}
            <div className="flex justify-center lg:justify-end">
              <div className="w-72 h-80 md:w-96 md:h-96 rounded-2xl shadow-2xl overflow-hidden border border-slate-200 dark:border-slate-700 transition-transform hover:scale-105 hover:shadow-3xl bg-white dark:bg-slate-800 p-4 flex flex-col">
                {/* Mock Header */}
                <div className="flex items-center justify-between mb-3">
                  <div className="text-sm font-bold text-slate-800 dark:text-white">Dashboard</div>
                  <div className="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                    <span className="text-xs font-bold text-blue-600 dark:text-blue-300">A</span>
                  </div>
                </div>

                {/* Stats Grid */}
                <div className="grid grid-cols-2 gap-3 flex-1">
                  {[
                    { label: 'Hadir', value: '142', color: 'bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300' },
                    { label: 'Izin', value: '8', color: 'bg-yellow-100 dark:bg-yellow-900 text-yellow-700 dark:text-yellow-300' },
                    { label: 'Alpha', value: '5', color: 'bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300' },
                    { label: 'Total', value: '155', color: 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300' },
                  ].map((stat, i) => (
                    <div
                      key={i}
                      className={`${stat.color} p-3 rounded-lg text-center`}
                    >
                      <div className="text-lg font-bold">{stat.value}</div>
                      <div className="text-xs mt-1 opacity-90">{stat.label}</div>
                    </div>
                  ))}
                </div>

                {/* Mini Chart */}
                <div className="mt-3 pt-2 border-t border-slate-200 dark:border-slate-700">
                  <div className="flex text-xs text-slate-500 dark:text-slate-400 mb-1">
                    <span>Absensi 7 Hari Terakhir</span>
                  </div>
                  <div className="flex items-end h-10 space-x-1">
                    {[60, 80, 70, 90, 50, 85, 75].map((h, i) => (
                      <div
                        key={i}
                        className="flex-1 bg-blue-500 rounded-t-sm"
                        style={{ height: `${h}%` }}
                      />
                    ))}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>

      {/* Features */}
      <section className="max-w-7xl mx-auto px-6 pb-20">
        <h3 className="text-2xl md:text-3xl font-bold text-slate-900 dark:text-white text-center mb-12 transition-colors">
          Fitur Unggulan
        </h3>
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
          {[
            {
              title: 'Real-time Attendance',
              desc: 'Scan cepat & log langsung tersimpan di cloud.',
              bg: 'bg-red-100 dark:bg-red-900',
              text: 'text-red-600 dark:text-red-300',
              icon: (
                <path
                  d="M10.5 1.5H3.75A2.25 2.25 0 001.5 3.75v12.5A2.25 2.25 0 003.75 18.5h12.5a2.25 2.25 0 002.25-2.25V9.5m-15-4h6m-6 4h10m-10 4h10m-10 4h6"
                  stroke="currentColor"
                  strokeWidth="1.5"
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  fill="none"
                />
              ),
            },
            {
              title: 'Secure Admin Tools',
              desc: 'Role, izin, dan kontrol akses yang aman.',
              bg: 'bg-blue-100 dark:bg-blue-900',
              text: 'text-blue-600 dark:text-blue-300',
              icon: (
                <path
                  d="M16 8a3 3 0 11-6 0 3 3 0 016 0zM2 10a8 8 0 1116 0 8 8 0 01-16 0z"
                  fillRule="evenodd"
                  clipRule="evenodd"
                />
              ),
            },
            {
              title: 'Analytics Insight',
              desc: 'Pantau statistik kehadiran dengan visual yang jelas.',
              bg: 'bg-green-100 dark:bg-green-900',
              text: 'text-green-600 dark:text-green-300',
              icon: (
                <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z" />
              ),
            },
          ].map((feature, index) => (
            <div
              key={index}
              className="p-8 bg-white dark:bg-slate-800 rounded-xl shadow-md hover:shadow-lg hover:-translate-y-2 transition-all border border-slate-200 dark:border-slate-700"
            >
              <div className="flex items-start gap-4">
                <div className={`flex-shrink-0 w-12 h-12 rounded-lg ${feature.bg} flex items-center justify-center`}>
                  <svg className={`w-6 h-6 ${feature.text}`} fill="currentColor" viewBox="0 0 20 20">
                    {feature.icon}
                  </svg>
                </div>
                <div>
                  <h4 className="font-semibold text-lg text-slate-900 dark:text-white mb-1 transition-colors">
                    {feature.title}
                  </h4>
                  <p className="text-slate-600 dark:text-slate-300 text-sm transition-colors">
                    {feature.desc}
                  </p>
                </div>
              </div>
            </div>
          ))}
        </div>
      </section>
    </div>
  );
}