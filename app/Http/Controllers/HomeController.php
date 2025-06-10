<?php

namespace App\Http\Controllers;

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
            
            // Get statistics
            $totalCriteria = Criteria::count();
            $totalAlternatives = Alternative::where('user_id', $userId)->count();
            $totalDecisions = Decision::where('user_id', $userId)->count();

            // Recent decisions - global for all users
            $recentDecisions = Decision::where('user_id', $userId)
                ->latest()
                ->take(5)
                ->get();

            return view('home', compact(
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
                'totalAlternatives' => 0,
                'totalDecisions' => 0,
                'totalCriteria' => 0,
                'recentDecisions' => collect()
            ]);
        }
    }
}