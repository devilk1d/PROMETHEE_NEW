<?php

namespace App\Http\Controllers;

use App\Models\Criteria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class CriteriaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // Admin can manage, User can view
        $this->middleware('role:admin')->except(['index', 'getCriteriaForAlternative']);
    }
    
    public function index()
    {
        Log::info('Criteria Index Access (No Cases):', [
            'user_id' => Auth::id(),
            'user_role' => Auth::user()->role,
            'user_name' => Auth::user()->name
        ]);
        
        // Get ALL criteria in the system
        $criterias = Criteria::orderBy('weight', 'desc')->get();
        
        Log::info('Criteria found:', [
            'count' => $criterias->count()
        ]);
        
        $userRole = Auth::user()->role;
        
        return view('criteria.index', compact('criterias', 'userRole'));
    }

    public function create()
    {
        $criterion = new Criteria();
        return view('criteria.form', compact('criterion'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:criteria,name',
            'weight' => 'required|numeric|min:0|max:1',
            'type' => 'required|in:benefit,cost',
            'description' => 'nullable|string',
            'preference_function' => 'required|in:usual,quasi,linear,level,linear_quasi,gaussian',
            'p' => 'nullable|numeric|min:0',
            'q' => 'nullable|numeric|min:0',
        ]);

        // Remove case_id requirement
        Log::info('Creating criteria (no cases):', $validated);
        
        $criteria = Criteria::create($validated);
        
        Log::info('Criteria created with ID: ' . $criteria->id);

        return redirect()->route('criteria.index')
            ->with('success', 'Criteria created successfully.');
    }

    public function edit(Criteria $criterion)
    {
        return view('criteria.form', compact('criterion'));
    }

    public function update(Request $request, Criteria $criterion)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:criteria,name,' . $criterion->id,
            'weight' => 'required|numeric|min:0|max:1',
            'type' => 'required|in:benefit,cost',
            'description' => 'nullable|string',
            'preference_function' => 'required|in:usual,quasi,linear,level,linear_quasi,gaussian',
            'p' => 'nullable|numeric|min:0',
            'q' => 'nullable|numeric|min:0',
        ]);

        $criterion->update($validated);

        return redirect()->route('criteria.index')
            ->with('success', 'Criteria updated successfully.');
    }

    public function destroy(Criteria $criterion)
    {
        if ($criterion->values()->exists()) {
            return redirect()->back()
                ->with('error', 'Cannot delete criteria because it has associated values.');
        }

        $criterion->delete();
        return redirect()->route('criteria.index')
            ->with('success', 'Criteria deleted successfully.');
    }

    public function batch()
    {
        $criterias = Criteria::orderBy('weight', 'desc')->get();
        
        return view('criteria.batch', compact('criterias'));
    }

    public function batchStore(Request $request)
    {
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
        
        foreach ($request->criteria as $criteriaData) {
            if (isset($criteriaData['id'])) {
                $criteria = Criteria::find($criteriaData['id']);
                
                if ($criteria) {
                    $criteria->update($criteriaData);
                }
            } else {
                Criteria::create($criteriaData);
            }
        }
        
        if ($request->has('delete_criteria')) {
            Criteria::whereIn('id', $request->delete_criteria)->delete();
        }
        
        return redirect()->route('criteria.index')
            ->with('success', 'Criteria updated successfully.');
    }

    public function getCriteriaForAlternative()
    {
        $criterias = Criteria::select('id', 'name', 'type', 'weight', 'description')
            ->orderBy('weight', 'desc')
            ->get();
        
        return response()->json($criterias);
    }
}