<?php

namespace App\Services;

use App\Models\Alternative;
use App\Models\Criteria;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class PrometheeService
{
    /**
     * Main method to calculate PROMETHEE ranking
     */
    public function calculatePromethee($alternatives, $criterias)
    {
        try {
            // Convert to collections if not already
            $alternatives = collect($alternatives);
            $criterias = collect($criterias);
            
            // Validate input data
            $this->validateInputData($alternatives, $criterias);
            
            // 1. Build decision matrix
            $decisionMatrix = $this->buildDecisionMatrix($alternatives, $criterias);
            
            // 2. Normalize decision matrix (opsional dalam PROMETHEE)
            $normalizedMatrix = $this->normalizeDecisionMatrix($decisionMatrix, $criterias);
            
            // 3. Calculate preference matrix for each criterion
            $preferenceMatrices = $this->calculatePreferenceMatrices($normalizedMatrix, $alternatives, $criterias);
            
            // 4. Calculate global preference matrix (aggregated with weights)
            $globalPreference = $this->calculateGlobalPreference($preferenceMatrices, $criterias);
            
            // 5. Calculate PROMETHEE flows
            $flows = $this->calculateFlows($globalPreference, $alternatives);
            
            // 6. Generate final ranking
            $ranking = $this->generateRanking($flows, $alternatives);
            
            return [
                'decision_matrix' => $decisionMatrix,
                'normalized_matrix' => $normalizedMatrix,
                'preference_matrices' => $preferenceMatrices,
                'global_preference_matrix' => $globalPreference,
                'flows' => $flows,
                'ranking' => $ranking,
                'metadata' => [
                    'alternatives_count' => $alternatives->count(),
                    'criteria_count' => $criterias->count(),
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
    private function validateInputData(Collection $alternatives, Collection $criterias)
    {
        if ($alternatives->count() < 2) {
            throw new \Exception('At least 2 alternatives are required for PROMETHEE analysis');
        }
        
        if ($criterias->count() < 1) {
            throw new \Exception('At least 1 criterion is required for PROMETHEE analysis');
        }
        
        // Check and normalize weights
        $totalWeight = 0;
        foreach ($criterias as $criteria) {
            $weight = is_array($criteria) ? $criteria['weight'] : $criteria->weight;
            if ($weight < 0) {
                throw new \Exception('All weights must be non-negative');
            }
            $totalWeight += $weight;
        }
        
        if ($totalWeight <= 0) {
            throw new \Exception('Total weight must be greater than 0');
        }
        
        // Normalize weights to sum to 1
        if (abs($totalWeight - 1.0) > 0.0001) {
            foreach ($criterias as $criteria) {
                if (is_array($criteria)) {
                    $criteria['weight'] = $criteria['weight'] / $totalWeight;
                } else {
                    $criteria->weight = $criteria->weight / $totalWeight;
                }
            }
        }
        
        // Validate that all alternatives have values for all criteria
        foreach ($alternatives as $alternative) {
            foreach ($criterias as $criteria) {
                $criteriaId = is_array($criteria) ? $criteria['id'] : $criteria->id;
                $criteriaName = is_array($criteria) ? $criteria['name'] : $criteria->name;
                
                $value = $this->getAlternativeCriteriaValue($alternative, $criteriaId);
                if (!is_numeric($value)) {
                    $altName = is_object($alternative) ? $alternative->name : $alternative['name'];
                    throw new \Exception("Alternative '{$altName}' missing or invalid value for criterion '{$criteriaName}'");
                }
            }
        }
        
        // Validate preference function parameters
        foreach ($criterias as $criteria) {
            $this->validatePreferenceFunctionParams($criteria);
        }
    }

    /**
     * Validate preference function parameters
     */
    private function validatePreferenceFunctionParams($criteria)
    {
        $p = is_array($criteria) ? ($criteria['p'] ?? 0) : ($criteria->p ?? 0);
        $q = is_array($criteria) ? ($criteria['q'] ?? 0) : ($criteria->q ?? 0);
        $functionType = is_array($criteria) ? ($criteria['preference_function'] ?? 'usual') : ($criteria->preference_function ?? 'usual');
        
        // Validate p and q parameters
        if ($p < 0 || $q < 0) {
            throw new \Exception('Parameters p and q must be non-negative');
        }
        
        // For some functions, p should be greater than q
        if (in_array(strtolower($functionType), ['linear_quasi', 'linear-quasi', '5']) && $p <= $q && $p > 0) {
            throw new \Exception('For linear-quasi function, parameter p must be greater than q');
        }
    }

    /**
     * Get criteria value for an alternative
     */
    private function getAlternativeCriteriaValue($alternative, $criteriaId)
    {
        if (is_object($alternative) && method_exists($alternative, 'getCriteriaValue')) {
            return $alternative->getCriteriaValue($criteriaId);
        }
        
        if (is_object($alternative) && isset($alternative->criteriaValues)) {
            $criteriaValue = $alternative->criteriaValues->where('criteria_id', $criteriaId)->first();
            return $criteriaValue ? $criteriaValue->value : null;
        }
        
        if (is_array($alternative) && isset($alternative['criteria_values'][$criteriaId])) {
            return $alternative['criteria_values'][$criteriaId];
        }
        
        return null;
    }

    /**
     * Build decision matrix from alternatives and criteria
     */
    private function buildDecisionMatrix(Collection $alternatives, Collection $criterias)
    {
        $matrix = [];
        
        foreach ($alternatives as $alternative) {
            $altId = is_object($alternative) ? $alternative->id : $alternative['id'];
            
            foreach ($criterias as $criteria) {
                $criteriaId = is_array($criteria) ? $criteria['id'] : $criteria->id;
                $value = $this->getAlternativeCriteriaValue($alternative, $criteriaId);
                $matrix[$altId][$criteriaId] = (float) $value;
            }
        }
        
        return $matrix;
    }

    /**
     * Normalize decision matrix (opsional dalam PROMETHEE)
     */
    private function normalizeDecisionMatrix($decisionMatrix, Collection $criterias)
    {
        $normalizedMatrix = [];
        $minMaxValues = [];
        
        // Calculate min and max for each criterion
        foreach ($criterias as $criteria) {
            $criteriaId = is_array($criteria) ? $criteria['id'] : $criteria->id;
            $values = array_column($decisionMatrix, $criteriaId);
            $minMaxValues[$criteriaId] = [
                'min' => min($values),
                'max' => max($values)
            ];
        }
        
        // Normalize each value
        foreach ($decisionMatrix as $altId => $criteriaValues) {
            foreach ($criteriaValues as $criteriaId => $value) {
                $criteria = $criterias->first(function($c) use ($criteriaId) {
                    $id = is_array($c) ? $c['id'] : $c->id;
                    return $id == $criteriaId;
                });
                
                $criteriaType = is_array($criteria) ? $criteria['type'] : $criteria->type;
                $min = $minMaxValues[$criteriaId]['min'];
                $max = $minMaxValues[$criteriaId]['max'];
                $range = $max - $min;
                
                if ($range == 0) {
                    // Semua nilai sama - tidak ada preferensi
                    $normalizedMatrix[$altId][$criteriaId] = $value; // Gunakan nilai asli
                    continue;
                }
                
                if ($criteriaType === 'benefit' || $criteriaType === 'maximize') {
                    $normalizedMatrix[$altId][$criteriaId] = ($value - $min) / $range;
                } else {
                    $normalizedMatrix[$altId][$criteriaId] = ($max - $value) / $range;
                }
            }
        }
        
        return $normalizedMatrix;
    }

    /**
     * Calculate preference matrices for each criterion
     */
    private function calculatePreferenceMatrices($normalizedMatrix, Collection $alternatives, Collection $criterias)
    {
        $preferenceMatrices = [];

        foreach ($criterias as $criteria) {
            $criteriaId = is_array($criteria) ? $criteria['id'] : $criteria->id;
            $matrix = [];
            
            foreach ($alternatives as $altA) {
                $altAId = is_object($altA) ? $altA->id : $altA['id'];
                
                foreach ($alternatives as $altB) {
                    $altBId = is_object($altB) ? $altB->id : $altB['id'];
                    
                    if ($altAId === $altBId) {
                        $matrix[$altAId][$altBId] = 0;
                        continue;
                    }
                    
                    $valueA = $normalizedMatrix[$altAId][$criteriaId];
                    $valueB = $normalizedMatrix[$altBId][$criteriaId];
                    
                    // Calculate deviation
                    $deviation = $valueA - $valueB;
                    
                    // Apply preference function
                    $preferenceFunction = is_array($criteria) ? ($criteria['preference_function'] ?? 'usual') : ($criteria->preference_function ?? 'usual');
                    $p = is_array($criteria) ? ($criteria['p'] ?? 0) : ($criteria->p ?? 0);
                    $q = is_array($criteria) ? ($criteria['q'] ?? 0) : ($criteria->q ?? 0);
                    
                    $preference = $this->applyPreferenceFunction($deviation, $preferenceFunction, $p, $q);
                    
                    $matrix[$altAId][$altBId] = $preference;
                }
            }
            
            $preferenceMatrices[$criteriaId] = $matrix;
        }

        return $preferenceMatrices;
    }

    /**
     * Apply preference function - DIPERBAIKI
     */
    private function applyPreferenceFunction($deviation, $functionType, $p = 0, $q = 0)
    {
        // Jika tidak ada deviasi positif, tidak ada preferensi
        if ($deviation <= 0) {
            return 0;
        }
        
        switch (strtolower($functionType)) {
            case 'usual':
            case '1':
                return 1;
                
            case 'quasi':
            case 'u-shape':
            case '2':
                return $deviation > $q ? 1 : 0;
                
            case 'linear':
            case 'v-shape':
            case '3':
                if ($p <= 0) return 1;
                return min(1, $deviation / $p);
                
            case 'level':
            case '4':
                if ($deviation <= $q) return 0;
                if ($deviation > $p) return 1;
                return 0.5;
                
            case 'linear_quasi':
            case 'linear-quasi':
            case '5':
                if ($deviation <= $q) return 0;
                if ($deviation >= $p) return 1;
                if ($p <= $q) return 1;
                return ($deviation - $q) / ($p - $q);
                
            case 'gaussian':
            case '6':
                if ($p <= 0) return 1;
                // Perbaikan: gunakan variance yang lebih stabil
                $variance = ($p * $p) / 2;
                return 1 - exp(-($deviation * $deviation) / (2 * $variance));
                
            default:
                return 1;
        }
    }

    /**
     * Calculate global preference matrix
     */
    private function calculateGlobalPreference($preferenceMatrices, Collection $criterias)
    {
        $globalMatrix = [];
        
        if (empty($preferenceMatrices)) {
            throw new \Exception('No preference matrices available for calculation');
        }
        
        $firstMatrix = reset($preferenceMatrices);
        
        // Initialize with zeros
        foreach (array_keys($firstMatrix) as $altA) {
            foreach (array_keys($firstMatrix[$altA]) as $altB) {
                $globalMatrix[$altA][$altB] = 0;
            }
        }
        
        // Aggregate with weights
        foreach ($preferenceMatrices as $criteriaId => $matrix) {
            $criteria = $criterias->first(function($c) use ($criteriaId) {
                $id = is_array($c) ? $c['id'] : $c->id;
                return $id == $criteriaId;
            });
            
            if (!$criteria) continue;
            
            $weight = is_array($criteria) ? $criteria['weight'] : $criteria->weight;
            
            foreach ($matrix as $altA => $preferences) {
                foreach ($preferences as $altB => $preference) {
                    $globalMatrix[$altA][$altB] += $weight * $preference;
                }
            }
        }

        return $globalMatrix;
    }

    /**
     * Calculate PROMETHEE flows
     */
    private function calculateFlows($globalMatrix, Collection $alternatives)
    {
        $flows = [];
        $n = $alternatives->count();

        if ($n <= 1) {
            throw new \Exception('Cannot calculate flows with less than 2 alternatives');
        }

        foreach ($alternatives as $alternative) {
            $altId = is_object($alternative) ? $alternative->id : $alternative['id'];
            
            // Positive flow: rata-rata outgoing preference
            $positiveFlow = 0;
            if (isset($globalMatrix[$altId])) {
                $positiveFlow = array_sum($globalMatrix[$altId]) / ($n - 1);
            }
            
            // Negative flow: rata-rata incoming preference
            $negativeFlow = 0;
            foreach ($globalMatrix as $fromAltId => $preferences) {
                if (isset($preferences[$altId])) {
                    $negativeFlow += $preferences[$altId];
                }
            }
            $negativeFlow = $negativeFlow / ($n - 1);
            
            // Net flow
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
     * Generate final ranking
     */
    private function generateRanking($flows, Collection $alternatives)
    {
        $rankingData = [];
        
        foreach ($alternatives as $alternative) {
            $altId = is_object($alternative) ? $alternative->id : $alternative['id'];
            $altName = is_object($alternative) ? $alternative->name : $alternative['name'];
            
            if (!isset($flows[$altId])) continue;
            
            $flow = $flows[$altId];
            
            $rankingData[] = [
                'id' => $altId,
                'name' => $altName,
                'positive_flow' => $flow['positive'],
                'negative_flow' => $flow['negative'],
                'net_flow' => $flow['net']
            ];
        }
        
        // Sort by net flow desc, then positive flow desc, then negative flow asc
        usort($rankingData, function($a, $b) {
            $netDiff = $b['net_flow'] - $a['net_flow'];
            if (abs($netDiff) > 0.000001) {
                return $netDiff > 0 ? 1 : -1;
            }
            
            $posDiff = $b['positive_flow'] - $a['positive_flow'];
            if (abs($posDiff) > 0.000001) {
                return $posDiff > 0 ? 1 : -1;
            }
            
            $negDiff = $a['negative_flow'] - $b['negative_flow'];
            return $negDiff > 0 ? 1 : ($negDiff < 0 ? -1 : 0);
        });
        
        // Convert to final ranking with ranks
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
}