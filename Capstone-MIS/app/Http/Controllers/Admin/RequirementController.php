<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Requirement;

class RequirementController extends Controller
{
    public function index()
    {
        $requirements = Requirement::orderBy('document_requirement')->get();
        return view('content.admin-interface.programs.requirements.manage-requirements', compact('requirements'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'document_requirement' => 'required|string|max:255|unique:requirements,document_requirement',
        ]);

        $requirement = Requirement::create($request->only('document_requirement'));

        // If AJAX, return JSON
        if ($request->expectsJson() || $request->isJson()) {
            return response()->json([
                'id' => $requirement->id,
                'document_requirement' => $requirement->document_requirement
            ]);
        }

        // Otherwise, redirect
        return redirect()->route('requirements.index')->with('success', 'Requirement added!');
    }

    public function destroy(Requirement $requirement)
    {
        $requirement->delete();
        return redirect()->route('requirements.index')->with('success', 'Requirement deleted!');
    }


    public function update(Request $request, Requirement $requirement)
    {
        $request->validate([
            'document_requirement' => 'required|string|max:255|unique:requirements,document_requirement,' . $requirement->id,
        ]);
        $requirement->update($request->only('document_requirement'));
        return redirect()->route('requirements.index')->with('success', 'Requirement updated!');
    }
}
