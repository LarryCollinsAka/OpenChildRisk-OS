import React, { useState } from 'react';
import { Head, Link, router, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';

export default function Configurations({ auth, provider, configurations }) {
    const [showAddForm, setShowAddForm] = useState(false);
    const [editingId, setEditingId] = useState(null);

    const { data, setData, post, put, processing, errors, reset } = useForm({
        config_key: '',
        config_value: '',
        value_type: 'string',
        description: '',
        is_active: true,
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        
        if (editingId) {
            put(route('admin.conflict-providers.configurations.update', [provider.id, editingId]), {
                onSuccess: () => {
                    setEditingId(null);
                    reset();
                }
            });
        } else {
            post(route('admin.conflict-providers.configurations.store', provider.id), {
                onSuccess: () => {
                    setShowAddForm(false);
                    reset();
                }
            });
        }
    };

    const handleEdit = (config) => {
        setData({
            config_key: config.config_key,
            config_value: config.config_value,
            value_type: config.value_type,
            description: config.description || '',
            is_active: config.is_active,
        });
        setEditingId(config.id);
        setShowAddForm(true);
    };

    const handleDelete = (configId) => {
        if (confirm('Are you sure you want to delete this configuration?')) {
            router.delete(route('admin.conflict-providers.configurations.delete', [provider.id, configId]));
        }
    };

    return (
        <AppLayout user={auth.user}>
            <Head title={`${provider.name} - Configurations`} />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 bg-white border-b border-gray-200">
                            <div className="flex justify-between items-center mb-6">
                                <div>
                                    <Link
                                        href={route('admin.conflict-providers.index')}
                                        className="text-blue-600 hover:underline text-sm mb-2 block"
                                    >
                                        ← Back to Providers
                                    </Link>
                                    <h2 className="text-2xl font-bold">{provider.name} - Configurations</h2>
                                    <p className="text-sm text-gray-600">{provider.description}</p>
                                </div>
                                <button
                                    onClick={() => {
                                        setShowAddForm(!showAddForm);
                                        setEditingId(null);
                                        reset();
                                    }}
                                    className="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
                                >
                                    {showAddForm ? 'Cancel' : 'Add Configuration'}
                                </button>
                            </div>

                            {showAddForm && (
                                <div className="bg-gray-50 p-4 rounded mb-6">
                                    <h3 className="font-semibold mb-4">
                                        {editingId ? 'Edit Configuration' : 'Add New Configuration'}
                                    </h3>
                                    <form onSubmit={handleSubmit} className="space-y-4">
                                        <div>
                                            <label className="block text-sm font-medium mb-1">
                                                Config Key
                                                {!editingId && <span className="text-red-500">*</span>}
                                            </label>
                                            <input
                                                type="text"
                                                value={data.config_key}
                                                onChange={(e) => setData('config_key', e.target.value)}
                                                disabled={editingId}
                                                placeholder="e.g., file_id_2024"
                                                className="w-full px-3 py-2 border rounded disabled:bg-gray-100"
                                                required={!editingId}
                                            />
                                            {errors.config_key && (
                                                <p className="text-red-500 text-xs mt-1">{errors.config_key}</p>
                                            )}
                                        </div>

                                        <div>
                                            <label className="block text-sm font-medium mb-1">
                                                Config Value <span className="text-red-500">*</span>
                                            </label>
                                            <input
                                                type="text"
                                                value={data.config_value}
                                                onChange={(e) => setData('config_value', e.target.value)}
                                                placeholder="e.g., 10488291"
                                                className="w-full px-3 py-2 border rounded"
                                                required
                                            />
                                            {errors.config_value && (
                                                <p className="text-red-500 text-xs mt-1">{errors.config_value}</p>
                                            )}
                                        </div>

                                        <div>
                                            <label className="block text-sm font-medium mb-1">Value Type</label>
                                            <select
                                                value={data.value_type}
                                                onChange={(e) => setData('value_type', e.target.value)}
                                                className="w-full px-3 py-2 border rounded"
                                            >
                                                <option value="string">String</option>
                                                <option value="integer">Integer</option>
                                                <option value="url">URL</option>
                                                <option value="json">JSON</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label className="block text-sm font-medium mb-1">Description</label>
                                            <textarea
                                                value={data.description}
                                                onChange={(e) => setData('description', e.target.value)}
                                                placeholder="Optional description"
                                                className="w-full px-3 py-2 border rounded"
                                                rows="2"
                                            />
                                        </div>

                                        {editingId && (
                                            <div className="flex items-center">
                                                <input
                                                    type="checkbox"
                                                    checked={data.is_active}
                                                    onChange={(e) => setData('is_active', e.target.checked)}
                                                    className="mr-2"
                                                />
                                                <label className="text-sm">Active</label>
                                            </div>
                                        )}

                                        <button
                                            type="submit"
                                            disabled={processing}
                                            className="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 disabled:opacity-50"
                                        >
                                            {processing ? 'Saving...' : (editingId ? 'Update' : 'Add')}
                                        </button>
                                    </form>
                                </div>
                            )}

                            <div className="space-y-3">
                                {configurations.length === 0 ? (
                                    <p className="text-gray-500 text-center py-8">
                                        No configurations yet. Click "Add Configuration" to create one.
                                    </p>
                                ) : (
                                    configurations.map((config) => (
                                        <div
                                            key={config.id}
                                            className="border rounded-lg p-4 hover:shadow-md transition"
                                        >
                                            <div className="flex justify-between items-start">
                                                <div className="flex-1">
                                                    <div className="flex items-center gap-2 mb-2">
                                                        <h3 className="font-semibold">{config.config_key}</h3>
                                                        <span className={`text-xs px-2 py-1 rounded ${
                                                            config.is_active
                                                                ? 'bg-green-100 text-green-800'
                                                                : 'bg-gray-100 text-gray-800'
                                                        }`}>
                                                            {config.is_active ? 'Active' : 'Inactive'}
                                                        </span>
                                                        <span className="text-xs px-2 py-1 rounded bg-blue-100 text-blue-800">
                                                            {config.value_type}
                                                        </span>
                                                    </div>
                                                    <p className="text-sm font-mono text-gray-700 mb-1">
                                                        {config.config_value}
                                                    </p>
                                                    {config.description && (
                                                        <p className="text-xs text-gray-500">{config.description}</p>
                                                    )}
                                                    {config.last_verified_at && (
                                                        <p className="text-xs text-gray-400 mt-1">
                                                            Last verified: {config.last_verified_at}
                                                        </p>
                                                    )}
                                                </div>
                                                {config.is_editable_via_ui && (
                                                    <div className="flex gap-2">
                                                        <button
                                                            onClick={() => handleEdit(config)}
                                                            className="px-3 py-1 bg-blue-100 text-blue-800 rounded hover:bg-blue-200 text-sm"
                                                        >
                                                            Edit
                                                        </button>
                                                        <button
                                                            onClick={() => handleDelete(config.id)}
                                                            className="px-3 py-1 bg-red-100 text-red-800 rounded hover:bg-red-200 text-sm"
                                                        >
                                                            Delete
                                                        </button>
                                                    </div>
                                                )}
                                            </div>
                                        </div>
                                    ))
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}