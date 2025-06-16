<?php

namespace App\Http\Controllers;

use App\Models\Alternative;
use App\Models\Criteria;
use App\Models\Decision;
use App\Models\CriteriaValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\PrometheeService;

class DecisionController extends Controller
{
    protected $prometheeService;

    public function __construct(PrometheeService $prometheeService)
    {
        $this->middleware('auth');
        $this->prometheeService = $prometheeService;
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

        // Perform PROMETHEE calculation using the service
        $result = $this->prometheeService->calculatePromethee($alternatives, $criteria);

        // Store the decision
        $decision = Decision::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'result_data' => [
                'ranking' => $result['ranking'],
                'flows' => $result['flows'],
                'calculation_steps' => [
                    'decision_matrix' => $this->formatDecisionMatrixStep($result['decision_matrix'], $alternatives, $criteria),
                    'normalized_matrix' => $this->formatNormalizedMatrixStep($result['normalized_matrix'], $alternatives, $criteria),
                    'preference_matrices' => $this->formatPreferenceMatricesStep($result['preference_matrices'], $alternatives, $criteria),
                    'global_preference_matrix' => $this->formatGlobalPreferenceMatrixStep($result['global_preference_matrix'], $alternatives),
                    'flows' => $this->formatFlowsStep($result['flows'], $alternatives)
                ]
            ],
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

    /**
     * Helper methods to format the calculation steps for storage
     */
    private function formatDecisionMatrixStep($matrix, $alternatives, $criteria)
    {
        $altNames = $alternatives->pluck('name', 'id')->toArray();
        $criteriaInfo = [];
        
        foreach ($criteria as $c) {
            $criteriaInfo[] = [
                'id' => $c['id'],
                'name' => $c['name'],
                'type' => $c['type'],
                'weight' => $c['weight'],
                'preference_function' => $c['preference_function']
            ];
        }
        
        return [
            'alternatives' => $altNames,
            'criteria' => $criteriaInfo,
            'values' => $matrix
        ];
    }

    private function formatNormalizedMatrixStep($matrix, $alternatives, $criteria)
    {
        $altNames = $alternatives->pluck('name', 'id')->toArray();
        $criteriaInfo = [];
        
        foreach ($criteria as $c) {
            $criteriaInfo[] = [
                'id' => $c['id'],
                'name' => $c['name']
            ];
        }
        
        return [
            'alternatives' => $altNames,
            'criteria' => $criteriaInfo,
            'values' => $matrix
        ];
    }

    private function formatPreferenceMatricesStep($matrices, $alternatives, $criteria)
    {
        $altNames = $alternatives->pluck('name', 'id')->toArray();
        $formattedMatrices = [];
        
        foreach ($matrices as $criteriaId => $matrix) {
            $criterion = collect($criteria)->firstWhere('id', $criteriaId);
            $formattedMatrices[$criteriaId] = [
                'criterion_name' => $criterion['name'],
                'weight' => $criterion['weight'],
                'alternatives' => $altNames,
                'values' => $matrix
            ];
        }
        
        return $formattedMatrices;
    }

    private function formatGlobalPreferenceMatrixStep($matrix, $alternatives)
    {
        $altNames = $alternatives->pluck('name', 'id')->toArray();
        
        return [
            'alternatives' => $altNames,
            'values' => $matrix
        ];
    }

    private function formatFlowsStep($flows, $alternatives)
    {
        $altNames = $alternatives->pluck('name', 'id')->toArray();
        $formattedFlows = [];
        
        foreach ($flows as $altId => $flow) {
            $formattedFlows[$altId] = [
                'name' => $altNames[$altId],
                'positive' => $flow['positive'],
                'negative' => $flow['negative'],
                'net' => $flow['net']
            ];
        }
        
        return $formattedFlows;
    }
}