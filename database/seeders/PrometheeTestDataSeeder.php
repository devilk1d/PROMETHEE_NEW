<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Cases;
use App\Models\Criteria;
use App\Models\Alternative;
use App\Models\CriteriaValue;
use Illuminate\Support\Facades\Hash;

class PrometheeTestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create test user if not exists
        $user = User::firstOrCreate(
            ['email' => 'bima@gmail.com'],
            [
                'name' => 'bima',
                'password' => Hash::make('jambu123'),
                'email_verified_at' => now(),
            ]
        );

        // 2. Create test cases
        $cases = [
            [
                'name' => 'Product Selection Analysis',
                'description' => 'Comprehensive analysis for selecting the best product among multiple alternatives based on various criteria including price, quality, features, warranty, distance, and reputation.',
                'user_id' => $user->id,
            ],
            [
                'name' => 'Laptop Purchase Decision',
                'description' => 'Decision analysis for purchasing a laptop considering performance, price, brand reputation, and other factors.',
                'user_id' => $user->id,
            ]
        ];

        $createdCases = [];
        foreach ($cases as $caseData) {
            $case = Cases::create($caseData);
            $createdCases[] = $case;
        }

        // 3. Create criteria for first case (Product Selection Analysis)
        $case1 = $createdCases[0];
        $criteriaData = [
            [
                'name' => 'Price',
                'weight' => 0.30,
                'type' => 'cost',
                'description' => 'Product price - lower is better',
                'preference_function' => 'usual', // Type I
                'p' => null,
                'q' => null,
                'case_id' => $case1->id,
            ],
            [
                'name' => 'Quality',
                'weight' => 0.25,
                'type' => 'benefit',
                'description' => 'Product quality rating (scale 1-10) - higher is better',
                'preference_function' => 'quasi', // Type II
                'p' => null,
                'q' => 0.2, // Indifference threshold
                'case_id' => $case1->id,
            ],
            [
                'name' => 'Features',
                'weight' => 0.20,
                'type' => 'benefit',
                'description' => 'Product features completeness (scale 1-10) - higher is better',
                'preference_function' => 'linear', // Type III
                'p' => 0.5, // Preference threshold
                'q' => null,
                'case_id' => $case1->id,
            ],
            [
                'name' => 'Warranty',
                'weight' => 0.15,
                'type' => 'benefit',
                'description' => 'Warranty period (in years) - longer is better',
                'preference_function' => 'level', // Type IV
                'p' => 2, // Preference threshold
                'q' => 1, // Indifference threshold
                'case_id' => $case1->id,
            ],
            [
                'name' => 'Distance',
                'weight' => 0.05,
                'type' => 'cost',
                'description' => 'Distance to seller location (in km) - shorter is better',
                'preference_function' => 'linear_quasi', // Type V
                'p' => 10, // Preference threshold
                'q' => 5, // Indifference threshold
                'case_id' => $case1->id,
            ],
            [
                'name' => 'Reputation',
                'weight' => 0.05,
                'type' => 'benefit',
                'description' => 'Seller reputation (scale 1-5) - higher is better',
                'preference_function' => 'gaussian', // Type VI
                'p' => 0.3, // Standard deviation
                'q' => null,
                'case_id' => $case1->id,
            ]
        ];

        $criteriaIds = [];
        foreach ($criteriaData as $data) {
            $criteria = Criteria::create($data);
            $criteriaIds[$criteria->name] = $criteria->id;
        }

        // 4. Create alternatives for first case
        $alternativesData = [
            [
                'name' => 'Product A - Premium',
                'description' => 'Premium product with high price but excellent quality and features',
                'case_id' => $case1->id,
                'values' => [
                    'Price' => 8000000,
                    'Quality' => 9,
                    'Features' => 7,
                    'Warranty' => 3,
                    'Distance' => 8,
                    'Reputation' => 4.5
                ]
            ],
            [
                'name' => 'Product B - Balanced',
                'description' => 'Mid-range product with balanced price and performance',
                'case_id' => $case1->id,
                'values' => [
                    'Price' => 5000000,
                    'Quality' => 7,
                    'Features' => 6,
                    'Warranty' => 2,
                    'Distance' => 12,
                    'Reputation' => 3.8
                ]
            ],
            [
                'name' => 'Product C - Economic',
                'description' => 'Budget-friendly product with basic features',
                'case_id' => $case1->id,
                'values' => [
                    'Price' => 3000000,
                    'Quality' => 5,
                    'Features' => 4,
                    'Warranty' => 1,
                    'Distance' => 15,
                    'Reputation' => 4.2
                ]
            ],
            [
                'name' => 'Product D - Specialist',
                'description' => 'Specialized product with unique features',
                'case_id' => $case1->id,
                'values' => [
                    'Price' => 6000000,
                    'Quality' => 8,
                    'Features' => 9,
                    'Warranty' => 2,
                    'Distance' => 6,
                    'Reputation' => 4.7
                ]
            ]
        ];

        foreach ($alternativesData as $altData) {
            $alternative = Alternative::create([
                'name' => $altData['name'],
                'description' => $altData['description'],
                'case_id' => $altData['case_id'],
            ]);

            foreach ($altData['values'] as $criteriaName => $value) {
                CriteriaValue::create([
                    'alternative_id' => $alternative->id,
                    'criteria_id' => $criteriaIds[$criteriaName],
                    'value' => $value
                ]);
            }
        }

        // 5. Create criteria and alternatives for second case (Laptop Purchase)
        $case2 = $createdCases[1];
        $laptopCriteria = [
            [
                'name' => 'Price',
                'weight' => 0.25,
                'type' => 'cost',
                'description' => 'Laptop price in IDR',
                'preference_function' => 'usual',
                'p' => null,
                'q' => null,
                'case_id' => $case2->id,
            ],
            [
                'name' => 'Performance',
                'weight' => 0.30,
                'type' => 'benefit',
                'description' => 'Overall performance score (1-10)',
                'preference_function' => 'quasi',
                'p' => null,
                'q' => 0.5,
                'case_id' => $case2->id,
            ],
            [
                'name' => 'Battery Life',
                'weight' => 0.20,
                'type' => 'benefit',
                'description' => 'Battery life in hours',
                'preference_function' => 'linear',
                'p' => 2,
                'q' => null,
                'case_id' => $case2->id,
            ],
            [
                'name' => 'Weight',
                'weight' => 0.15,
                'type' => 'cost',
                'description' => 'Laptop weight in kg',
                'preference_function' => 'level',
                'p' => 0.5,
                'q' => 0.2,
                'case_id' => $case2->id,
            ],
            [
                'name' => 'Brand Rating',
                'weight' => 0.10,
                'type' => 'benefit',
                'description' => 'Brand reputation score (1-5)',
                'preference_function' => 'gaussian',
                'p' => 0.4,
                'q' => null,
                'case_id' => $case2->id,
            ]
        ];

        $laptopCriteriaIds = [];
        foreach ($laptopCriteria as $data) {
            $criteria = Criteria::create($data);
            $laptopCriteriaIds[$criteria->name] = $criteria->id;
        }

        $laptopAlternatives = [
            [
                'name' => 'Laptop Gaming Pro',
                'description' => 'High-performance gaming laptop',
                'case_id' => $case2->id,
                'values' => [
                    'Price' => 25000000,
                    'Performance' => 9,
                    'Battery Life' => 4,
                    'Weight' => 2.8,
                    'Brand Rating' => 4.5
                ]
            ],
            [
                'name' => 'Laptop Business Ultra',
                'description' => 'Lightweight business laptop',
                'case_id' => $case2->id,
                'values' => [
                    'Price' => 18000000,
                    'Performance' => 7,
                    'Battery Life' => 8,
                    'Weight' => 1.2,
                    'Brand Rating' => 4.8
                ]
            ],
            [
                'name' => 'Laptop Budget Smart',
                'description' => 'Affordable laptop for daily use',
                'case_id' => $case2->id,
                'values' => [
                    'Price' => 8000000,
                    'Performance' => 5,
                    'Battery Life' => 6,
                    'Weight' => 2.1,
                    'Brand Rating' => 3.5
                ]
            ]
        ];

        foreach ($laptopAlternatives as $altData) {
            $alternative = Alternative::create([
                'name' => $altData['name'],
                'description' => $altData['description'],
                'case_id' => $altData['case_id'],
            ]);

            foreach ($altData['values'] as $criteriaName => $value) {
                CriteriaValue::create([
                    'alternative_id' => $alternative->id,
                    'criteria_id' => $laptopCriteriaIds[$criteriaName],
                    'value' => $value
                ]);
            }
        }

        $this->command->info('✅ PROMETHEE test data with Cases structure created successfully!');
        $this->command->info('📊 Created:');
        $this->command->info('   - 1 Test user (test@example.com / password)');
        $this->command->info('   - 2 Cases with complete data');
        $this->command->info('   - Case 1: Product Selection (6 criteria, 4 alternatives)');
        $this->command->info('   - Case 2: Laptop Purchase (5 criteria, 3 alternatives)');
        $this->command->info('   - All preference functions (Type I-VI) demonstrated');
    }
}