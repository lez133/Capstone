<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Barangay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;

class BarangayController extends Controller
{
    public function index()
    {
        $barangays = Barangay::orderBy('barangay_name')->get();
        return view('content.admin-interface.programs.barangays.barangay', compact('barangays'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'names' => 'required|array|min:1',
            'names.*' => 'required|string|max:255|distinct'
        ]);
        foreach ($request->names as $name) {
            Barangay::firstOrCreate(['barangay_name' => $name]);
        }
        return back()->with('success', 'Barangay(s) added!');
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt']);
        $file = fopen($request->file('file'), 'r');
        while (($line = fgetcsv($file)) !== false) {
            if (!empty($line[0])) {
                Barangay::firstOrCreate(['barangay_name' => trim($line[0])]);
            }
        }
        fclose($file);
        return back()->with('success', 'Barangays imported!');
    }

    public function export()
    {
        $barangays = Barangay::orderBy('barangay_name')->pluck('barangay_name')->toArray();
        $csv = implode("\n", $barangays);
        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="barangays.csv"',
        ]);
    }
}
