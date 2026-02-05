<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubscriptionPlan;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Free',
                'slug' => 'free',
                'price' => 0,
                'billing_cycle' => 'monthly',
                'boost_multiplier' => 1.00,
                'max_featured_listings' => 0,
                'badge_enabled' => false,
                'badge_text' => null,
                'features' => [
                    'Basic listing',
                    'Standard search visibility',
                    'Basic analytics',
                ],
                'is_active' => true,
                'sort_order' => 0,
            ],
            [
                'name' => 'Bronze',
                'slug' => 'bronze',
                'price' => 15000,
                'billing_cycle' => 'monthly',
                'boost_multiplier' => 1.50,
                'max_featured_listings' => 3,
                'badge_enabled' => true,
                'badge_text' => 'Bronze Seller',
                'features' => [
                    '1.5x ranking boost',
                    '3 featured listings',
                    'Bronze seller badge',
                    'Priority support',
                ],
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Silver',
                'slug' => 'silver',
                'price' => 45000,
                'billing_cycle' => 'monthly',
                'boost_multiplier' => 2.00,
                'max_featured_listings' => 10,
                'badge_enabled' => true,
                'badge_text' => 'Silver Seller',
                'features' => [
                    '2x ranking boost',
                    '10 featured listings',
                    'Silver seller badge',
                    'Priority support',
                    'Advanced analytics',
                ],
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Gold',
                'slug' => 'gold',
                'price' => 100000,
                'billing_cycle' => 'monthly',
                'boost_multiplier' => 3.00,
                'max_featured_listings' => -1, // Unlimited
                'badge_enabled' => true,
                'badge_text' => 'Gold Seller',
                'features' => [
                    '3x ranking boost',
                    'Unlimited featured listings',
                    'Gold seller badge',
                    'Premium support',
                    'Advanced analytics',
                    'Promotional banners',
                ],
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }

        $this->command->info('Subscription plans seeded successfully!');
    }
}
