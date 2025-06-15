<?php

namespace App\Services;

use App\Models\Alternative;
use App\Models\Criteria;
use Illuminate\Support\Facades\Log;

class PrometheeService
{
    /**
     * Main method to calculate PROMETHEE ranking
     */
    public function calculatePromethee($alternatives, $criterias)
    {
        try {
            // Validate input data
            $this->validateInputData($alternatives, $criterias);
            
            // 1. Build decision matrix and normalize if needed
            $decisionMatrix = $this->buildDecisionMatrix($alternatives, $criterias);
            
            // 2. Calculate preference matrix for each criterion
            $preferenceMatrices = $this->calculatePreferenceMatrices($decisionMatrix, $alternatives, $criterias);
            
            // 3. Calculate global preference matrix (aggregated with weights)
            $globalPreference = $this->calculateGlobalPreference($preferenceMatrices, $criterias);
            
            // 4. Calculate PROMETHEE flows
            $flows = $this->calculateFlows($globalPreference, $alternatives);
            
            // 5. Generate final ranking
            $ranking = $this->generateRanking($flows, $alternatives);
            
            return [
                'flows' => $flows,
                'ranking' => $ranking,
                'decision_matrix' => $decisionMatrix,
                'metadata' => [
                    'alternatives_count' => count($alternatives),
                    'criteria_count' => count($criterias),
                    'total_weight' => $criterias->sum('weight')
                ]
            ];
            
        } catch (\Exception $e) {
            Log::error('PROMETHEE calculation error: ' . $e->getMessage());
            throw new \Exception('Error in PROMETHEE calculation: ' . $e->getMessage());
        }
    }

    /**
     * Validate input data for PROMETHEE calculation
     */
    private function validateInputData($alternatives, $criterias)
    {
        if (count($alternatives) < 2) {
            throw new \Exception('At least 2 alternatives are required for PROMETHEE analysis');
        }
        
        if (count($criterias) < 1) {
            throw new \Exception('At least 1 criterion is required for PROMETHEE analysis');
        }
        
        $totalWeight = $criterias->sum('weight');
        if ($totalWeight <= 0) {
            throw new \Exception('Total weight of criteria must be greater than 0');
        }
        
        // Check if all alternatives have values for all criteria
        foreach ($alternatives as $alternative) {
            foreach ($criterias as $criteria) {
                $value = $alternative->getCriteriaValue($criteria->id);
                if (!is_numeric($value)) {
                    throw new \Exception("Alternative '{$alternative->name}' missing value for criterion '{$criteria->name}'");
                }
            }
        }
    }

    /**
     * Build decision matrix from alternatives and criteria
     */
    private function buildDecisionMatrix($alternatives, $criterias)
    {
        $matrix = [];
        
        foreach ($alternatives as $alternative) {
            foreach ($criterias as $criteria) {
                $value = $alternative->getCriteriaValue($criteria->id);
                $matrix[$alternative->id][$criteria->id] = (float) $value;
            }
        }
        
        return $matrix;
    }

    /**
     * Calculate preference matrices for each criterion
     */
    private function calculatePreferenceMatrices($decisionMatrix, $alternatives, $criterias)
    {
        $preferenceMatrices = [];

        foreach ($criterias as $criteria) {
            $matrix = [];
            
            foreach ($alternatives as $altA) {
                foreach ($alternatives as $altB) {
                    if ($altA->id === $altB->id) {
                        // Self-comparison is always 0
                        $matrix[$altA->id][$altB->id] = 0;
                        continue;
                    }
                    
                    $valueA = $decisionMatrix[$altA->id][$criteria->id];
                    $valueB = $decisionMatrix[$altB->id][$criteria->id];
                    
                    // Calculate deviation based on criterion type
                    $deviation = $this->calculateDeviation($valueA, $valueB, $criteria->type);
                    
                    // Apply preference function
                    $preference = $this->applyPreferenceFunction(
                        $deviation,
                        $criteria->preference_function,
                        $criteria->p ?? 0,
                        $criteria->q ?? 0
                    );
                    
                    $matrix[$altA->id][$altB->id] = $preference;
                }
            }
            
            $preferenceMatrices[$criteria->id] = $matrix;
        }

        return $preferenceMatrices;
    }

    /**
     * Calculate deviation between two values based on criterion type
     */
    private function calculateDeviation($valueA, $valueB, $criterionType)
    {
        if ($criterionType === 'benefit') {
            // For benefit criteria: higher is better
            return $valueA - $valueB;
        } else {
            // For cost criteria: lower is better
            return $valueB - $valueA;
        }
    }

    /**
     * Apply preference function to calculate preference degree
     */
    private function applyPreferenceFunction($deviation, $functionType, $p = 0, $q = 0)
    {
        // If deviation is negative or zero, no preference
        if ($deviation <= 0) {
            return 0;
        }
        
        switch ($functionType) {
            case Criteria::PREFERENCE_USUAL:
                // Type I: Usual criterion
                return 1;
                
            case Criteria::PREFERENCE_QUASI:
                // Type II: Quasi criterion (U-shape)
                return $deviation > $q ? 1 : 0;
                
            case Criteria::PREFERENCE_LINEAR:
                // Type III: Linear criterion (V-shape)
                if ($p <= 0) return 1; // Avoid division by zero
                return min(1, $deviation / $p);
                
            case Criteria::PREFERENCE_LEVEL:
                // Type IV: Level criterion
                if ($deviation <= $q) return 0;
                if ($deviation > $p) return 1;
                return 0.5;
                
            case Criteria::PREFERENCE_LINEAR_QUASI:
                // Type V: Linear criterion with indifference area
                if ($deviation <= $q) return 0;
                if ($deviation >= $p) return 1;
                if ($p <= $q) return 1; // Avoid division by zero
                return ($deviation - $q) / ($p - $q);
                
            case Criteria::PREFERENCE_GAUSSIAN:
                // Type VI: Gaussian criterion
                if ($p <= 0) return 1; // Avoid division by zero
                return 1 - exp(-($deviation * $deviation) / (2 * $p * $p));
                
            default:
                return 0;
        }
    }

    /**
     * Calculate global preference matrix by aggregating with weights
     */
    private function calculateGlobalPreference($preferenceMatrices, $criterias)
    {
        $globalMatrix = [];
        $totalWeight = $criterias->sum('weight');
        
        // Initialize global matrix
        foreach ($preferenceMatrices as $criteriaId => $matrix) {
            foreach ($matrix as $altA => $preferences) {
                foreach ($preferences as $altB => $preference) {
                    if (!isset($globalMatrix[$altA][$altB])) {
                        $globalMatrix[$altA][$altB] = 0;
                    }
                }
            }
        }
        
        // Aggregate preferences with weights
        foreach ($preferenceMatrices as $criteriaId => $matrix) {
            $criteria = $criterias->firstWhere('id', $criteriaId);
            $normalizedWeight = $criteria->weight / $totalWeight;
            
            foreach ($matrix as $altA => $preferences) {
                foreach ($preferences as $altB => $preference) {
                    $globalMatrix[$altA][$altB] += $normalizedWeight * $preference;
                }
            }
        }

        return $globalMatrix;
    }

    /**
     * Calculate PROMETHEE flows (positive, negative, and net)
     */
    private function calculateFlows($globalMatrix, $alternatives)
    {
        $flows = [];
        $n = count($alternatives);

        foreach ($alternatives as $alternative) {
            $altId = $alternative->id;
            
            // Calculate positive flow (φ+) - outgoing flows
            $positiveFlow = 0;
            if (isset($globalMatrix[$altId])) {
                $positiveFlow = array_sum($globalMatrix[$altId]) / ($n - 1);
            }
            
            // Calculate negative flow (φ-) - incoming flows
            $negativeFlow = 0;
            foreach ($globalMatrix as $fromAltId => $preferences) {
                if (isset($preferences[$altId]) && $fromAltId !== $altId) {
                    $negativeFlow += $preferences[$altId];
                }
            }
            $negativeFlow = $negativeFlow / ($n - 1);
            
            // Calculate net flow (φ)
            $netFlow = $positiveFlow - $negativeFlow;
            
            $flows[$altId] = [
                'positive' => round($positiveFlow, 6),
                'negative' => round($negativeFlow, 6),
                'net' => round($netFlow, 6)
            ];
        }

        return $flows;
    }

    /**
     * Generate final ranking based on PROMETHEE flows
     */
    private function generateRanking($flows, $alternatives)
    {
        // Create ranking data with alternative information
        $rankingData = [];
        
        foreach ($alternatives as $alternative) {
            $altId = $alternative->id;
            $flow = $flows[$altId];
            
            $rankingData[] = [
                'id' => $altId,
                'name' => $alternative->name,
                'positive_flow' => $flow['positive'],
                'negative_flow' => $flow['negative'],
                'net_flow' => $flow['net']
            ];
        }
        
        // Sort by PROMETHEE ranking rules:
        // 1. Net flow (descending)
        // 2. Positive flow (descending) - for tie-breaking
        // 3. Negative flow (ascending) - for tie-breaking
        // 4. Name (ascending) - for final tie-breaking
        usort($rankingData, function($a, $b) {
            // Primary: Net flow (higher is better)
            if (abs($a['net_flow'] - $b['net_flow']) > 1e-6) {
                return $b['net_flow'] <=> $a['net_flow'];
            }
            
            // Secondary: Positive flow (higher is better)
            if (abs($a['positive_flow'] - $b['positive_flow']) > 1e-6) {
                return $b['positive_flow'] <=> $a['positive_flow'];
            }
            
            // Tertiary: Negative flow (lower is better)
            if (abs($a['negative_flow'] - $b['negative_flow']) > 1e-6) {
                return $a['negative_flow'] <=> $b['negative_flow'];
            }
            
            // Final: Name (alphabetical)
            return strcmp($a['name'], $b['name']);
        });
        
        // Convert to final ranking format with ranks
        $ranking = [];
        foreach ($rankingData as $index => $data) {
            $ranking[$data['id']] = [
                'rank' => $index + 1,
                'name' => $data['name'],
                'positive_flow' => $data['positive_flow'],
                'negative_flow' => $data['negative_flow'],
                'net_flow' => $data['net_flow']
            ];
        }
        
        return $ranking;
    }

    /**
     * Get detailed analysis for debugging purposes
     */
    public function getDetailedAnalysis($alternatives, $criterias)
    {
        $decisionMatrix = $this->buildDecisionMatrix($alternatives, $criterias);
        $preferenceMatrices = $this->calculatePreferenceMatrices($decisionMatrix, $alternatives, $criterias);
        $globalPreference = $this->calculateGlobalPreference($preferenceMatrices, $criterias);
        
        return [
            'decision_matrix' => $decisionMatrix,
            'preference_matrices' => $preferenceMatrices,
            'global_preference' => $globalPreference,
            'criteria_info' => $criterias->map(function($c) {
                return [
                    'id' => $c->id,
                    'name' => $c->name,
                    'type' => $c->type,
                    'weight' => $c->weight,
                    'preference_function' => $c->preference_function,
                    'p' => $c->p,
                    'q' => $c->q
                ];
            })
        ];
    }
}