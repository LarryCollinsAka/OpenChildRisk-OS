<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConflictProviderSource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ConflictProviderController extends Controller
{
    /**
     * List all conflict providers
     */
    public function index()
    {
        $providers = ConflictProviderSource::withCount('conflictEvents')
            ->get()
            ->map(function($provider) {
                return [
                    'id' => $provider->id,
                    'code' => $provider->code,
                    'name' => $provider->name,
                    'description' => $provider->description,
                    'provider_type' => $provider->provider_type,
                    'reliability_score' => $provider->reliability_score,
                    'composite_confidence' => $provider->getCompositeConfidence(),
                    'is_active' => $provider->is_active,
                    'api_enabled' => $provider->api_enabled,
                    'requires_institutional_access' => $provider->requires_institutional_access,
                    'update_frequency' => $provider->update_frequency,
                    'events_count' => $provider->conflict_events_count,
                    'last_successful_ingestion' => $provider->last_successful_ingestion?->format('Y-m-d H:i'),
                ];
            });

        return Inertia::render('Admin/ConflictProviders/Index', [
            'providers' => $providers,
        ]);
    }

    /**
     * View provider configurations
     */
    public function configurations(ConflictProviderSource $provider)
    {
        $configurations = DB::table('provider_configurations')
            ->where('provider_source_id', $provider->id)
            ->orderBy('config_key')
            ->get()
            ->map(function($config) {
                return [
                    'id' => $config->id,
                    'config_key' => $config->config_key,
                    'config_value' => $config->config_value,
                    'value_type' => $config->value_type,
                    'description' => $config->description,
                    'is_active' => $config->is_active,
                    'is_editable_via_ui' => $config->is_editable_via_ui,
                    'last_verified_at' => $config->last_verified_at,
                ];
            });

        return Inertia::render('Admin/ConflictProviders/Configurations', [
            'provider' => [
                'id' => $provider->id,
                'code' => $provider->code,
                'name' => $provider->name,
                'description' => $provider->description,
            ],
            'configurations' => $configurations,
        ]);
    }

    /**
     * Store new configuration
     */
    public function storeConfiguration(Request $request, ConflictProviderSource $provider)
    {
        $validated = $request->validate([
            'config_key' => 'required|string|max:255',
            'config_value' => 'required|string',
            'value_type' => 'required|in:string,json,integer,url',
            'description' => 'nullable|string',
        ]);

        DB::table('provider_configurations')->insert([
            'id' => \Illuminate\Support\Str::uuid(),
            'provider_source_id' => $provider->id,
            'config_key' => $validated['config_key'],
            'config_value' => $validated['config_value'],
            'value_type' => $validated['value_type'],
            'description' => $validated['description'] ?? null,
            'is_active' => true,
            'is_editable_via_ui' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()
            ->route('admin.conflict-providers.configurations', $provider)
            ->with('success', 'Configuration added successfully');
    }

    /**
     * Update configuration
     */
    public function updateConfiguration(Request $request, ConflictProviderSource $provider, string $configurationId)
    {
        $validated = $request->validate([
            'config_value' => 'required|string',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        DB::table('provider_configurations')
            ->where('id', $configurationId)
            ->where('provider_source_id', $provider->id)
            ->where('is_editable_via_ui', true)
            ->update([
                'config_value' => $validated['config_value'],
                'description' => $validated['description'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
                'updated_at' => now(),
                'last_verified_at' => now(),
            ]);

        return redirect()
            ->route('admin.conflict-providers.configurations', $provider)
            ->with('success', 'Configuration updated successfully');
    }

    /**
     * Delete configuration
     */
    public function deleteConfiguration(ConflictProviderSource $provider, string $configurationId)
    {
        DB::table('provider_configurations')
            ->where('id', $configurationId)
            ->where('provider_source_id', $provider->id)
            ->where('is_editable_via_ui', true)
            ->delete();

        return redirect()
            ->route('admin.conflict-providers.configurations', $provider)
            ->with('success', 'Configuration deleted successfully');
    }

    /**
     * Toggle provider active status
     */
    public function toggle(ConflictProviderSource $provider)
    {
        $provider->update([
            'is_active' => !$provider->is_active,
        ]);

        $status = $provider->is_active ? 'enabled' : 'disabled';

        return redirect()
            ->route('admin.conflict-providers.index')
            ->with('success', "Provider {$status} successfully");
    }
}