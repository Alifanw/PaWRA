import AdminLayout from '@/Layouts/AdminLayout';

export default function Settings({ auth, settings = {} }) {
    return (
        <AdminLayout auth={auth} title="Settings">
            <div className="mb-6">
                <h1 className="text-2xl font-semibold text-slate-900 dark:text-slate-100">Settings</h1>
                <p className="mt-1 text-sm text-slate-600 dark:text-slate-400">Application and account settings</p>
            </div>

            <div className="bg-white dark:bg-slate-800 rounded-lg shadow p-6">
                <div className="max-w-3xl space-y-6">
                    <section>
                        <h2 className="text-lg font-medium text-slate-900 dark:text-slate-100">General</h2>
                        <p className="mt-2 text-sm text-slate-500 dark:text-slate-400">Basic application preferences.</p>
                    </section>

                    <section>
                        <h2 className="text-lg font-medium text-slate-900 dark:text-slate-100">Notifications</h2>
                        <p className="mt-2 text-sm text-slate-500 dark:text-slate-400">Manage notification preferences.</p>
                    </section>

                    <div className="mt-4">
                        <a href="/admin/settings/edit" className="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Edit Settings</a>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
