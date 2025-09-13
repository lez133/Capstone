<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BeneficiariesController extends Controller
{
    /**
     * Display a listing of the beneficiaries.
     */
    public function index(Request $request)
    {
        // Static data for demonstration
        $beneficiaries = [
            [
                'id' => 1,
                'name' => 'John Doe',
                'program' => 'Health Program',
                'contact' => '+123456789',
                'address' => '123 Main St',
            ],
            [
                'id' => 2,
                'name' => 'Jane Smith',
                'program' => 'Education Program',
                'contact' => '+987654321',
                'address' => '456 Elm St',
            ],
        ];

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $beneficiaries = array_filter($beneficiaries, function ($beneficiary) use ($search) {
                return stripos($beneficiary['name'], $search) !== false ||
                       stripos($beneficiary['program'], $search) !== false ||
                       stripos($beneficiary['contact'], $search) !== false ||
                       stripos($beneficiary['address'], $search) !== false;
            });
        }

        return view('content.admin-interface.beneficiaries.beneficiaries-interface', compact('beneficiaries'));
    }

    /**
     * Show the form for creating a new beneficiary.
     */
    public function create()
    {
        return view('content.admin-interface.beneficiaries.create-beneficiary');
    }

    /**
     * Store a newly created beneficiary in storage.
     */
    public function store(Request $request)
    {
        // Simulate storing data (no database)
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'program' => 'required|string|max:255',
            'contact' => 'required|string|max:15',
            'address' => 'required|string|max:255',
        ]);

        // Normally, you would save to the database here
        return redirect()->route('beneficiaries.index')->with('success', 'Beneficiary added successfully (simulated).');
    }

    /**
     * Display the specified beneficiary.
     */
    public function show($id)
    {
        // Static data for demonstration
        $beneficiary = [
            'id' => $id,
            'name' => 'John Doe',
            'program' => 'Health Program',
            'contact' => '+123456789',
            'address' => '123 Main St',
        ];

        return view('content.admin-interface.beneficiaries.show-beneficiary', compact('beneficiary'));
    }

    /**
     * Show the form for editing the specified beneficiary.
     */
    public function edit($id)
    {
        // Static data for demonstration
        $beneficiary = [
            'id' => $id,
            'name' => 'John Doe',
            'program' => 'Health Program',
            'contact' => '+123456789',
            'address' => '123 Main St',
        ];

        return view('content.admin-interface.beneficiaries.edit-beneficiary', compact('beneficiary'));
    }

    /**
     * Update the specified beneficiary in storage.
     */
    public function update(Request $request, $id)
    {
        // Simulate updating data (no database)
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'program' => 'required|string|max:255',
            'contact' => 'required|string|max:15',
            'address' => 'required|string|max:255',
        ]);

        // Normally, you would update the database here
        return redirect()->route('beneficiaries.index')->with('success', 'Beneficiary updated successfully (simulated).');
    }

    /**
     * Remove the specified beneficiary from storage.
     */
    public function destroy($id)
    {
        // Simulate deleting data (no database)
        return redirect()->route('beneficiaries.index')->with('success', 'Beneficiary deleted successfully (simulated).');
    }
}
