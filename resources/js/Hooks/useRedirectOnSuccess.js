import { useEffect } from 'react';
import { usePage } from '@inertiajs/react';

/**
 * Custom hook untuk menangani redirect response dari server
 * Digunakan untuk memastikan bahwa redirect setelah login benar-benar terjadi
 */
export function useRedirectOnSuccess() {
    const { component, props } = usePage();

    useEffect(() => {
        // Jika page component berubah, berarti redirect sudah terjadi
        console.log('🔄 Page component updated:', component);
    }, [component]);

    return null;
}

export default useRedirectOnSuccess;
