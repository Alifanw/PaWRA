import AdminLayout from '@/Layouts/AdminLayout';

export default function Profile({ auth }) {
    return (
        <AdminLayout auth={auth} title="Profile">
            <div className="mb-6">
                <h1 className="text-2xl font-semibold text-slate-900 dark:text-slate-100">Profile</h1>
                <p className="mt-1 text-sm text-slate-600 dark:text-slate-400">Manage your account information</p>
            </div>

            <div className="bg-white dark:bg-slate-800 rounded-lg shadow p-6">
                <div className="max-w-2xl">
                    <p className="text-sm text-slate-500 dark:text-slate-400">Name</p>
                    <div className="text-lg font-medium text-slate-900 dark:text-slate-100">{auth?.user?.full_name || 'N/A'}</div>

                    <p className="mt-4 text-sm text-slate-500 dark:text-slate-400">Email</p>
                    <div className="text-lg font-medium text-slate-900 dark:text-slate-100">{auth?.user?.email || 'N/A'}</div>

                    <div className="mt-6">
                        <a href="/admin/profile/edit" className="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Edit Profile</a>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
