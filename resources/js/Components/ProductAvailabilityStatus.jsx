import { useState, useEffect } from 'react';
import { CheckIcon, ExclamationTriangleIcon } from '@heroicons/react/24/outline';

export default function ProductAvailabilityStatus({ 
    productId, 
    quantity, 
    checkinDate, 
    checkoutDate,
    onAvailabilityChange 
}) {
    const [availability, setAvailability] = useState(null);
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        if (!productId || !quantity || !checkinDate || !checkoutDate) {
            setAvailability(null);
            return;
        }

        checkAvailability();
    }, [productId, quantity, checkinDate, checkoutDate]);

    const checkAvailability = async () => {
        setLoading(true);
        try {
            const params = new URLSearchParams({
                product_id: productId,
                quantity: quantity,
                checkin: checkinDate,
                checkout: checkoutDate,
            });

            const response = await fetch(`/api/availabilities/check?${params}`);
            const result = await response.json();

            if (result.success) {
                setAvailability(result);
                onAvailabilityChange?.(result.available);
            }
        } catch (error) {
            console.error('Failed to check availability:', error);
        } finally {
            setLoading(false);
        }
    };

    if (!availability && !loading) return null;

    if (loading) {
        return (
            <div className="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                <div className="animate-spin h-4 w-4 border-2 border-blue-500 border-t-transparent rounded-full"></div>
                Checking availability...
            </div>
        );
    }

    return (
        <div className={`flex items-center gap-2 text-sm p-2 rounded ${
            availability.available 
                ? 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300'
                : 'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300'
        }`}>
            {availability.available ? (
                <CheckIcon className="h-4 w-4" />
            ) : (
                <ExclamationTriangleIcon className="h-4 w-4" />
            )}
            <span>{availability.message}</span>
        </div>
    );
}
