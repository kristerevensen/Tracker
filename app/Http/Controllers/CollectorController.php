<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Analytics;
use App\Models\DataLinkClicks;
use App\Models\FormSubmission;
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
        $eventType = $request->input('eventType');

        switch ($eventType) {
            case 'pageLoad':
                return $this->handlePageLoad($request);

            case 'linkClick':
                return $this->handleLinkClick($request);

            case 'formSubmit':
                return $this->handleFormSubmit($request);

            default:
                return response()->json(['error' => 'Unknown event type'], 400);
        }
    }


    protected function handlePageLoad(Request $request)
    {
        $projectCode = $request->input('project_code');
        if (!$this->validateProjectCode($projectCode)) {
            return response()->json(['error' => 'Invalid project code'], 400);
        }
        try {
            // Create a new Analytics instance
            $analytics = new Analytics;

            // Assign data from request to the analytics model
            $analytics->url = $request->input('url');
            $analytics->event_type = $request->input('eventType');
            $analytics->url_code = $this->generateUrlCode($request->input('url'));
            $analytics->title = $request->input('title');
            $analytics->referrer = $request->input('referrer');
            $analytics->entrance = $this->determineEntrance($request->input('session_id'));
            $analytics->device_type = $request->input('device_type');
            $analytics->project_code = $request->input('project_code');
            $analytics->session_id = $request->input('session_id');
            $analytics->hostname = $request->input('hostname');
            $analytics->protocol = $request->input('protocol');
            $analytics->pathname = $request->input('pathname');
            $analytics->language = $request->input('language');
            $analytics->cookie_enabled = $request->input('cookie_enabled') ? 1 : 0;
            $analytics->screen_width = $request->input('screen_width');
            $analytics->screen_height = $request->input('screen_height');
            $analytics->history_length = $request->input('history_length');
            $analytics->word_count = $request->input('word_count');
            $analytics->form_count = $request->input('form_count');
            $analytics->meta_description = $request->input('meta_description');
            $analytics->outbound_links = serialize($request->input('outbound_links'));
            $analytics->inbound_links = serialize($request->input('inbound_links'));

            // Save the analytics data
            $analytics->save();

            return response()->json(['message' => 'Analytics data stored successfully'], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to store analytics data', 'details' => $e->getMessage()], 500);
        }
    }

    protected function handleLinkClick(Request $request)
    {
        $projectCode = $request->input('project_code');
        if (!$this->validateProjectCode($projectCode)) {
            return response()->json(['error' => 'Invalid project code'], 400);
        }
        try {
            // Create a new instance for link click data
            $linkClick = new DataLinkClicks;

            // Assign data from the request to the link click model
            $linkClick->session_id = $request->input('session_id');
            $linkClick->event_type = $request->input('eventType');
            $linkClick->project_code = $request->input('project_code');
            $linkClick->link_url = $request->input('linkUrl');
            $linkClick->url_code = $this->generateUrlCode($request->input('linkUrl'));
            $linkClick->link_text = $request->input('linkText');
            $linkClick->click_class = $request->input('clickClass');
            $linkClick->click_id = $request->input('clickId');
            $linkClick->data_attributes = is_array($request->input('dataAttributes')) ? serialize($request->input('dataAttributes')) : null;
            $linkClick->page_url = $request->input('pageUrl');
            $linkClick->click_type = $request->input('clickType');
            $linkClick->coordinates_x = $request->input('coordinates.x');
            $linkClick->coordinates_y = $request->input('coordinates.y');

            // Save the link click data
            $linkClick->save();

            return response()->json(['message' => 'Link click data stored successfully'], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to store link click data', 'details' => $e->getMessage()], 500);
        }
    }

    protected function handleFormSubmit(Request $request)
    {
        $projectCode = $request->input('project_code');
        if (!$this->validateProjectCode($projectCode)) {
            return response()->json(['error' => 'Invalid project code'], 400);
        }

        try {
            // Create a new instance for form submission data
            $formSubmission = new FormSubmission;

            // Assign data from the request to the form submission model
            $formSubmission->session_id = $request->input('session_id');
            $formSubmission->project_code = $projectCode;
            $formSubmission->form_id = $request->input('formDetails.id');
            $formSubmission->form_name = $request->input('formDetails.name');
            $formSubmission->page_url = $request->input('pageUrl');
            $formSubmission->form_data = json_encode($request->input('formDetails.elements'));

            // Save the form submission data
            $formSubmission->save();

            return response()->json(['message' => 'Form submission data stored successfully'], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to store form submission data', 'details' => $e->getMessage()], 500);
        }
    }


    protected function validateProjectCode($projectCode)
    {
        if (!Project::where('project_code', $projectCode)->exists()) {
            return false;
        }
        return true;
    }


    protected function determineEntrance($sessionID)
    {
        // Check if the session ID exists in the database
        return Analytics::where('session_id', $sessionID)->doesntExist() ? 1 : 0;
    }



    }
