<?php

namespace App\Services;

use App\Models\ContactProperty;
use App\Models\CrmBusinessProfile;
use App\Models\LeadPriorityRule;
use App\Models\LeadSource;
use App\Models\Pipeline;
use App\Models\RetryReason;
use Illuminate\Support\Facades\Cache;

class CrmCacheService
{
    /**
     * Cache TTLs in seconds.
     */
    private const TTL_BOOTSTRAP = 600;    // 10 minutes
    private const TTL_PIPELINES = 300;    // 5 minutes
    private const TTL_SOURCES = 900;      // 15 minutes
    private const TTL_PROPERTIES = 600;   // 10 minutes
    private const TTL_RETRY = 600;        // 10 minutes

    /**
     * Get the full bootstrap payload (used by settings/bootstrap endpoint).
     */
    public static function bootstrap(): array
    {
        return Cache::remember('crm:bootstrap', self::TTL_BOOTSTRAP, function () {
            return [
                'business_profile' => CrmBusinessProfile::first(),
                'lead_sources' => LeadSource::active()->orderBy('name')->get(),
                'contact_properties' => ContactProperty::active()->orderBy('sort_order')->get(),
                'lead_priority_rules' => LeadPriorityRule::orderBy('sort_order')->get(),
                'retry_reasons' => RetryReason::with('rule')->orderBy('sort_order')->get(),
            ];
        });
    }

    /**
     * Get active pipelines with stages and tags.
     */
    public static function activePipelines()
    {
        return Cache::remember('crm:pipelines:active', self::TTL_PIPELINES, function () {
            return Pipeline::with(['stages.tags', 'stages.transitions'])
                ->active()
                ->ordered()
                ->get();
        });
    }

    /**
     * Get active lead sources.
     */
    public static function leadSources()
    {
        return Cache::remember('crm:lead_sources', self::TTL_SOURCES, function () {
            return LeadSource::active()->orderBy('name')->get();
        });
    }

    /**
     * Get active contact properties.
     */
    public static function contactProperties()
    {
        return Cache::remember('crm:contact_properties', self::TTL_PROPERTIES, function () {
            return ContactProperty::active()->orderBy('sort_order')->get();
        });
    }

    /**
     * Get retry reasons with rules.
     */
    public static function retryReasons()
    {
        return Cache::remember('crm:retry_reasons', self::TTL_RETRY, function () {
            return RetryReason::with('rule')->orderBy('sort_order')->orderBy('name')->get();
        });
    }

    /**
     * Flush all CRM caches. Call this after settings are updated.
     */
    public static function flush(): void
    {
        Cache::forget('crm:bootstrap');
        Cache::forget('crm:pipelines:active');
        Cache::forget('crm:lead_sources');
        Cache::forget('crm:contact_properties');
        Cache::forget('crm:retry_reasons');
    }

    /**
     * Flush only pipeline-related caches.
     */
    public static function flushPipelines(): void
    {
        Cache::forget('crm:bootstrap');
        Cache::forget('crm:pipelines:active');
    }

    /**
     * Flush only settings-related caches.
     */
    public static function flushSettings(): void
    {
        Cache::forget('crm:bootstrap');
        Cache::forget('crm:lead_sources');
        Cache::forget('crm:contact_properties');
        Cache::forget('crm:retry_reasons');
    }
}
