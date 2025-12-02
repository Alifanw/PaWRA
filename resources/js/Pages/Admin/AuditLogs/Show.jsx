import React from 'react';
import AdminLayout from '@/Layouts/AdminLayout';

export default function Show({ auth }) {
  return (
    <AdminLayout auth={auth} title="Audit Log Details">
      <div className="mb-6">
        <h1 className="text-2xl font-semibold text-slate-900 dark:text-slate-100">Audit Log Details</h1>
        <p className="mt-1 text-sm text-slate-600 dark:text-slate-400">
          View audit log information
        </p>
      </div>

      <div className="bg-white dark:bg-slate-800 rounded-lg shadow p-6">
        <p className="text-slate-600 dark:text-slate-400\">Audit Log Show Page</p>
      </div>
    </AdminLayout>
  );
}
