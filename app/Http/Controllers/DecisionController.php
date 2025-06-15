<?php

namespace App\Http\Controllers;

use App\Models\Alternative;
use App\Models\Criteria;
use App\Models\Decision;
use App\Models\CriteriaValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DecisionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $decisions = Decision::where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('decisions.index', compact('decisions'));
    }

    public function calculate()
    {
        $alternatives = Alternative::with(['criteriaValues.criteria'])
            ->where('user_id', Auth::id())
            ->get();
        $criterias = Criteria::all();

        return view('decisions.calculate', compact('alternatives', 'criterias'));
    }

    public function process(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'selected_alternatives' => 'required|array|min:2',
            'selected_alternatives.*' => 'exists:alternatives,id',
        ]);

        // Verify all alternatives belong to the user
        $userAlternatives = Alternative::whereIn('id', $validated['selected_alternatives'])
            ->where('user_id', Auth::id())
            ->pluck('id')
            ->toArray();

        if (count($userAlternatives) !== count($validated['selected_alternatives'])) {
            return redirect()->back()
                ->with('error', 'Invalid alternative selection.')
                ->withInput();
        }

        // Get selected alternatives with their criteria values
        $alternatives = Alternative::with(['criteriaValues' => function($query) {
            $query->where('is_selected', true)->with('criteria');
        }])->whereIn('id', $validated['selected_alternatives'])
            ->get();

        // Get all criteria with their default weights and thresholds from the database
        // Only include selected criteria based on the alternatives that are actually selected for calculation
        $selectedCriteriaIds = [];
        foreach ($alternatives as $alternative) {
            foreach ($alternative->criteriaValues as $criteriaValue) {
                if ($criteriaValue->is_selected) {
                    $selectedCriteriaIds[] = $criteriaValue->criteria_id;
                }
            }
        }
        $selectedCriteriaIds = array_unique($selectedCriteriaIds);

        $criteria = Criteria::whereIn('id', $selectedCriteriaIds)->get()->map(function ($c) {
            return [
                'id' => $c->id,
                'name' => $c->name,
                'type' => $c->type,
                'weight' => $c->weight,
                'preference_function' => $c->preference_function,
                'p' => $c->p ?? 0,
                'q' => $c->q ?? 0,
                'preference_threshold' => $c->preference_threshold ?? 0,
                'indifference_threshold' => $c->indifference_threshold ?? 0,
                'gaussian_threshold' => $c->gaussian_threshold ?? 0,
            ];
        });

        // Perform PROMETHEE calculation
        $result = $this->calculatePromethee($alternatives, $criteria);

        // Store the decision
        $decision = Decision::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'result_data' => $result,
            'selected_alternatives' => $validated['selected_alternatives'],
            'user_id' => Auth::id()
        ]);

        return redirect()->route('decisions.result', ['decision' => $decision->id])
            ->with('success', 'PROMETHEE calculation completed successfully.');
    }

    public function result(Decision $decision)
    {
        // Check if user owns this decision
        if ($decision->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this decision.');
        }

        if (empty($decision->result_data)) {
            return redirect()->route('decisions.index')
                ->with('error', 'Invalid decision data.');
        }

        $selectedAlternatives = Alternative::whereIn('id', $decision->selected_alternatives ?? [])
            ->pluck('name', 'id')
            ->toArray();

        return view('decisions.result', compact('decision', 'selectedAlternatives'));
    }

    public function destroy(Decision $decision)
    {
        // Check if user owns this decision
        if ($decision->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this decision.');
        }

        $decision->delete();
        
        return redirect()->route('decisions.index')
            ->with('success', 'Decision deleted successfully.');
    }

    private function calculatePromethee($alternatives, $criterias)
    {
        $result = [
            'ranking' => [],
            'flows' => []
        ];

        if ($alternatives->isEmpty() || $criterias->isEmpty()) {
            return $result;
        }

        $alternativeIds = $alternatives->pluck('id')->toArray();
        $numAlternatives = count($alternativeIds);

        // Step 1: Calculate Pairwise Preferences (P(a,b)) for each criterion
        $pairwisePreferences = []; // [alternative_a_id][alternative_b_id][criteria_id] => preference_value

        foreach ($alternatives as $alternativeA) {
            foreach ($alternatives as $alternativeB) {
                if ($alternativeA->id === $alternativeB->id) continue;

                foreach ($criterias as $criteria) {
                    // Ensure criteria is selected for both alternatives, otherwise skip or assign 0 preference
                    $valueA = $alternativeA->getCriteriaValue($criteria['id']);
                    $valueB = $alternativeB->getCriteriaValue($criteria['id']);

                    // If either criterion is not selected for an alternative, treat value as 0 for comparison
                    // Or, more accurately for PROMETHEE, if a criterion is not selected, it shouldn't influence
                    // the preference for that pair on that criterion.
                    // For now, let's assume getCriteriaValue already handles non-selected by returning 0
                    // and we calculate preference only if both are 'active' for this criterion.
                    
                    // A more robust check might be needed here to explicitly handle is_selected
                    // For simplicity now, let's proceed with values, and ensure getCriteriaValue returns 0 for unselected.

                    $diff = $valueA - $valueB;
                    $preference = $this->getPreference($criteria, $diff);
                    $pairwisePreferences[$alternativeA->id][$alternativeB->id][$criteria['id']] = $preference;
                }
            }
        }

        // Step 2: Calculate Multicriteria Preference Index (PI(a,b))
        $preferenceIndices = []; // [alternative_a_id][alternative_b_id] => preference_index
        $totalWeight = $criterias->sum('weight');
        if ($totalWeight == 0) {
            return $result; // Avoid division by zero if no criteria or weights are zero
        }

        foreach ($alternatives as $alternativeA) {
            foreach ($alternatives as $alternativeB) {
                if ($alternativeA->id === $alternativeB->id) continue;

                $sumWeightedPreferences = 0;
                foreach ($criterias as $criteria) {
                    $pref = $pairwisePreferences[$alternativeA->id][$alternativeB->id][$criteria['id']] ?? 0;
                    $sumWeightedPreferences += ($pref * $criteria['weight']);
                }
                $preferenceIndices[$alternativeA->id][$alternativeB->id] = $sumWeightedPreferences / $totalWeight;
            }
        }

        // Step 3: Calculate Positive and Negative Outranking Flows
        $positiveFlows = []; // alternative_id => phi_plus
        $negativeFlows = []; // alternative_id => phi_minus

        foreach ($alternatives as $alternative) {
            $sumPiPlus = 0;
            $sumPiMinus = 0;
            foreach ($alternativeIds as $otherAlternativeId) {
                if ($alternative->id === $otherAlternativeId) continue;

                // Positive flow (phi_plus(a)) = Sum(PI(a,x)) / (n-1)
                $sumPiPlus += ($preferenceIndices[$alternative->id][$otherAlternativeId] ?? 0);

                // Negative flow (phi_minus(a)) = Sum(PI(x,a)) / (n-1)
                $sumPiMinus += ($preferenceIndices[$otherAlternativeId][$alternative->id] ?? 0);
            }

            $denominator = $numAlternatives - 1;
            if ($denominator == 0) $denominator = 1; // Avoid division by zero for single alternative case

            $positiveFlows[$alternative->id] = $sumPiPlus / $denominator;
            $negativeFlows[$alternative->id] = $sumPiMinus / $denominator;
        }

        // Step 4: Calculate Net Flow and Ranking
        $ranking = [];
        $flows = [];

        foreach ($alternatives as $alternative) {
            $phiPlus = $positiveFlows[$alternative->id] ?? 0;
            $phiMinus = $negativeFlows[$alternative->id] ?? 0;
            $netFlow = $phiPlus - $phiMinus;

            $ranking[$alternative->id] = [
                'id' => $alternative->id,
                'name' => $alternative->name,
                'net_flow' => $netFlow,
                'positive_flow' => $phiPlus,
                'negative_flow' => $phiMinus,
            ];

            $flows[$alternative->id] = [
                'positive' => $phiPlus,
                'negative' => $phiMinus,
            ];
        }

        // Sort ranking by net flow (descending)
        uasort($ranking, function ($a, $b) {
            return $b['net_flow'] <=> $a['net_flow'];
        });

        $result = [
            'ranking' => array_values($ranking), // Reindex array numerically
            'flows' => $flows
        ];

        return $result;
    }

    private function getPreference($criteria, $diff)
    {
        $p = $criteria['p'] ?? 0;
        $q = $criteria['q'] ?? 0;
        $type = $criteria['type'];

        $relevantDiff = $diff;

        // For cost criteria, a negative difference (A < B) means A is preferred, so invert the diff
        if ($type === 'cost') {
            $relevantDiff = -$diff;
        }

        // If the relevant difference is not positive, there is no preference of A over B
        if ($relevantDiff <= 0) {
            return 0;
        }

        switch ($criteria['preference_function']) {
            case 'usual':
                return 1;

            case 'quasi':
                return ($relevantDiff > $q) ? 1 : 0;

            case 'linear':
                if ($relevantDiff <= $q) return 0;
                if ($relevantDiff >= $p) return 1;
                // Avoid division by zero if p == q
                if (($p - $q) == 0) return ($relevantDiff > $q) ? 1 : 0;
                return ($relevantDiff - $q) / ($p - $q);

            case 'level':
                if ($relevantDiff <= $q) return 0;
                if ($relevantDiff > $q && $relevantDiff <= $p) return 0.5;
                return 1;

            case 'linear_quasi':
                if ($relevantDiff <= $q) return 0;
                if ($relevantDiff >= $p) return 1;
                // Avoid division by zero if p == q
                if (($p - $q) == 0) return ($relevantDiff > $q) ? 1 : 0;
                return ($relevantDiff - $q) / ($p - $q);

            case 'gaussian':
                // Handle case where p is 0, behaves like usual criterion for non-zero diff
                if ($p == 0) return ($relevantDiff > 0) ? 1 : 0;
                
                $s = $p / 2; // Using p/2 as a proxy for standard deviation
                if ($s == 0) return ($relevantDiff > 0) ? 1 : 0; // Avoid division by zero if s becomes 0
                
                return 1 - exp(-pow($relevantDiff, 2) / (2 * pow($s, 2)));

            default:
                return 0;
        }
    }
}