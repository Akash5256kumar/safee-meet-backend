<?php

namespace Database\Seeders;

use App\Models\Feature;
use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class FeatureSeeder extends Seeder
{
    /**
     * Seeds the comparison feature catalog and maps each canonical plan to it.
     * Idempotent — features keyed on slug, plan mappings synced. `pin_search`
     * value is derived from each plan's pin_search_limit column so there's a
     * single source of truth (null = "Unlimited").
     */
    public function run(): void
    {
        // ── Feature catalog (comparison rows) ────────────────────────────────
        $catalog = [
            // Verification & Identity
            ['identity_registration', 'Identity Registration', 'boolean', 'Verification & Identity'],
            ['level1_verification', 'Level 1 Verification', 'boolean', 'Verification & Identity'],
            ['level2_clearance', 'Level 2 Clearance', 'boolean', 'Verification & Identity'],
            ['background_verification', 'Background Verification', 'boolean', 'Verification & Identity'],
            ['professional_verification', 'Professional Verification', 'boolean', 'Verification & Identity'],
            // Search & Connect
            ['pin_search', 'SAFEE PIN Search / Chat', 'limit', 'Search & Connect'],
            ['qr_code', 'QR Code Generation', 'boolean', 'Search & Connect'],
            ['meeting_history', 'Meeting History', 'limit', 'Search & Connect'],
            // Safety
            ['basic_safety_tips', 'Basic Safety Tips', 'boolean', 'Safety'],
            ['community_guidelines', 'Community Guidelines Access', 'boolean', 'Safety'],
            ['trust_score', 'Trust Score Calculation', 'boolean', 'Safety'],
            ['safety_score_analytics', 'Safety Score Analytics', 'boolean', 'Safety'],
            ['priority_visibility', 'Priority Visibility', 'boolean', 'Safety'],
            ['trusted_contact_alerts', 'Trusted Contact Alerts', 'boolean', 'Safety'],
            // Badges & Support
            ['verified_badge', 'Verified Badge Display', 'boolean', 'Badges & Support'],
            ['premium_badge', 'Premium Badge', 'boolean', 'Badges & Support'],
            ['priority_support', 'Priority Support', 'boolean', 'Badges & Support'],
            // Business / Pro
            ['business_listing', 'Business Listing', 'boolean', 'Business / Pro'],
            ['api_access', 'API Access', 'boolean', 'Business / Pro'],
            ['dedicated_account_manager', 'Dedicated Account Manager', 'boolean', 'Business / Pro'],
            ['custom_integrations', 'Custom Integrations', 'boolean', 'Business / Pro'],
        ];

        $featureIds = [];
        foreach ($catalog as $i => [$slug, $name, $type, $group]) {
            $feature = Feature::updateOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'type' => $type, 'group' => $group, 'sort_order' => $i + 1, 'is_active' => true],
            );
            $featureIds[$slug] = $feature->id;
        }

        // ── Per-plan feature sets (boolean slugs cumulative per the pricing
        //    sheet; meeting_history value stated explicitly). pin_search value
        //    is filled from the plan's pin_search_limit below. ──────────────
        $booleanByPlan = [
            'free_trial' => ['identity_registration', 'level1_verification', 'basic_safety_tips', 'community_guidelines'],
            'basic_limited' => ['identity_registration', 'level1_verification', 'basic_safety_tips', 'community_guidelines'],
            'basic' => ['identity_registration', 'level1_verification', 'basic_safety_tips', 'community_guidelines', 'verified_badge', 'priority_support', 'qr_code'],
            'premium' => ['identity_registration', 'level1_verification', 'basic_safety_tips', 'community_guidelines', 'verified_badge', 'priority_support', 'qr_code', 'level2_clearance', 'background_verification', 'trust_score', 'safety_score_analytics', 'premium_badge', 'priority_visibility', 'trusted_contact_alerts'],
            'professional' => ['identity_registration', 'level1_verification', 'basic_safety_tips', 'community_guidelines', 'verified_badge', 'priority_support', 'qr_code', 'level2_clearance', 'background_verification', 'trust_score', 'safety_score_analytics', 'premium_badge', 'priority_visibility', 'trusted_contact_alerts', 'professional_verification', 'business_listing', 'api_access', 'dedicated_account_manager', 'custom_integrations'],
        ];

        $meetingHistory = [
            'free_trial' => 'Limited',
            'basic_limited' => 'Limited',
            'basic' => 'Full',
            'premium' => 'Full',
            'professional' => 'Full',
        ];

        foreach ($booleanByPlan as $planSlug => $booleanSlugs) {
            $plan = SubscriptionPlan::where('slug', $planSlug)->first();
            if (! $plan) {
                continue;
            }

            $sync = [];

            // Boolean features included for this plan.
            foreach ($booleanSlugs as $slug) {
                $sync[$featureIds[$slug]] = ['included' => true, 'value' => null];
            }

            // pin_search — value from the plan's own limit column.
            $sync[$featureIds['pin_search']] = [
                'included' => true,
                'value' => $plan->pin_search_limit === null ? 'Unlimited' : (string) $plan->pin_search_limit,
            ];

            // meeting_history — limit value per plan.
            $sync[$featureIds['meeting_history']] = ['included' => true, 'value' => $meetingHistory[$planSlug]];

            // sync() also removes any feature no longer assigned to this plan.
            $plan->comparisonFeatures()->sync($sync);
        }
    }
}
