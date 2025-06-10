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
        $alternatives = Alternative::where('user_id', Auth::id())->get();
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
        $alternatives = Alternative::with(['criteriaValues.criteria'])
            ->whereIn('id', $validated['selected_alternatives'])
            ->get();

        // Get all criteria with their default weights and thresholds from the database
        $criteria = Criteria::all()->map(function ($c) {
            return [
                'id' => $c->id,
                'name' => $c->name,
                'type' => $c->type,
                'weight' => $c->weight,
                'preference_threshold' => $c->preference_threshold,
                'indifference_threshold' => $c->indifference_threshold,
                'gaussian_threshold' => $c->gaussian_threshold
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

    private function calculatePromethee($alternatives, $criteria)
    {
        // This is a placeholder - implement your actual PROMETHEE calculation logic here
        $result = [
            'ranking' => [],
            'flows' => []
        ];

        // Example: Dummy ranking and flows for demonstration
        if ($alternatives->isNotEmpty()) {
            $ranking = [];
            foreach ($alternatives as $alternative) {
                $netFlow = rand(-100, 100) / 100; // Dummy net flow
                $ranking[$alternative->id] = [
                    'name' => $alternative->name,
                    'net_flow' => $netFlow
                ];
            }
            // Sort by net flow (descending)
            uasort($ranking, function ($a, $b) {
                return $b['net_flow'] <=> $a['net_flow'];
            });

            $flows = [];
            foreach ($alternatives as $alternative) {
                $positiveFlow = rand(0, 100) / 100; // Dummy positive flow
                $negativeFlow = rand(0, 100) / 100; // Dummy negative flow
                $flows[$alternative->id] = [
                    'positive' => $positiveFlow,
                    'negative' => $negativeFlow,
                ];
            }

            $result = [
                'ranking' => $ranking,
                'flows' => $flows
            ];
        }

        return $result;
    }
}