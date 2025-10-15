<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Barangay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

class BarangayController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search'); // Get the search query from the request

        // Filter barangays based on the search query
        $barangays = Barangay::when($search, function ($query, $search) {
            return $query->where('barangay_name', 'LIKE', "%{$search}%");
        })->orderBy('barangay_name')->get();

        return view('content.admin-interface.programs.barangays.barangay', compact('barangays', 'search'));
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

    public function search(Request $request)
    {
        // If caller passed a "barangay" param, require it to be an encrypted string.
        if ($request->filled('barangay')) {
            $raw = $request->input('barangay');

            // Block plain numeric IDs
            if (ctype_digit((string) $raw)) {
                return response()->json(['message' => 'Invalid parameter'], 400);
            }

            // Try to decrypt; if decryption fails, reject
            try {
                $decryptedId = Crypt::decryptString($raw);
            } catch (DecryptException $e) {
                return response()->json(['message' => 'Invalid parameter'], 400);
            }

            // Ensure decrypted value is a valid id
            if (!is_numeric($decryptedId)) {
                return response()->json(['message' => 'Invalid parameter'], 400);
            }

            $barangay = Barangay::find((int)$decryptedId);
            return response()->json($barangay ? [$barangay] : []);
        }

        // Normal live-search (no barangay param)
        $search = $request->input('search');
        $barangays = Barangay::when($search, function ($q, $s) {
            $q->where('barangay_name', 'LIKE', "%{$s}%");
        })->orderBy('barangay_name')->get();

        return response()->json($barangays);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'barangay_name' => 'required|string|max:255|unique:barangays,barangay_name,' . $id,
        ]);

        $barangay = Barangay::findOrFail($id);
        $barangay->update(['barangay_name' => $request->barangay_name]);

        return back()->with('success', 'Barangay updated successfully!');
    }


    public function destroy($id)
    {
        $barangay = Barangay::findOrFail($id);
        $barangay->delete();

        return back()->with('success', 'Barangay deleted successfully!');
    }
}
