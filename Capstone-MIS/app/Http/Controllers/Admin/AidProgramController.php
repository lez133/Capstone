<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AidProgram;
use App\Models\ProgramType;
use App\Models\Barangay;
use Illuminate\Http\Request;
use App\Models\Requirement;
use App\Models\Schedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Notifications\AidProgramAvailableNotification;
use App\Models\Beneficiary;

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
            'description' => 'nullable|string',
            'program_type_id' => 'required|exists:program_types,id',
            'background_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'requirements' => 'nullable|array',
            'requirements.*' => 'exists:requirements,id',
            'create_schedule_now' => 'nullable|in:1',
            // schedule fields validated below when create_schedule_now is present
        ]);

        DB::beginTransaction();
        try {
            $data = $request->only(['aid_program_name', 'description', 'program_type_id', 'location', 'default_background']);
            if ($request->hasFile('background_image')) {
                $data['background_image'] = $request->file('background_image')->store('aid_programs', 'public');
            }

            // preserve qualified_barangays if present
            if ($request->has('qualified_barangays')) {
                $data['qualified_barangays'] = json_encode($request->input('qualified_barangays'));
            }

            $aidProgram = \App\Models\AidProgram::create($data);

            // attach requirements if any
            $aidProgram->requirements()->sync($request->input('requirements', []));

            // optionally create schedule
            if ($request->input('create_schedule_now')) {
                // validate schedule fields
                $scheduleRules = [
                    'beneficiary_type' => 'required|in:senior,pwd,both',
                    'start_date' => 'required|date|before:end_date',
                    'end_date' => 'required|date|after:start_date',
                ];
                if (in_array($request->input('beneficiary_type'), ['senior','both'])) {
                    $scheduleRules['barangay_ids'] = 'required|array|min:1';
                    $scheduleRules['barangay_ids.*'] = 'exists:barangays,id';
                }

                $validatedSchedule = $request->validate($scheduleRules);

                \App\Models\Schedule::create([
                    'aid_program_id'   => $aidProgram->id,
                    'barangay_ids'     => $request->input('barangay_ids', []),
                    'beneficiary_type' => $request->input('beneficiary_type'),
                    'start_date'       => $request->input('start_date'),
                    'end_date'         => $request->input('end_date'),
                    'published'        => false,
                ]);
            }

            DB::commit();
            return redirect()->route('aid-programs.index')->with('success', 'Aid Program created successfully' . ($request->input('create_schedule_now') ? ' and schedule created.' : '.'));
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('aidProgramCreateError', ['error' => $e->getMessage()]);
            return back()->withInput()->withErrors('Failed to create program. ' . $e->getMessage());
        }
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

    public function publish($id)
    {
        $program = AidProgram::find($id);
        $program->published = true;
        $program->save();

        // send to beneficiaries you want (example: all beneficiaries in qualified barangays)
        $beneficiaries = Beneficiary::whereIn('barangay_id', $program->qualified_barangays ?? [])->get();
        foreach ($beneficiaries as $b) {
            $b->notify(new AidProgramAvailableNotification($program));
        }

        return redirect()->route('aid-programs.index')->with('success', 'Aid Program published successfully!');
    }
}
