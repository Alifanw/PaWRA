import React from 'react';

export default function PageHeader({ title, description, children }) {
    return (
        <div className="bg-white shadow">
            <div className="py-6 px-6 max-w-7xl mx-auto">
                <h1 className="text-3xl font-bold text-gray-900">{title}</h1>
                {description && (
                    <p className="mt-2 text-gray-600">{description}</p>
                )}
                {children && (
                    <div className="mt-4">
                        {children}
                    </div>
                )}
            </div>
        </div>
    );
}
