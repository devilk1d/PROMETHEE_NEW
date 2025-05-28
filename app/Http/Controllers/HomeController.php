<?php

namespace App\Http\Controllers;

use App\Models\Cases;
use App\Models\Decision;
use App\Models\Alternative;
use App\Models\Criteria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     */
    public function index()
    {
        try {
            $userId = Auth::id();
            
            // Get cases with counts - filtered by current user
            $cases = Cases::withCount(['criteria', 'alternatives', 'decisions'])
                ->where('user_id', $userId)
                ->latest()
                ->get();
                
            // Get statistics for current user only
            $userCases = Cases::where('user_id', $userId)->pluck('id');
            
            $totalCases = $cases->count();
            $totalAlternatives = Alternative::whereIn('case_id', $userCases)->count();
            $totalDecisions = Decision::whereIn('case_id', $userCases)->count();
            $totalCriteria = Criteria::whereIn('case_id', $userCases)->count();

            // Recent decisions for current user only
            $recentDecisions = Decision::with(['case' => function($query) {
                    $query->select('id', 'name', 'user_id');
                }])
                ->whereHas('case', function($query) use ($userId) {
                    $query->where('user_id', $userId);
                })
                ->latest()
                ->take(5)
                ->get();

            return view('home', compact(
                'cases',
                'totalCases',
                'totalAlternatives',
                'totalDecisions',
                'totalCriteria',
                'recentDecisions'
            ));
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Dashboard error: ' . $e->getMessage());
            
            // Return view with empty data to prevent crash
            return view('home', [
                'cases' => collect(),
                'totalCases' => 0,
                'totalAlternatives' => 0,
                'totalDecisions' => 0,
                'totalCriteria' => 0,
                'recentDecisions' => collect()
            ]);
        }
    }
}