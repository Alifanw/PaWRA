import AdminLayout from '@/Layouts/AdminLayout';
import { ArrowUpIcon, ArrowDownIcon, UsersIcon, ShoppingBagIcon, TicketIcon, CurrencyDollarIcon } from '@heroicons/react/24/outline';
import { Line, Bar } from 'react-chartjs-2';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    Title,
    Tooltip,
    Legend,
} from 'chart.js';

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    Title,
    Tooltip,
    Legend
);

export default function Dashboard({ auth, stats, recentBookings, dailySales }) {
    const statCards = [
        {
            name: 'Total Bookings (Today)',
            value: stats?.today_bookings || 0,
            change: '+12%',
            changeType: 'positive',
            icon: ShoppingBagIcon,
        },
        {
            name: 'Ticket Sales (Today)',
            value: stats?.today_ticket_sales || 0,
            change: '+5.2%',
            changeType: 'positive',
            icon: TicketIcon,
        },
        {
            name: 'Revenue (Today)',
            value: `Rp ${Number(stats?.today_revenue || 0).toLocaleString('id-ID')}`,
            change: '+8.1%',
            changeType: 'positive',
            icon: CurrencyDollarIcon,
        },
        {
            name: 'Active Users',
            value: stats?.active_users || 0,
            change: '-2%',
            changeType: 'negative',
            icon: UsersIcon,
        },
    ];

    const salesChartData = {
        labels: dailySales?.map(d => d.sale_date) || [],
        datasets: [
            {
                label: 'Daily Sales',
                data: dailySales?.map(d => d.net_amount) || [],
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.5)',
            },
        ],
    };

    const salesChartOptions = {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
            },
            title: {
                display: true,
                text: 'Daily Sales (Last 7 Days)',
            },
        },
    };

    return (
        <AdminLayout auth={auth} title="Dashboard">
            {/* Page header */}
            <div className="mb-6">
                <h1 className="text-2xl font-semibold text-gray-900">Dashboard</h1>
                <p className="mt-1 text-sm text-gray-600">
                    Welcome back, {auth.user.full_name}
                </p>
            </div>

            {/* Stats cards */}
            <div className="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
                {statCards.map((stat) => (
                    <div
                        key={stat.name}
                        className="relative overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:px-6 sm:py-6"
                    >
                        <dt>
                            <div className="absolute rounded-md bg-blue-500 p-3">
                                <stat.icon className="h-6 w-6 text-white" aria-hidden="true" />
                            </div>
                            <p className="ml-16 truncate text-sm font-medium text-gray-500">{stat.name}</p>
                        </dt>
                        <dd className="ml-16 flex items-baseline">
                            <p className="text-2xl font-semibold text-gray-900">{stat.value}</p>
                            <p
                                className={`ml-2 flex items-baseline text-sm font-semibold ${
                                    stat.changeType === 'positive' ? 'text-green-600' : 'text-red-600'
                                }`}
                            >
                                {stat.changeType === 'positive' ? (
                                    <ArrowUpIcon className="h-5 w-5 flex-shrink-0 self-center text-green-500" aria-hidden="true" />
                                ) : (
                                    <ArrowDownIcon className="h-5 w-5 flex-shrink-0 self-center text-red-500" aria-hidden="true" />
                                )}
                                <span className="ml-1">{stat.change}</span>
                            </p>
                        </dd>
                    </div>
                ))}
            </div>

            <div className="grid grid-cols-1 gap-5 lg:grid-cols-2">
                {/* Sales chart */}
                <div className="bg-white rounded-lg shadow p-6">
                    <Line options={salesChartOptions} data={salesChartData} />
                </div>

                {/* Recent bookings */}
                <div className="bg-white rounded-lg shadow p-6">
                    <h3 className="text-lg font-medium text-gray-900 mb-4">Recent Bookings</h3>
                    <div className="flow-root">
                        <ul className="-my-5 divide-y divide-gray-200">
                            {recentBookings?.map((booking) => (
                                <li key={booking.id} className="py-4">
                                    <div className="flex items-center space-x-4">
                                        <div className="flex-1 min-w-0">
                                            <p className="truncate text-sm font-medium text-gray-900">
                                                {booking.customer_name}
                                            </p>
                                            <p className="truncate text-sm text-gray-500">
                                                {booking.booking_code}
                                            </p>
                                        </div>
                                        <div>
                                            <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                                                booking.status === 'confirmed' ? 'bg-green-100 text-green-800' :
                                                booking.status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                                                'bg-gray-100 text-gray-800'
                                            }`}>
                                                {booking.status}
                                            </span>
                                        </div>
                                    </div>
                                </li>
                            ))}
                        </ul>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
