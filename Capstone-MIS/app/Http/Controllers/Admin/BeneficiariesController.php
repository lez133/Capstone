<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Barangay;
use App\Models\SeniorCitizenBeneficiary;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\QueryException;
use Exception;
use \DateTime;

class BeneficiariesController extends Controller
{
    /**
     * Display a listing of the beneficiaries.
     */
    public function index(Request $request)
    {
        $beneficiaries = SeniorCitizenBeneficiary::orderBy('last_name', 'asc')->get();

        return view('content.admin-interface.beneficiaries.beneficiaries-interface', compact('beneficiaries'));
    }

    /**
     * Show the form for creating a new beneficiary.
     */
    public function create(Request $request)
    {
        try {
            // Validate that the 'barangay' parameter exists
            if (!$request->has('barangay') || empty($request->query('barangay'))) {
                abort(400, 'Invalid Barangay ID');
            }

            // Decrypt the barangay ID
            $encryptedBarangayId = $request->query('barangay');
            $barangayId = Crypt::decrypt($encryptedBarangayId);

            // Find the barangay
            $barangay = Barangay::findOrFail($barangayId);

            return view('content.admin-interface.beneficiaries.senior-citizen.add-beneficiary.add-beneficiary', compact('barangay'));
        } catch (DecryptException $e) {
            abort(404, 'Invalid Barangay ID');
        }
    }

    /**
     * Store a newly created beneficiary in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'last_name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'birthday' => 'required|date',
            'age' => 'required|integer|min:60',
            'gender' => 'required|string',
            'civil_status' => 'required|string|max:255',
            'osca_number' => 'required|string|max:255|unique:senior_citizen_beneficiaries,osca_number',
            'date_issued' => 'required|date',
            'remarks' => 'nullable|string|max:255',
            'national_id' => 'nullable|string|max:255',
            'pkn' => 'nullable|string|max:255',
            'rrn' => 'nullable|string|max:255',
            'barangay_id' => 'required', // Encrypted barangay ID
        ]);

        try {
            $validated['barangay_id'] = Crypt::decrypt($validated['barangay_id']); // Decrypt the barangay ID

            $validated['osca_number'] = Crypt::encrypt($validated['osca_number']);
            $validated['national_id'] = $validated['national_id'] ? Crypt::encrypt($validated['national_id']) : null;
            $validated['pkn'] = $validated['pkn'] ? Crypt::encrypt($validated['pkn']) : null;
            $validated['rrn'] = $validated['rrn'] ? Crypt::encrypt($validated['rrn']) : null;

            SeniorCitizenBeneficiary::create($validated);

            return redirect()
                ->route('senior-citizen.interface')
                ->with('success', 'Beneficiary added successfully!');
        } catch (DecryptException $e) {
            return back()->withErrors(['barangay_id' => 'Invalid Barangay ID']);
        }
    }


    /**
     * Display the specified beneficiary.
     */
    public function show($id)
    {
        return view('content.admin-interface.beneficiaries.show-beneficiary');
    }

    /**
     * Show the form for editing the specified beneficiary.
     */
    public function edit($id)
    {
        $barangays = Barangay::orderBy('barangay_name')->get();
        return view('content.admin-interface.beneficiaries.edit-beneficiary', compact('barangays'));
    }

    /**
     * Update the specified beneficiary in storage.
     */
    public function update(Request $request, $id)
    {
        return redirect()->route('beneficiaries.index')->with('success', 'Beneficiary updated successfully.');
    }

    /**
     * Remove the specified beneficiary from storage.
     */
    public function destroy($id)
    {
        return redirect()->route('beneficiaries.index')->with('success', 'Beneficiary deleted successfully.');
    }

    /**
     * Display the senior citizen interface.
     */
    public function seniorCitizenInterface(Request $request)
    {
        $search = $request->input('search');
        $barangays = Barangay::when($search, function ($query, $search) {
            return $query->where('barangay_name', 'LIKE', "%{$search}%");
        })->orderBy('barangay_name')->get();

        $selectedBarangay = $request->input('barangay');
        $beneficiaries = [];

        if ($selectedBarangay) {
            $beneficiaries = [];
        }

        return view('content.admin-interface.beneficiaries.senior-citizen.senior-citizen-interface', compact('barangays', 'beneficiaries', 'selectedBarangay'));
    }

    /**
     * Search for barangays based on the query.
     */
    public function searchBarangays(Request $request)
    {
        // If caller provided a 'barangay' query param, validate it strictly
        if ($request->has('barangay')) {
            $raw = $request->query('barangay');

            // Immediately reject plain numeric / raw ids
            if (ctype_digit((string) $raw)) {
                return response()->json(['message' => 'Invalid parameter'], 400);
            }

            // Try decrypting (support both encryptString and encrypt)
            $decryptedId = null;
            try {
                $decryptedId = Crypt::decryptString($raw);
            } catch (\Throwable $e1) {
                try {
                    $decryptedId = Crypt::decrypt($raw);
                } catch (\Throwable $e2) {
                    return response()->json(['message' => 'Invalid parameter'], 400);
                }
            }

            if (!is_numeric($decryptedId)) {
                return response()->json(['message' => 'Invalid parameter'], 400);
            }

            $barangay = Barangay::find((int) $decryptedId);
            return response()->json($barangay ? [$barangay] : []);
        }

        // Otherwise perform normal live-search by name
        $search = $request->input('search');
        $barangays = Barangay::when($search, function ($q, $s) {
                $q->where('barangay_name', 'LIKE', "%{$s}%");
            })->orderBy('barangay_name')->get();

        return response()->json($barangays);
    }

    /**
     * View senior beneficiaries for a specific barangay.
     */
    public function viewSeniorBeneficiaries(Request $request, $encryptedBarangayId)
    {
        try {
            $barangayId = Crypt::decrypt($encryptedBarangayId);
            $barangay = Barangay::findOrFail($barangayId);
            $search = $request->input('search');

            $beneficiaries = SeniorCitizenBeneficiary::where('barangay_id', $barangayId)
                ->when($search, function ($query, $search) {
                    return $query->where(function ($q) use ($search) {
                        $q->where('last_name', 'LIKE', "%{$search}%")
                          ->orWhere('first_name', 'LIKE', "%{$search}%")
                          ->orWhere('middle_name', 'LIKE', "%{$search}%")
                          ->orWhere('osca_number', 'LIKE', "%{$search}%");
                    });
                })
                ->orderBy('last_name', 'asc') // Order by last name in ascending order
                ->paginate(20);

            return view('content.admin-interface.beneficiaries.senior-citizen.view-senior-citizen.view-senior-citizen', compact('barangay', 'beneficiaries', 'search'));
        } catch (DecryptException $e) {
            abort(404, 'Invalid Barangay ID');
        }
    }

    /**
     * Import senior citizen beneficiaries from a CSV file.
     */
    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
            'encrypted_barangay_id' => 'required',
        ]);

        try {
            $barangayId = Crypt::decrypt($request->input('encrypted_barangay_id'));
        } catch (DecryptException $e) {
            return response()->json(['error' => 'Invalid Barangay ID'], 400);
        }

        $file = fopen($request->file('csv_file'), 'r');
        $header = fgetcsv($file);

        $totalRows = 0;
        $insertedRows = 0;
        $errors = [];

        while ($row = fgetcsv($file)) {
            $totalRows++;
            $row = array_map(fn($field) => mb_convert_encoding($field, 'UTF-8', 'auto'), $row);

            try {
                $age = is_numeric($row[5]) ? (int)$row[5] : null;
                $birthday = null;

                if (!empty($row[4])) {
                    $birthday = $this->normalizeDate($row[4]);
                    if ($birthday && is_null($age)) {
                        $birthDate = new DateTime($birthday);
                        $age = (new DateTime())->diff($birthDate)->y;
                    }
                }

                if (!$birthday && !is_null($age)) {
                    $birthYear = now()->year - $age;
                    $birthday = $birthYear . '-01-01';
                }

                if (is_null($age)) {
                    throw new Exception("Age cannot be null for row {$totalRows}");
                }
                if (is_null($birthday)) {
                    throw new Exception("Birthday cannot be null for row {$totalRows}");
                }

                $dateIssued = $this->normalizeDate($row[9]);

                SeniorCitizenBeneficiary::create([
                    'barangay_id' => $barangayId,
                    'last_name' => $row[1] ?? null,
                    'first_name' => $row[2] ?? null,
                    'middle_name' => $row[3] ?? null,
                    'birthday' => $birthday,
                    'age' => $age,
                    'gender' => $row[6] ?? null,
                    'civil_status' => $row[7] ?? null,
                    'osca_number' => $row[8] ? Crypt::encrypt($row[8]) : null,
                    'date_issued' => $dateIssued,
                    'remarks' => $row[10] ?? null,
                    'national_id' => $row[11] ? Crypt::encrypt($row[11]) : null,
                    'pkn' => $row[12] ? Crypt::encrypt($row[12]) : null,
                    'rrn' => $row[13] ? Crypt::encrypt($row[13]) : null,
                ]);

                $insertedRows++;
            } catch (QueryException $qe) {
                if ($qe->getCode() == 23000) {
                    $errors[] = ['row' => $totalRows, 'error' => "Duplicate OSCA Number: {$row[8]}"];
                } else {
                    $errors[] = ['row' => $totalRows, 'error' => $qe->getMessage()];
                }
            } catch (\Exception $e) {
                $errors[] = ['row' => $totalRows, 'error' => $e->getMessage()];
            }
        }

        fclose($file);

        return response()->json([
            'success' => true,
            'message' => 'Import completed.',
            'totalRows' => $totalRows,
            'insertedRows' => $insertedRows,
            'errors' => $errors,
        ]);
    }

    /**
     * Normalize date formats to Y-m-d (handles -, /, .)
     */
    private function normalizeDate($date)
    {
        if (empty($date)) {
            return null;
        }

        // Replace / or . with -
        $date = str_replace(['/', '.'], '-', trim($date));

        $formats = ['m-d-Y', 'Y-m-d', 'd-m-Y'];

        foreach ($formats as $format) {
            $dateTime = DateTime::createFromFormat($format, $date);
            if ($dateTime) {
                return $dateTime->format('Y-m-d');
            }
        }

        // Last resort: let DateTime try auto-detection
        try {
            return (new DateTime($date))->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

}
