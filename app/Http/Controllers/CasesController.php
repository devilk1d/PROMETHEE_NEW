<?php

namespace App\Http\Controllers;

use App\Models\Cases;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CasesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index()
    {
        // Only show cases belonging to the authenticated user
        $cases = Cases::withCount(['criteria', 'alternatives', 'decisions'])
            ->where('user_id', Auth::id())
            ->latest()
            ->get();
            
        return view('cases.index', compact('cases'));
    }

    public function create()
    {
        $case = new Cases();
        return view('cases.form', compact('case'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                // Unique per user
                'unique:cases,name,NULL,id,user_id,' . Auth::id()
            ],
            'description' => 'nullable|string',
        ]);

        // Add current user ID
        $validated['user_id'] = Auth::id();

        Cases::create($validated);

        return redirect()->route('cases.index')
            ->with('success', 'Case created successfully.');
    }

    public function show(Cases $case)
    {
        // Ensure the case belongs to the authenticated user
        if ($case->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this case.');
        }

        $criteriaCount = $case->criteria()->count();
        $alternativeCount = $case->alternatives()->count();
        $decisionCount = $case->decisions()->count();
        $recentDecisions = $case->decisions()->latest()->take(5)->get();

        return view('cases.show', compact(
            'case',
            'criteriaCount',
            'alternativeCount',
            'decisionCount',
            'recentDecisions'
        ));
    }

    public function edit(Cases $case)
    {
        // Ensure the case belongs to the authenticated user
        if ($case->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this case.');
        }

        return view('cases.form', compact('case'));
    }

    public function update(Request $request, Cases $case)
    {
        // Ensure the case belongs to the authenticated user
        if ($case->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this case.');
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                // Unique per user, excluding current case
                'unique:cases,name,' . $case->id . ',id,user_id,' . Auth::id()
            ],
            'description' => 'nullable|string',
        ]);

        $case->update($validated);

        return redirect()->route('cases.index')
            ->with('success', 'Case updated successfully.');
    }

    public function destroy(Cases $case)
    {
        // Ensure the case belongs to the authenticated user
        if ($case->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this case.');
        }

        $case->delete();
        
        return redirect()->route('cases.index')
            ->with('success', 'Case and all related data deleted successfully.');
    }
}