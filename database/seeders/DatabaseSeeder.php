<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->firstOrCreate(
            ['email' => 'demo.customer@example.com'],
            ['name' => 'Demo Customer', 'is_admin' => false, 'password' => bcrypt('password')],
        );

        User::query()->firstOrCreate(
            ['email' => 'admin@demo.test'],
            ['name' => 'Demo Admin', 'is_admin' => true, 'password' => bcrypt('password')],
        );

        $this->seedRiskRules();
    }

    /**
     * Seed a few risk rules so the admin panel's Risk Rules section is populated out of the
     * box. These persist to laravel-rebel-admin-api's rebel_risk_rules table; you can add more
     * from the panel (which POSTs to the same store).
     */
    private function seedRiskRules(): void
    {
        $model = \Padosoft\Rebel\AdminApi\Models\RiskRule::class;

        if (! class_exists($model)) {
            return;
        }

        $rules = [
            ['key' => 'high_value', 'signal' => 'amount', 'operator' => '>', 'value' => '1000', 'action' => 'require_step_up', 'required_assurance' => 'aal2', 'phishing_resistant' => true, 'status' => 'active'],
            ['key' => 'new_device', 'signal' => 'new_device', 'operator' => '==', 'value' => '1', 'action' => 'require_step_up', 'required_assurance' => 'aal2', 'phishing_resistant' => false, 'status' => 'active'],
            ['key' => 'b2b_credit', 'signal' => 'b2b_credit', 'operator' => '==', 'value' => '1', 'action' => 'require_step_up', 'required_assurance' => 'aal3', 'phishing_resistant' => true, 'status' => 'active'],
            ['key' => 'impossible_travel', 'signal' => 'velocity', 'operator' => '>', 'value' => '800', 'action' => 'block', 'required_assurance' => 'aal3', 'phishing_resistant' => true, 'status' => 'active'],
            ['key' => 'risky_country', 'signal' => 'country', 'operator' => 'in', 'value' => 'NG,RO,BY', 'action' => 'require_step_up', 'required_assurance' => 'aal2', 'phishing_resistant' => false, 'status' => 'active'],
            ['key' => 'velocity_burst', 'signal' => 'velocity', 'operator' => '>', 'value' => '40', 'action' => 'require_step_up', 'required_assurance' => 'aal2', 'phishing_resistant' => false, 'status' => 'draft'],
        ];

        foreach ($rules as $rule) {
            $model::query()->updateOrCreate(['tenant_id' => null, 'key' => $rule['key']], $rule);
        }
    }
}
