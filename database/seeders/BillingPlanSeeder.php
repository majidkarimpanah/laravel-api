<?php

namespace Database\Seeders;

use Common\Auth\Permissions\Permission;
use Common\Billing\BillingPlan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class BillingPlanSeeder extends Seeder
{
    /**
     * @var BillingPlan
     */
    private $plan;

    /**
     * @param BillingPlan $plan
     */
    public function __construct(BillingPlan $plan)
    {
        $this->plan = $plan;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->plan->count() === 0 && config('common.site.demo')) {
            $permissions = app(Permission::class)->pluck('id', 'name');

            $this->createPlan($permissions, [
                'name' => 'Basic',
                'amount' => 7.99,
                'position' => 1,
                'visitors' => 1000,
                'screens' => 1,
            ]);

            $this->createPlan($permissions, [
                'name' => 'Standard',
                'amount' => 9.99,
                'position' => 2,
                'recommended' => true,
                'screens' => 2,
                'hd' => true,
            ]);

            $this->createPlan($permissions, [
                'name' => 'Pro',
                'amount' => 11.99,
                'position' => 3,
                'screens' => 4,
                'hd' => true,
                'ultra-hd' => true,
            ]);
        }
    }

    private function createPlan($permissions, $params)
    {
        $features = [
            'No advertisements',
            'Watch on laptop, TV, phone and tablet',
            'Unlimited movies and TV shows',
            'Cancel anytime',
            'First month free',
        ];

        if (isset($params['hd'])) {
            $features[] = 'HD available';
        }

        if (isset($params['ultra-hd'])) {
            $features[] = 'Ultra HD available';
        }

        $features[] = "{$params['screens']} screen(s) at the same time";

        $basic = $this->plan->create([
            'name' => $params['name'],
            'uuid' => str_random(36),
            'amount' => $params['amount'],
            'currency' => 'USD',
            'currency_symbol' => '$',
            'interval' => 'month',
            'interval_count' => 1,
            'position' => $params['position'],
            'recommended' => Arr::get($params, 'recommended', false),
            'features' => $features,

        ]);

        $this->plan->create([
            'name' => "6 Month Subscription",
            'uuid' => str_random(36),
            'parent_id' => $basic->id,
            'interval' => 'month',
            'interval_count' => 6,
            'amount' => ($params['amount'] * 6) * ((100 - 10) / 100), // 6 months - 10%
            'currency' => 'USD',
            'currency_symbol' => '$',
        ]);

        $this->plan->create([
            'name' => "1 Year Subscription",
            'uuid' => str_random(36),
            'parent_id' => $basic->id,
            'interval' => 'month',
            'interval_count' => 12,
            'amount' => ($params['amount'] * 12) * ((100 - 20) / 100), // 12 months - 20%,
            'currency' => 'USD',
            'currency_symbol' => '$',
        ]);
    }
}
