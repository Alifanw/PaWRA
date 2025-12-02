import AppLayout from '../Layouts/AppLayout';
import { Head } from '@inertiajs/react';

export default function Dashboard() {
    return (
        <AppLayout>
            <Head title="Dashboard" />
            <div className="card" style={{ maxWidth: 600, margin: "40px auto" }}>
                <h1 className="text-2xl font-bold mb-4">Dashboard</h1>
                <div>You're logged in!</div>
            </div>
        </AppLayout>
    );
}
