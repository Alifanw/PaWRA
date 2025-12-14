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
    const [errorMsg, setErrorMsg] = useState(null);

    useEffect(() => {
        console.log('AvailabilitySelector useEffect called with:', { productId, checkinDate, checkoutDate });
        
        if (!productId || !checkinDate || !checkoutDate) {
            console.log('Missing required parameters, clearing groups');
            setVillaGroups([]);
            return;
        }

        setLoading(true);
        setErrorMsg(null);

        const url = `/api/availabilities?product_id=${productId}&checkin=${checkinDate}&checkout=${checkoutDate}`;
        console.log('Fetching from URL:', url);
        
        fetch(url)
            .then(res => res.json())
            .then(data => {
                console.log('AvailabilitySelector API Response:', data);
                if (data.success) {
                    const groups = data.data || [];
                    console.log('Setting villaGroups:', groups);
                    setVillaGroups(groups);
                    // Clear selection if not in list anymore
                    if (value && !data.data.some(g => 
                        g.rooms.some(r => r.id == value)
                    )) {
                        onChange(null);
                    }
                } else {
                    console.error('API returned success=false:', data);
                    setErrorMsg(data.message || 'Failed to load availability');
                }
            })
            .catch(err => {
                console.error('Error fetching availability:', err);
                setErrorMsg('Failed to load availability options');
            })
            .finally(() => setLoading(false));
    }, [productId, checkinDate, checkoutDate]);

    const totalRooms = villaGroups.reduce((sum, g) => sum + g.available_rooms, 0);
    
    if (productId && checkinDate && checkoutDate && villaGroups.length === 0 && !loading) {
        console.warn('⚠️ WARNING: villaGroups is empty even though all params are filled!', {
            productId, 
            checkinDate, 
            checkoutDate,
            villaGroupsLength: villaGroups.length
        });
    }
    const isDisabled = disabled || loading || (!productId || !checkinDate || !checkoutDate);

    return (
        <div className="mb-4">
            <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                {label}
            </label>
            <div className="relative">
                <select
                    value={value || ''}
                    onChange={(e) => onChange(e.target.value ? Number(e.target.value) : null)}
                    disabled={isDisabled}
                    className={`w-full px-4 py-2 border rounded-lg appearance-none focus:outline-none focus:ring-2 focus:ring-blue-500 transition dark:bg-slate-700 dark:text-slate-100 dark:border-slate-600 ${
                        isDisabled
                            ? 'bg-gray-100 cursor-not-allowed text-gray-500 dark:bg-slate-800'
                            : 'bg-white cursor-pointer hover:border-gray-400 dark:hover:border-slate-500'
                    } ${
                        error || errorMsg ? 'border-red-500 dark:border-red-400' : 'border-gray-300'
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
                    
                    {villaGroups && villaGroups.length > 0 && villaGroups.map((group) => (
                        <optgroup key={group.parent_unit} label={group.parent_unit}>
                            {group.rooms && group.rooms.length > 0 && group.rooms.map(room => (
                                <option key={room.id} value={room.id}>
                                    {room.unit_name} {room.max_capacity ? `(${room.max_capacity} pax)` : ''}
                                </option>
                            ))}
                        </optgroup>
                    ))}
                </select>
                <ChevronDownIcon className="absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none dark:text-slate-500" />
            </div>

            {error && (
                <p className="mt-1 text-sm text-red-600 dark:text-red-400">{error}</p>
            )}
            {errorMsg && (
                <p className="mt-1 text-sm text-red-600 dark:text-red-400">{errorMsg}</p>
            )}

            {totalRooms === 0 && !loading && productId && checkinDate && checkoutDate && (
                <p className="mt-2 text-sm text-yellow-600 dark:text-yellow-400">
                    No available rooms for selected dates
                </p>
            )}

            {totalRooms > 0 && (
                <p className="mt-1 text-xs text-gray-500 dark:text-slate-400">
                    {totalRooms} room{totalRooms !== 1 ? 's' : ''} available
                </p>
            )}
        </div>
    );
}
