<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AidProgram;
use App\Models\ProgramType;
use App\Models\Barangay;
use Illuminate\Http\Request;
use App\Models\Requirement;
use App\Models\Schedule;

class AidProgramController extends Controller
{
    public function index()
    {
        $aidPrograms = AidProgram::with('programType')->get();
        $programTypes = ProgramType::all();
        $barangays = Barangay::all();
        $requirements = Requirement::all();
        $now = now();
        $schedules = Schedule::with(['aidProgram.requirements'])
            ->where('published', true)
            ->where('end_date', '>=', $now)
            ->get();

        return view('content.admin-interface.programs.aid-program.aid-program', compact('aidPrograms', 'programTypes', 'barangays', 'requirements', 'schedules'));
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

        $data['requirements'] = $request->input('requirements');
        $data['location'] = $request->input('location');
        $data['qualified_barangays'] = json_encode($request->input('qualified_barangays'));

        $aidProgram = AidProgram::create($data);
        $aidProgram->requirements()->sync($request->input('requirements', []));

        return back()->with('success', 'Aid Program added successfully!');
    }

    public function show($id)
    {
        $aidProgram = AidProgram::with(['requirements', 'programType'])->findOrFail($id);
        $programTypes = ProgramType::all();
        $requirements = Requirement::all();
        $now = now();
        $schedules = Schedule::with(['aidProgram.requirements'])
            ->where('published', true)
            ->where('end_date', '>=', $now)
            ->get();

        return view('content.admin-interface.programs.aid-program.view-aid-program', compact('aidProgram', 'programTypes', 'requirements', 'schedules'));
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

        // Sync requirements
        $aidProgram->requirements()->sync($request->input('requirements', []));

        return redirect()->route('aid-programs.show', $id)->with('success', 'Aid Program updated successfully!');
    }

    public function destroy($id)
    {
        $aidProgram = AidProgram::findOrFail($id);
        $aidProgram->delete();

        return redirect()->route('aid-programs.index')->with('success', 'Aid Program deleted successfully!');
    }

    public function storeRequirement(Request $request)
    {
        $request->validate([
            'document_requirement' => 'required|string|max:255|unique:requirements,document_requirement',
        ]);
        $req = Requirement::create(['document_requirement' => $request->document_requirement]);
        return response()->json(['id' => $req->id, 'document_requirement' => $req->document_requirement]);
    }
}
