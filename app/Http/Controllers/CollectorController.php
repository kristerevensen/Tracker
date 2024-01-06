<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Analytics;
use App\Models\DataLinkClicks;
use App\Models\LinkClicks;
use App\Models\Project;
use Illuminate\Support\Str;

class CollectorController extends Controller
{
    //
    public function index()
    {
        // return view("");
    }
    public function generateUniqueId($length = 10)
    {
        return str::random($length);
    }
    function generateUrlCode($url) {
        return md5($url);
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

         //Check if the session ID has been set before
         $sessionID = $request->input('session_id');
         $entrance = 1;
         $session_start = 1;
         if (Analytics::where('session_id',$sessionID)->exists()) {
             $entrance = 0;
             $session_start = 0;
         }



        $eventType = $request->input('eventType');

        if($eventType == 'pageLoad') {

            try {
                // Store the analytics data
                $analytics = new Analytics;
                $analytics->url = $request->input('url');
                $analytics->url_code = $this->generateUrlCode($analytics->url);
                $analytics->title = $request->input('title');
                $analytics->referrer = $request->input('referrer');
                $analytics->entrance = $entrance;
                $analytics->session_start = $session_start;
                $analytics->device_type = $request->input('device_type');
                $analytics->project_code = $request->input('project_code');
                $analytics->session_id = $request->input('session_id');
                $analytics->hostname = $request->input('hostname');
                $analytics->protocol = $request->input('protocol');
                $analytics->pathname = $request->input('pathname');
                $analytics->language = $request->input('language');
                $analytics->cookie_enabled = $request->input('cookie_enabled');
                $analytics->screen_width = $request->input('screen_width');
                $analytics->screen_height = $request->input('screen_height');
                $analytics->history_length = $request->input('history_length');
                $analytics->word_count = $request->input('word_count');
                $analytics->form_count = $request->input('form_count');
                $analytics->meta_description = $request->input('meta_description');
                $analytics->outbound_links = serialize(json_encode($request->input('outbound_links')));
                $analytics->inbound_links = serialize(json_encode($request->input('inbound_links')));
                $analytics->save();

                return response()->json(['message' => 'Analytics data stored successfully'], 201);
            } catch (\Exception $e) {
                return response()->json(['error' => 'Failed to store analytics data', 'details' => $e->getMessage()], 500);
            }

        } else if($eventType == 'linkClick') {
            try {
                $linkClick = new DataLinkClicks;
                $linkClick->session_id = $request->input('session_id');
                $linkClick->project_code = $request->input('project_code');
                $linkClick->link_url = $request->input('linkUrl');
                $linkClick->url_code = $this->generateUrlCode($linkClick->page_url);
                $linkClick->link_text = $request->input('linkText');
                $linkClick->click_class = $request->input('clickClass');
                $linkClick->click_id = $request->input('clickId');
                $linkClick->data_attributes = serialize(json_encode($request->input('dataAttributes')));
                $linkClick->page_url = $request->input('pageUrl');
                $linkClick->click_type = $request->input('clickType');
                $linkClick->coordinates_x = $request->input('coordinates.x');
                $linkClick->coordinates_y = $request->input('coordinates.y');

                $linkClick->save();

                return response()->json(['message' => 'Link click data stored successfully']. 201);
            } catch (\Exception $e) {
                return response()->json(['error' => 'Failed to store link click', 'details' => $e->getMessage()], 500);
            }

        } else if($eventType == 'formSubmit') {

        }





    }
}
