<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class ScheduleController extends Controller
{
    /**
     * Display the schedule page.
     */
    public function index()
    {
        return view('content.admin-interface.schedule.schedule');
    }
}
