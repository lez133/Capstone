<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AidProgram;
use App\Models\ProgramType;
use Illuminate\Http\Request;

class AidProgramController extends Controller
{
    public function index()
    {
        $aidPrograms = AidProgram::with('programType')->orderBy('aid_program_name')->get();
        $programTypes = ProgramType::orderBy('program_type_name')->get();
        return view('content.admin-interface.programs.aid-program.aid-program', compact('aidPrograms', 'programTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'aid_program_name' => 'required|string|max:255',
            'description' => 'required|string',
            'program_type_id' => 'required|exists:program_types,id',
            'background_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'default_background' => 'nullable|string',
        ]);

        $data = $request->all();

        // Handle uploaded background image
        if ($request->hasFile('background_image')) {
            $data['background_image'] = $request->file('background_image')->store('aid_programs', 'public');
        }

        // Handle default background
        if (!$request->hasFile('background_image') && $request->default_background) {
            $data['default_background'] = $request->default_background;
        }

        AidProgram::create($data);

        return back()->with('success', 'Aid Program added successfully!');
    }

    public function show($id)
    {
        $aidProgram = AidProgram::findOrFail($id);
        $programTypes = ProgramType::all();
        return view('content.admin-interface.programs.aid-program.view-aid-program', compact('aidProgram', 'programTypes'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'aid_program_name' => 'required|string|max:255',
            'description' => 'required|string',
            'program_type_id' => 'required|exists:program_types,id',
            'background_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'default_background' => 'nullable|string',
        ]);

        $aidProgram = AidProgram::findOrFail($id);

        $data = $request->all();

        if ($request->hasFile('background_image')) {
            $data['background_image'] = $request->file('background_image')->store('aid_programs', 'public');
        }

        $aidProgram->update($data);

        return redirect()->route('aid-programs.show', $id)->with('success', 'Aid Program updated successfully!');
    }

    public function destroy($id)
    {
        $aidProgram = AidProgram::findOrFail($id);
        $aidProgram->delete();

        return redirect()->route('aid-programs.index')->with('success', 'Aid Program deleted successfully!');
    }
}
