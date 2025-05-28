<?php

namespace App\Http\Controllers;

use App\Models\Alternative;
use App\Models\Cases;
use App\Models\Criteria;
use App\Models\CriteriaValue;
use Illuminate\Http\Request;

class AlternativeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index(Cases $case)
    {
        $alternatives = Alternative::with(['criteriaValues.criteria'])
            ->where('case_id', $case->id)
            ->get();
            
        return view('alternatives.index', compact('alternatives', 'case'));
    }

    public function create(Cases $case)
    {
        $alternative = new Alternative();
        $criterias = Criteria::where('case_id', $case->id)->get();
        
        return view('alternatives.form', compact('alternative', 'criterias', 'case'));
    }

    public function store(Request $request, Cases $case)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:alternatives,name,NULL,id,case_id,' . $case->id,
            'description' => 'nullable|string',
            'criteria_values' => 'required|array',
            'criteria_values.*' => 'required|numeric'
        ]);

        // Add case_id to the alternative
        $alternative = Alternative::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'case_id' => $case->id
        ]);

        // Ensure we only use criteria from this case
        $caseCriteriaIds = Criteria::where('case_id', $case->id)
            ->pluck('id')
            ->toArray();
            
        foreach ($validated['criteria_values'] as $criteriaId => $value) {
            // Skip criteria that don't belong to this case
            if (!in_array($criteriaId, $caseCriteriaIds)) {
                continue;
            }
            
            CriteriaValue::create([
                'alternative_id' => $alternative->id,
                'criteria_id' => $criteriaId,
                'value' => $value
            ]);
        }

        return redirect()->route('alternatives.index', $case)
            ->with('success', 'Alternative created successfully.');
    }

    public function edit(Cases $case, Alternative $alternative)
    {
        // Ensure the alternative belongs to the case
        if ($alternative->case_id != $case->id) {
            return redirect()->route('alternatives.index', $case)
                ->with('error', 'Alternative not found in this case.');
        }
        
        $alternative->load('criteriaValues');
        $criterias = Criteria::where('case_id', $case->id)->get();
        
        return view('alternatives.form', compact('alternative', 'criterias', 'case'));
    }

    public function update(Request $request, Cases $case, Alternative $alternative)
    {
        // Ensure the alternative belongs to the case
        if ($alternative->case_id != $case->id) {
            return redirect()->route('alternatives.index', $case)
                ->with('error', 'Alternative not found in this case.');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:alternatives,name,'.$alternative->id.',id,case_id,' . $case->id,
            'description' => 'nullable|string',
            'criteria_values' => 'required|array',
            'criteria_values.*' => 'required|numeric'
        ]);

        $alternative->update([
            'name' => $validated['name'],
            'description' => $validated['description']
        ]);

        // Ensure we only use criteria from this case
        $caseCriteriaIds = Criteria::where('case_id', $case->id)
            ->pluck('id')
            ->toArray();
            
        foreach ($validated['criteria_values'] as $criteriaId => $value) {
            // Skip criteria that don't belong to this case
            if (!in_array($criteriaId, $caseCriteriaIds)) {
                continue;
            }
            
            CriteriaValue::updateOrCreate(
                [
                    'alternative_id' => $alternative->id,
                    'criteria_id' => $criteriaId
                ],
                ['value' => $value]
            );
        }

        return redirect()->route('alternatives.index', $case)
            ->with('success', 'Alternative updated successfully.');
    }

    public function destroy(Cases $case, Alternative $alternative)
    {
        // Ensure the alternative belongs to the case
        if ($alternative->case_id != $case->id) {
            return redirect()->route('alternatives.index', $case)
                ->with('error', 'Alternative not found in this case.');
        }
        
        $alternative->criteriaValues()->delete();
        $alternative->delete();
        
        return redirect()->route('alternatives.index', $case)
            ->with('success', 'Alternative deleted successfully.');
    }

    // Add these methods to your existing AlternativeController
    public function batch(Cases $case)
    {
        $alternatives = Alternative::with(['criteriaValues.criteria'])
            ->where('case_id', $case->id)
            ->get();
            
        $criterias = Criteria::where('case_id', $case->id)->get();
        
        return view('alternatives.batch', compact('alternatives', 'criterias', 'case'));
    }

    public function batchStore(Request $request, Cases $case)
    {
        // Validate the entire request
        $request->validate([
            'alternatives' => 'required|array',
            'alternatives.*.name' => 'required|string|max:255',
            'alternatives.*.description' => 'nullable|string',
        ]);
        
        // Process alternatives
        foreach ($request->alternatives as $alternativeData) {
            // Extract criteria values and selected criteria
            $criteriaValues = $alternativeData['criteria_values'] ?? [];
            $selectedCriteria = $alternativeData['selected_criteria'] ?? [];
            
            // Remove these from the data for the alternative model
            unset($alternativeData['criteria_values']);
            unset($alternativeData['selected_criteria']);
            
            // If it has an ID, update it
            if (isset($alternativeData['id'])) {
                $alternative = Alternative::find($alternativeData['id']);
                
                // Check if alternative exists and belongs to this case
                if ($alternative && $alternative->case_id == $case->id) {
                    $alternative->update($alternativeData);
                    $this->updateCriteriaValues($alternative, $criteriaValues, $selectedCriteria, $case);
                }
            } else {
                // Add case_id to the data
                $alternativeData['case_id'] = $case->id;
                $alternative = Alternative::create($alternativeData);
                $this->updateCriteriaValues($alternative, $criteriaValues, $selectedCriteria, $case);
            }
        }
        
        // Handle deletion
        if ($request->has('delete_alternatives')) {
            Alternative::whereIn('id', $request->delete_alternatives)
                ->where('case_id', $case->id)
                ->delete();
        }
        
        return redirect()->route('alternatives.index', $case)
            ->with('success', 'Alternatives updated successfully.');
    }

    private function updateCriteriaValues($alternative, $criteriaValues, $selectedCriteria, $case)
    {
        // Get valid criteria IDs for this case
        $validCriteriaIds = Criteria::where('case_id', $case->id)
            ->pluck('id')
            ->toArray();
        
        // Delete values for criteria not in the current selection
        if (!empty($criteriaValues)) {
            $alternative->criteriaValues()
                ->whereNotIn('criteria_id', array_keys($criteriaValues))
                ->delete();
        }
        
        // Update or create criteria values for all criteria
        foreach ($criteriaValues as $criteriaId => $value) {
            // Skip criteria that don't belong to this case
            if (!in_array($criteriaId, $validCriteriaIds)) {
                continue;
            }
            
            // Only create or update if there's a meaningful value
            if ($value !== null && $value !== '') {
                CriteriaValue::updateOrCreate(
                    [
                        'alternative_id' => $alternative->id,
                        'criteria_id' => $criteriaId
                    ],
                    ['value' => $value]
                );
            } else {
                // If value is empty, delete that criteria value
                $alternative->criteriaValues()
                    ->where('criteria_id', $criteriaId)
                    ->delete();
            }
        }
    }
}