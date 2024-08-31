<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DataPage;
use App\Models\DataLinkClicks;
use App\Models\FormSubmission;
use Illuminate\Support\Str;

class CollectorController extends Controller
{
    public function index(Request $request)
    {
        $eventType = $request->query('eventType');

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
        $projectCode = $request->query('project_code');
        if (!$this->validateProjectCode($projectCode)) {
            return response()->json(['error' => 'Invalid project code'], 400);
        }

        try {
            $analytics = new DataPage;

            $analytics->url = $request->query('url');
            $analytics->event_type = $request->query('eventType');
            $analytics->content_hash = $request->query('contentHash');
            $analytics->page_content = $request->query('pageContent');
            $analytics->url_code = $this->generateUrlCode($request->query('url'));
            $analytics->title = $request->query('title');
            $analytics->referrer = $request->query('referrer');
            $analytics->device_type = $request->query('device_type');
            $analytics->project_code = $request->query('project_code');
            $analytics->session_id = $request->query('session_id');
            $analytics->hostname = $request->query('hostname');
            $analytics->protocol = $request->query('protocol');
            $analytics->pathname = $request->query('pathname');
            $analytics->language = $request->query('language');
            $analytics->cookie_enabled = $request->query('cookie_enabled') ? 1 : 0;
            $analytics->screen_width = $request->query('screen_width');
            $analytics->screen_height = $request->query('screen_height');
            $analytics->history_length = $request->query('history_length');
            $analytics->word_count = $request->query('word_count');
            $analytics->form_count = $request->query('form_count');
            $analytics->meta_description = $request->query('meta_description');
            $analytics->outbound_links = serialize(explode(',', $request->query('outbound_links')));
            $analytics->inbound_links = serialize(explode(',', $request->query('inbound_links')));

            $analytics->save();

            return response()->json(['message' => 'Analytics data stored successfully'], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to store analytics data', 'details' => $e->getMessage()], 500);
        }
    }

    protected function handleLinkClick(Request $request)
    {
        $projectCode = $request->query('project_code');
        if (!$this->validateProjectCode($projectCode)) {
            return response()->json(['error' => 'Invalid project code'], 400);
        }

        try {
            $linkClick = new DataLinkClicks;

            $linkClick->session_id = $request->query('session_id');
            $linkClick->event_type = $request->query('eventType');
            $linkClick->project_code = $projectCode;
            $linkClick->link_url = $request->query('linkUrl');
            $linkClick->url_code = $this->generateUrlCode($request->query('linkUrl'));
            $linkClick->link_text = $request->query('linkText');
            $linkClick->click_class = $request->query('clickClass');
            $linkClick->click_id = $request->query('clickId');
            $linkClick->data_attributes = is_array($request->query('dataAttributes')) ? serialize($request->query('dataAttributes')) : null;
            $linkClick->page_url = $request->query('pageUrl');
            $linkClick->click_type = $request->query('clickType');
            $linkClick->coordinates_x = $request->query('coordinates.x');
            $linkClick->coordinates_y = $request->query('coordinates.y');

            $linkClick->save();

            return response()->json(['message' => 'Link click data stored successfully'], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to store link click data', 'details' => $e->getMessage()], 500);
        }
    }

    protected function handleFormSubmit(Request $request)
    {
        $projectCode = $request->query('project_code');
        if (!$this->validateProjectCode($projectCode)) {
            return response()->json(['error' => 'Invalid project code'], 400);
        }

        try {
            $formSubmission = new FormSubmission;

            $formSubmission->session_id = $request->query('session_id');
            $formSubmission->project_code = $projectCode;
            $formSubmission->form_id = $request->query('formDetails.id');
            $formSubmission->form_name = $request->query('formDetails.name');
            $formSubmission->page_url = $request->query('pageUrl');
            $formSubmission->form_data = json_encode($request->query('formDetails.elements'));

            $formSubmission->save();

            return response()->json(['message' => 'Form submission data stored successfully'], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to store form submission data', 'details' => $e->getMessage()], 500);
        }
    }

    protected function validateProjectCode($projectCode)
    {
        return Project::where('project_code', $projectCode)->exists();
    }

    protected function generateUrlCode($url)
    {
        return md5($url);
    }
}
