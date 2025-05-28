<?php

namespace App\Http\Controllers;

use App\Models\Alternative;
use App\Models\Criteria;
use App\Models\CriteriaValue;
use App\Models\Cases;
use Illuminate\Http\Request;

class CriteriaValueController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function edit(Cases $case, Alternative $alternative, Criteria $criteria)
    {
        // Ensure the alternative and criteria belong to the case
        if ($alternative->case_id !== $case->id || $criteria->case_id !== $case->id) {
            return redirect()->route('alternatives.index', $case)
                ->with('error', 'Alternative or criteria not found in this case.');
        }
        
        $value = $alternative->criteriaValues()
            ->where('criteria_id', $criteria->id)
            ->first();

        if (!$value) {
            $value = new CriteriaValue([
                'alternative_id' => $alternative->id,
                'criteria_id' => $criteria->id,
                'value' => 0
            ]);
        }

        return view('criteria_values.form', compact('alternative', 'criteria', 'value', 'case'));
    }

    public function update(Request $request, Cases $case, Alternative $alternative, Criteria $criteria)
    {
        // Ensure the alternative and criteria belong to the case
        if ($alternative->case_id !== $case->id || $criteria->case_id !== $case->id) {
            return redirect()->route('alternatives.index', $case)
                ->with('error', 'Alternative or criteria not found in this case.');
        }
        
        $validated = $request->validate([
            'value' => 'required|numeric'
        ]);

        CriteriaValue::updateOrCreate(
            [
                'alternative_id' => $alternative->id,
                'criteria_id' => $criteria->id
            ],
            ['value' => $validated['value']]
        );

        return redirect()->route('alternatives.index', $case)
            ->with('success', 'Criteria value updated successfully.');
    }
}