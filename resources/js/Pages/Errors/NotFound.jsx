import React from 'react';

export default function NotFound() {
    return (
        <div className="min-h-screen flex items-center justify-center">
            <div className="text-center">
                <h1 className="text-4xl font-bold">404 — Page not found</h1>
                <p className="mt-4 text-gray-600">The page you are looking for does not exist.</p>
            </div>
        </div>
    );
}
