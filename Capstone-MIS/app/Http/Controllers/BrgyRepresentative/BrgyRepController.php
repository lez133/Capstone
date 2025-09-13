<?php

namespace App\Http\Controllers\BrgyRepresentative;

use App\Http\Controllers\Controller;

class BrgyRepController extends Controller
{
    public function index()
    {
        // Define the $data variable
        $data = [
            'assist_registration' => true,
            'submit_aid_requests' => true,
            'manage_schedules' => true,
            'monitor_sms_notifications' => true,
            'track_applications' => true,
        ];

        // Pass $data to the view
        return view('content.brgyrepresentative-interface.dashboard.brgyrep-interface', compact('data'));
    }
}
