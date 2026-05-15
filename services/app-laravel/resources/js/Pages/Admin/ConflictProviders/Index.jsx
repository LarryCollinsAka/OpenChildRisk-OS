import React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';

export default function ConflictProvidersIndex({ auth, providers }) {
    const toggleProvider = (providerId) => {
        if (confirm('Are you sure you want to toggle this provider?')) {
            router.post(route('admin.conflict-providers.toggle', providerId));
        }
    };

    return (
        <AppLayout user={auth.user}>
            <Head title="Conflict Data Providers" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 bg-white border-b border-gray-200">
                            <h2 className="text-2xl font-bold mb-6">Conflict Data Providers</h2>

                            <div className="space-y-4">
                                {providers.map((provider) => (
                                    <div
                                        key={provider.id}
                                        className="border rounded-lg p-6 hover:shadow-md transition"
                                    >
                                        <div className="flex justify-between items-start mb-4">
                                            <div>
                                                <h3 className="text-xl font-semibold flex items-center gap-2">
                                                    {provider.name}
                                                    <span className={`text-xs px-2 py-1 rounded ${
                                                        provider.is_active 
                                                            ? 'bg-green-100 text-green-800' 
                                                            : 'bg-gray-100 text-gray-800'
                                                    }`}>
                                                        {provider.is_active ? 'Active' : 'Inactive'}
                                                    </span>
                                                    <span className="text-xs px-2 py-1 rounded bg-blue-100 text-blue-800">
                                                        {provider.provider_type}
                                                    </span>
                                                </h3>
                                                <p className="text-sm text-gray-600 mt-1">
                                                    {provider.description}
                                                </p>
                                            </div>
                                        </div>

                                        <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                                            <div>
                                                <p className="text-xs text-gray-500">Confidence</p>
                                                <p className="text-lg font-semibold">{provider.composite_confidence}</p>
                                            </div>
                                            <div>
                                                <p className="text-xs text-gray-500">Events Ingested</p>
                                                <p className="text-lg font-semibold">{provider.events_count || 0}</p>
                                            </div>
                                            <div>
                                                <p className="text-xs text-gray-500">Update Frequency</p>
                                                <p className="text-lg font-semibold capitalize">{provider.update_frequency}</p>
                                            </div>
                                            <div>
                                                <p className="text-xs text-gray-500">Last Ingestion</p>
                                                <p className="text-sm">{provider.last_successful_ingestion || 'Never'}</p>
                                            </div>
                                        </div>

                                        {provider.requires_institutional_access && (
                                            <div className="bg-yellow-50 border border-yellow-200 rounded p-2 mb-4">
                                                <p className="text-xs text-yellow-800">
                                                    ⚠️ Requires institutional email for API access
                                                </p>
                                            </div>
                                        )}

                                        <div className="flex gap-2">
                                            <Link
                                                href={route('admin.conflict-providers.configurations', provider.id)}
                                                className="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm"
                                            >
                                                Manage Configurations
                                            </Link>
                                            <button
                                                onClick={() => toggleProvider(provider.id)}
                                                className={`px-4 py-2 rounded text-sm ${
                                                    provider.is_active
                                                        ? 'bg-gray-200 text-gray-800 hover:bg-gray-300'
                                                        : 'bg-green-600 text-white hover:bg-green-700'
                                                }`}
                                            >
                                                {provider.is_active ? 'Disable' : 'Enable'}
                                            </button>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}