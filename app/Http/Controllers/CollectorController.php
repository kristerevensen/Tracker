<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DataPage;
use App\Models\DataLinkClicks;
use App\Models\FormSubmission;
use App\Models\Conversion;
use App\Models\Goal;
use App\Models\Project;
use Illuminate\Support\Str;

class CollectorController extends Controller
{
    public function index()
    {
        // Placeholder for index view or landing page
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

            case 'conversion':
                return $this->handleConversion($request);

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
            $analytics = new DataPage;

            $analytics->url = $request->input('url');
            $analytics->event_type = $request->input('eventType');
            $analytics->content_hash = $request->input('contentHash');
            $analytics->page_content = $request->input('pageContent');
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
            $linkClick = new DataLinkClicks;

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
            $formSubmission = new FormSubmission;

            $formSubmission->session_id = $request->input('session_id');
            $formSubmission->project_code = $projectCode;
            $formSubmission->form_id = $request->input('formDetails.id');
            $formSubmission->form_name = $request->input('formDetails.name');
            $formSubmission->page_url = $request->input('pageUrl');
            //$formSubmission->form_data = json_encode($request->input('formDetails.elements'));

            $formSubmission->save();

            return response()->json(['message' => 'Form submission data stored successfully'], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to store form submission data', 'details' => $e->getMessage()], 500);
        }
    }


    protected function handleConversion(Request $request)
    {

        $goalCode = $request->input('goal_uuid');

        //get the domain from the goal_uuid in goal table, and check if the pageUrl is from the same domain, if not return an error
        $goal = Goal::where('goal_uuid', $goalCode)->first();
        if (!$goal) {
            return response()->json(['error' => 'Invalid goal code'], 400);
        }
        $projectCode = $goal->project_code;

        $project = Project::where('project_code', $projectCode)->first();
        $domain = $project->domain;

        // if the pageUrl is different than the $domain, return an error
        if (strpos($request->input('pageUrl'), $domain) === false) {
            return response()->json(['error' => 'Invalid page URL'], 400);
        }

        try {
            $conversion = new Conversion;
            $conversion->session_id = $request->input('session_id');
            $conversion->goal_uuid = $goalCode;
            $conversion->page_url = $request->input('pageUrl');
            $conversion->referrer = $request->input('referrer');

            $conversion->save();

            return response()->json(['message' => 'Conversion data stored successfully'], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to store conversion data', 'details' => $e->getMessage()], 500);
        }
    }

    protected function validateProjectCode($projectCode)
    {
        return Project::where('project_code', $projectCode)->exists();
    }


    protected function validateGoal($goalCode)
    {
        // check to see if the goal_id exists in the goals table
        return Goal::where('goal_uuid', $goalCode)->exists();
    }

    protected function generateUrlCode($url)
    {
        return md5($url);
    }

    protected function determineEntrance($sessionID)
    {
        return DataPage::where('session_id', $sessionID)->doesntExist() ? 1 : 0;
    }
}
