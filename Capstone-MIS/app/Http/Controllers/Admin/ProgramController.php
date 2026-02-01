<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class ProgramController extends Controller
{
    public function index()
    {
        return view('content.admin-interface.programs.programs');
    }
}
