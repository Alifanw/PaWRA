import { useEffect, useState } from 'react';
import { ChevronDownIcon } from '@heroicons/react/24/outline';

export default function AvailabilitySelector({ 
    productId, 
    checkinDate, 
    checkoutDate, 
    value, 
    onChange,
    disabled = false,
    error = null,
    label = 'Select Unit'
}) {
    const [villaGroups, setVillaGroups] = useState([]);
    const [loading, setLoading] = useState(false);
    const [error_msg, setError] = useState(null);

    useEffect(() => {
        if (!productId || !checkinDate || !checkoutDate) {
            setVillaGroups([]);
            return;
        }

        setLoading(true);
        setError(null);

        // Call availability API
        fetch(`/api/availabilities?product_id=${productId}&checkin=${checkinDate}&checkout=${checkoutDate}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    setVillaGroups(data.data || []);
                    // Clear selection if not in list anymore
                    if (value && !data.data.some(g => 
                        g.rooms.some(r => r.id == value)
                    )) {
                        onChange(null);
                    }
                } else {
                    setError_msg(data.message || 'Failed to load availability');
                }
            })
            .catch(err => {
                console.error('Error fetching availability:', err);
                setError_msg('Failed to load availability options');
            })
            .finally(() => setLoading(false));
    }, [productId, checkinDate, checkoutDate]);

    const totalRooms = villaGroups.reduce((sum, g) => sum + g.available_rooms, 0);
    const isDisabled = disabled || loading || (!productId || !checkinDate || !checkoutDate);

    return (
        <div className="mb-4">
            <label className="block text-sm font-medium text-gray-700 mb-2">
                {label}
            </label>
            <div className="relative">
                <select
                    value={value || ''}
                    onChange={(e) => onChange(e.target.value ? Number(e.target.value) : null)}
                    disabled={isDisabled}
                    className={`w-full px-4 py-2 border rounded-lg appearance-none focus:outline-none focus:ring-2 focus:ring-blue-500 transition ${
                        isDisabled
                            ? 'bg-gray-100 cursor-not-allowed text-gray-500'
                            : 'bg-white cursor-pointer hover:border-gray-400'
                    } ${
                        error || error_msg ? 'border-red-500' : 'border-gray-300'
                    }`}
                >
                    <option value="">
                        {!productId || !checkinDate || !checkoutDate 
                            ? 'Please fill in product & dates first'
                            : loading 
                            ? 'Loading...' 
                            : totalRooms === 0
                            ? 'No available rooms for selected dates'
                            : `Choose room (${totalRooms} available)...`}
                    </option>
                    
                    {villaGroups.map((group) => (
                        <optgroup key={group.parent_unit} label={group.parent_unit}>
                            {group.rooms.map(room => (
                                <option key={room.id} value={room.id}>
                                    {room.unit_name} {room.max_capacity ? `(${room.max_capacity} pax)` : ''}
                                </option>
                            ))}
                        </optgroup>
                    ))}
                </select>
                <ChevronDownIcon className="absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none" />
            </div>

            {error && (
                <p className="mt-1 text-sm text-red-600">{error}</p>
            )}
            {error_msg && (
                <p className="mt-1 text-sm text-red-600">{error_msg}</p>
            )}

            {totalRooms === 0 && !loading && productId && checkinDate && checkoutDate && (
                <p className="mt-2 text-sm text-yellow-600">
                    No available rooms for selected dates
                </p>
            )}

            {totalRooms > 0 && (
                <p className="mt-1 text-xs text-gray-500">
                    {totalRooms} room{totalRooms !== 1 ? 's' : ''} available
                </p>
            )}
        </div>
    );
}
