<?php

namespace App\Http\Controllers;

use App\Models\Alternative;
use App\Models\Criteria;
use App\Models\CriteriaValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AlternativeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index()
    {
        $alternatives = Alternative::with(['criteriaValues.criteria'])
            ->where('user_id', Auth::id())
            ->get();
            
        return view('alternatives.index', compact('alternatives'));
    }

    public function create()
    {
        $alternative = new Alternative();
        $criterias = Criteria::all();
        
        return view('alternatives.form', compact('alternative', 'criterias'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:alternatives,name',
            'description' => 'nullable|string',
            'criteria_values' => 'required|array',
            'criteria_values.*' => 'required|numeric',
            'selected_criteria' => 'nullable|array',
            'selected_criteria.*' => 'boolean',
        ]);

        $alternative = Alternative::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'user_id' => Auth::id()
        ]);

        foreach ($validated['criteria_values'] as $criteriaId => $value) {
            $isSelected = isset($validated['selected_criteria'][$criteriaId]) ? (bool)$validated['selected_criteria'][$criteriaId] : false;
            $alternative->setCriteriaValue($criteriaId, $value, $isSelected);
        }

        return redirect()->route('alternatives.index')
            ->with('success', 'Alternative created successfully.');
    }

    public function edit(Alternative $alternative)
    {
        // Check if user owns this alternative
        if ($alternative->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this alternative.');
        }

        $alternative->load('criteriaValues');
        $criterias = Criteria::all();
        
        return view('alternatives.form', compact('alternative', 'criterias'));
    }

    public function update(Request $request, Alternative $alternative)
    {
        // Check if user owns this alternative
        if ($alternative->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this alternative.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:alternatives,name,'.$alternative->id,
            'description' => 'nullable|string',
            'criteria_values' => 'required|array',
            'criteria_values.*' => 'required|numeric',
            'selected_criteria' => 'nullable|array',
            'selected_criteria.*' => 'boolean',
        ]);

        $alternative->update([
            'name' => $validated['name'],
            'description' => $validated['description']
        ]);

        foreach ($validated['criteria_values'] as $criteriaId => $value) {
            $isSelected = isset($validated['selected_criteria'][$criteriaId]) ? (bool)$validated['selected_criteria'][$criteriaId] : false;
            $alternative->setCriteriaValue($criteriaId, $value, $isSelected);
        }

        return redirect()->route('alternatives.index')
            ->with('success', 'Alternative updated successfully.');
    }

    public function destroy(Alternative $alternative)
    {
        // Check if user owns this alternative
        if ($alternative->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this alternative.');
        }

        $alternative->criteriaValues()->delete();
        $alternative->delete();
        
        return redirect()->route('alternatives.index')
            ->with('success', 'Alternative deleted successfully.');
    }

    public function batch()
    {
        $alternatives = Alternative::with(['criteriaValues.criteria'])
            ->where('user_id', Auth::id())
            ->get();
        $criterias = Criteria::all();
        
        return view('alternatives.batch', compact('alternatives', 'criterias'));
    }

    public function batchStore(Request $request)
    {
        $request->validate([
            'alternatives' => 'required|array',
            'alternatives.*.name' => 'required|string|max:255',
            'alternatives.*.description' => 'nullable|string',
            'alternatives.*.criteria_values' => 'nullable|array',
            'alternatives.*.criteria_values.*' => 'nullable|numeric',
            'alternatives.*.selected_criteria' => 'nullable|array',
            'alternatives.*.selected_criteria.*' => 'boolean',
        ]);
        
        foreach ($request->alternatives as $alternativeData) {
            $criteriaValues = $alternativeData['criteria_values'] ?? [];
            $selectedCriteria = $alternativeData['selected_criteria'] ?? [];
            
            if (isset($alternativeData['id'])) {
                $alternative = Alternative::find($alternativeData['id']);
                
                if ($alternative && $alternative->user_id === Auth::id()) {
                    // Update basic alternative data
                    $alternative->name = $alternativeData['name'];
                    $alternative->description = $alternativeData['description'];
                    $alternative->save();

                    // Sync criteria values and selected status
                    $this->syncCriteriaValues($alternative, $criteriaValues, $selectedCriteria);
                }
            } else {
                // Create new alternative
                $alternative = Alternative::create([
                    'name' => $alternativeData['name'],
                    'description' => $alternativeData['description'],
                    'user_id' => Auth::id()
                ]);
                
                // Sync criteria values and selected status for new alternative
                $this->syncCriteriaValues($alternative, $criteriaValues, $selectedCriteria);
            }
        }
        
        if ($request->has('delete_alternatives')) {
            Alternative::whereIn('id', $request->delete_alternatives)
                ->where('user_id', Auth::id())
                ->delete();
        }
        
        return redirect()->route('alternatives.index')
            ->with('success', 'Alternatives updated successfully.');
    }

    private function syncCriteriaValues($alternative, $criteriaValues, $selectedCriteria)
    {
        $allCriteriaIds = Criteria::pluck('id')->toArray();
        
        // Get existing criteria values for this alternative
        $existingCriteriaValues = $alternative->criteriaValues->keyBy('criteria_id');

        foreach ($allCriteriaIds as $criteriaId) {
            $value = $criteriaValues[$criteriaId] ?? 0;
            $isSelected = isset($selectedCriteria[$criteriaId]) && (bool)$selectedCriteria[$criteriaId];

            if ($existingCriteriaValues->has($criteriaId)) {
                // Update existing criteria value
                $criteriaValue = $existingCriteriaValues->get($criteriaId);
                $criteriaValue->update([
                    'value' => $value,
                    'is_selected' => $isSelected
                ]);
            } else {
                // Create new criteria value
                CriteriaValue::create([
                    'alternative_id' => $alternative->id,
                    'criteria_id' => $criteriaId,
                    'value' => $value,
                    'is_selected' => $isSelected
                ]);
            }
        }
    }
}