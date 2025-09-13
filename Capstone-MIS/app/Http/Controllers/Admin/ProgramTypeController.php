<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProgramType;
use Illuminate\Http\Request;

class ProgramTypeController extends Controller
{
    public function index()
    {
        $programTypes = ProgramType::orderBy('program_type_name')->get();
        return view('content.admin-interface.programs.program-type.program-type', compact('programTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'program_type_name' => 'required|string|max:255|unique:program_types,program_type_name',
        ]);

        ProgramType::create(['program_type_name' => $request->program_type_name]);

        return back()->with('success', 'Program Type added successfully!');
    }
}
