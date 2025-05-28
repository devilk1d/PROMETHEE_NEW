<?php

namespace App\Http\Controllers;

use App\Models\Alternative;
use App\Models\Criteria;
use App\Models\Decision;
use App\Models\Cases;
use App\Services\PrometheeService;
use Illuminate\Http\Request;

class DecisionController extends Controller
{
    protected $prometheeService;

    public function __construct(PrometheeService $prometheeService)
    {
        $this->middleware('auth');
        $this->prometheeService = $prometheeService;
    }

    public function index(Cases $case)
    {
        $decisions = Decision::where('case_id', $case->id)
            ->latest()
            ->get();
            
        return view('decisions.index', compact('decisions', 'case'));
    }

    public function calculate(Cases $case)
    {
        $alternatives = Alternative::with('criteriaValues')
            ->where('case_id', $case->id)
            ->get();
            
        $criterias = Criteria::where('case_id', $case->id)->get();

        if ($alternatives->isEmpty() || $criterias->isEmpty()) {
            return redirect()->back()
                ->with('error', 'Please add at least one alternative and one criteria to this case first.');
        }

        return view('decisions.calculate', compact('alternatives', 'criterias', 'case'));
    }

    public function process(Request $request, Cases $case)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:decisions,name,NULL,id,case_id,' . $case->id,
            'description' => 'nullable|string',
            'selected_alternatives' => 'required|array|min:2', // Minimal 2 alternatif untuk perbandingan
            'selected_alternatives.*' => 'exists:alternatives,id'
        ]);

        // Fetch only alternatives from this case
        $selectedAlternativeIds = $validated['selected_alternatives'];
        $alternatives = Alternative::with('criteriaValues')
            ->whereIn('id', $selectedAlternativeIds)
            ->where('case_id', $case->id)
            ->get();

        $criterias = Criteria::where('case_id', $case->id)->get();

        $result = $this->prometheeService->calculatePromethee($alternatives, $criterias);

        $decision = Decision::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'result_data' => $result,
            'selected_alternatives' => $selectedAlternativeIds,
            'case_id' => $case->id // Set the case_id
        ]);

        return redirect()->route('decisions.result', ['case' => $case->id, 'decision' => $decision->id])
            ->with('success', 'PROMETHEE calculation completed successfully.');
    }

    public function result(Cases $case, Decision $decision)
    {
        // Ensure the decision belongs to the case
        if ($decision->case_id !== $case->id) {
            return redirect()->route('decisions.index', $case)
                ->with('error', 'Decision not found in this case.');
        }
        
        if (empty($decision->result_data)) {
            return redirect()->route('decisions.index', $case)
                ->with('error', 'Invalid decision data.');
        }

        // Get names of selected alternatives
        $selectedAlternatives = Alternative::whereIn('id', $decision->selected_alternatives ?? [])
            ->pluck('name', 'id')
            ->toArray();

        return view('decisions.result', compact('decision', 'selectedAlternatives', 'case'));
    }

    public function destroy(Cases $case, Decision $decision)
    {
        // Ensure the decision belongs to the case
        if ($decision->case_id !== $case->id) {
            return redirect()->route('decisions.index', $case)
                ->with('error', 'Decision not found in this case.');
        }
        
        $decision->delete();
        
        return redirect()->route('decisions.index', $case)
            ->with('success', 'Decision deleted successfully.');
    }
}