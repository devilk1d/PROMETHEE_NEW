<?php

namespace App\Http\Controllers;

use App\Models\Criteria;
use App\Models\Cases;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CriteriaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index(Cases $case)
    {
        // Debug statement to check case ID
        Log::info('Fetching criteria for case ID: ' . $case->id);
        
        $criterias = Criteria::where('case_id', $case->id)
            ->orderBy('weight', 'desc')
            ->get();
        
        // Log the SQL query and result count
        Log::info('Criteria query returned ' . $criterias->count() . ' results');
        
        return view('criteria.index', compact('criterias', 'case'));
    }

    public function create(Cases $case)
    {
        $criterion = new Criteria();
        return view('criteria.form', compact('criterion', 'case'));
    }

    public function store(Request $request, Cases $case)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:criteria,name,NULL,id,case_id,' . $case->id,
            'weight' => 'required|numeric|min:0|max:1',
            'type' => 'required|in:benefit,cost',
            'description' => 'nullable|string',
            'preference_function' => 'required|in:usual,quasi,linear,level,linear_quasi,gaussian',
            'p' => 'nullable|numeric|min:0',
            'q' => 'nullable|numeric|min:0',
        ]);

        // Add case_id to the validated data
        $validated['case_id'] = $case->id;
        
        // Debug log to verify case_id is being set
        Log::info('Creating criteria with case_id: ' . $case->id);
        
        $criteria = Criteria::create($validated);
        
        // Debug log to confirm the created criteria
        Log::info('Created criteria with ID: ' . $criteria->id . ' and case_id: ' . $criteria->case_id);

        return redirect()->route('criteria.index', $case)
            ->with('success', 'Criteria created successfully.');
    }

    public function edit(Cases $case, Criteria $criterion)
    {
        // Ensure the criterion belongs to the case
        if ($criterion->case_id != $case->id) {
            return redirect()->route('criteria.index', $case)
                ->with('error', 'Criteria not found in this case.');
        }
        
        return view('criteria.form', compact('criterion', 'case'));
    }

    public function update(Request $request, Cases $case, Criteria $criterion)
    {
        // Ensure the criterion belongs to the case
        if ($criterion->case_id != $case->id) {
            return redirect()->route('criteria.index', $case)
                ->with('error', 'Criteria not found in this case.');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:criteria,name,' . $criterion->id . ',id,case_id,' . $case->id,
            'weight' => 'required|numeric|min:0|max:1',
            'type' => 'required|in:benefit,cost',
            'description' => 'nullable|string',
            'preference_function' => 'required|in:usual,quasi,linear,level,linear_quasi,gaussian',
            'p' => 'nullable|numeric|min:0',
            'q' => 'nullable|numeric|min:0',
        ]);

        $criterion->update($validated);

        return redirect()->route('criteria.index', $case)
            ->with('success', 'Criteria updated successfully.');
    }

    public function destroy(Cases $case, Criteria $criterion)
    {
        // Ensure the criterion belongs to the case
        if ($criterion->case_id != $case->id) {
            return redirect()->route('criteria.index', $case)
                ->with('error', 'Criteria not found in this case.');
        }
        
        if ($criterion->values()->exists()) {
            return redirect()->back()
                ->with('error', 'Cannot delete criteria because it has associated values.');
        }

        $criterion->delete();
        return redirect()->route('criteria.index', $case)
            ->with('success', 'Criteria deleted successfully.');
    }

    // Add these methods to your existing CriteriaController
public function batch(Cases $case)
{
    $criterias = Criteria::where('case_id', $case->id)
        ->orderBy('weight', 'desc')
        ->get();
    
    return view('criteria.batch', compact('criterias', 'case'));
}

public function batchStore(Request $request, Cases $case)
{
    // Validate the entire request
    $request->validate([
        'criteria' => 'required|array',
        'criteria.*.name' => 'required|string|max:255',
        'criteria.*.weight' => 'required|numeric|min:0|max:1',
        'criteria.*.type' => 'required|in:benefit,cost',
        'criteria.*.preference_function' => 'required|in:usual,quasi,linear,level,linear_quasi,gaussian',
        'criteria.*.p' => 'nullable|numeric|min:0',
        'criteria.*.q' => 'nullable|numeric|min:0',
        'criteria.*.description' => 'nullable|string',
    ]);
    
    // Process criteria
    foreach ($request->criteria as $criteriaData) {
        // If it has an ID, update it
        if (isset($criteriaData['id'])) {
            $criteria = Criteria::find($criteriaData['id']);
            
            // Check if criteria exists and belongs to this case
            if ($criteria && $criteria->case_id == $case->id) {
                $criteria->update($criteriaData);
            }
        } else {
            // Add case_id to the data
            $criteriaData['case_id'] = $case->id;
            Criteria::create($criteriaData);
        }
    }
    
    // Handle deletion
    if ($request->has('delete_criteria')) {
        Criteria::whereIn('id', $request->delete_criteria)
            ->where('case_id', $case->id)
            ->delete();
    }
    
    return redirect()->route('criteria.index', $case)
        ->with('success', 'Criteria updated successfully.');
}
}