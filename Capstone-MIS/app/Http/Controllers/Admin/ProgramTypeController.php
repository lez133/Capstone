<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProgramType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ProgramTypeController extends Controller
{
    public function index()
    {
        $programTypes = ProgramType::orderBy('program_type_name')->get();
        return view('content.admin-interface.programs.program-type.program-type', compact('programTypes'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'program_type_name' => 'required|string|max:255|unique:program_types,program_type_name',
            ]);

            $programType = ProgramType::create([
                'program_type_name' => $validated['program_type_name'],
            ]);

            // Return JSON for AJAX/Fetch requests
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'id' => $programType->id,
                    'program_type_name' => $programType->program_type_name,
                ], 201);
            }

            return redirect()->route('program-types.index')->with('success', 'Program Type added!');
        } catch (ValidationException $e) {
            // Return JSON validation errors for AJAX
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['errors' => $e->errors()], 422);
            }
            throw $e;
        } catch (\Throwable $e) {
            Log::error('ProgramType store error: '.$e->getMessage());
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => 'Server error. Please try again.'], 500);
            }
            return back()->with('error', 'Server error. Please try again.');
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'program_type_name' => 'required|string|max:255|unique:program_types,program_type_name,' . $id,
        ]);
        $programType = ProgramType::findOrFail($id);
        $programType->update(['program_type_name' => $request->program_type_name]);
        return redirect()->route('program-types.index')->with('success', 'Program Type updated!');
    }

    public function destroy($id)
    {
        $programType = ProgramType::find($id);

        if (!$programType) {
            return response()->json(['success' => false, 'error' => 'Program Type not found.'], 404);
        }
        try {
            $programType->delete();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Delete failed.'], 500);
        }
    }
}
