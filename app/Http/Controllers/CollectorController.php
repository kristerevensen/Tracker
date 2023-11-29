<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Analytics;
use App\Models\Project;

class CollectorController extends Controller
{
    //
    public function index() {
       // return view("");
    }
    public function store(Request $request)
    {
        // Check if the project code exists in the projects table
        $projectCode = $request->input('project_code');
        if (!Project::where('project_code', $projectCode)->exists()) {
            return response()->json(['error' => 'Invalid project code'], 400)
                            ->header('Access-Control-Allow-Origin', '*')
                            ->header('Access-Control-Allow-Methods', 'POST, OPTIONS')
                            ->header('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With');
        }

        try {
            // Store the analytics data
            $analytics = new Analytics;
            $analytics->url = $request->input('url');
            $analytics->title = $request->input('title');
            $analytics->referrer = $request->input('referrer');
            $analytics->device_type = $request->input('device_type');
           //$analytics->timestamp = now(); // Assuming you want to capture the current timestamp
            $analytics->project_code = $projectCode;
            // Assign other fields from the request as needed
            $analytics->save();

            return response()->json(['message' => 'Analytics data stored successfully'], 201)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'POST, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With');
            } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to store analytics data', 'details' => $e->getMessage()], 500)
                        ->header('Access-Control-Allow-Origin', '*')
                        ->header('Access-Control-Allow-Methods', 'POST, OPTIONS')
                        ->header('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With');
            }
    }
}
