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

class SeniorCitizenBeneficiariesController extends Controller
{

    public function create(Request $request)
    {
        try {
            if (!$request->has('barangay') || empty($request->query('barangay'))) {
                abort(400, 'Invalid Barangay ID');
            }

            $encryptedBarangayId = $request->query('barangay');
            $barangayId = Crypt::decrypt($encryptedBarangayId);

            $barangay = Barangay::findOrFail($barangayId);

            // added: pull distinct civil_status and remarks for this barangay
            $civilStatuses = SeniorCitizenBeneficiary::where('barangay_id', $barangayId)
                ->whereNotNull('civil_status')
                ->distinct()
                ->pluck('civil_status')
                ->filter()
                ->values();

            $remarks = SeniorCitizenBeneficiary::where('barangay_id', $barangayId)
                ->whereNotNull('remarks')
                ->distinct()
                ->pluck('remarks')
                ->filter()
                ->values();

            return view('content.admin-interface.beneficiaries.senior-citizen.add-beneficiary.add-beneficiary', compact('barangay', 'civilStatuses', 'remarks'));
        } catch (DecryptException $e) {
            abort(404, 'Invalid Barangay ID');
        }
    }

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
            'barangay_id' => 'required',
        ]);

        try {
            $validated['barangay_id'] = Crypt::decrypt($validated['barangay_id']);

            $validated['osca_number'] = Crypt::encrypt($validated['osca_number']);
            $validated['national_id'] = $validated['national_id'] ? Crypt::encrypt($validated['national_id']) : null;
            $validated['pkn'] = $validated['pkn'] ? Crypt::encrypt($validated['pkn']) : null;
            $validated['rrn'] = $validated['rrn'] ? Crypt::encrypt($validated['rrn']) : null;

            SeniorCitizenBeneficiary::create($validated);

            // Check if request is AJAX
            if ($request->wantsJson() || $request->expectsJson() || $request->header('Accept') === 'application/json') {
                return response()->json([
                    'success' => true,
                    'message' => 'Beneficiary added successfully!'
                ], 201);
            }

            return redirect()
                ->route('senior-citizen.interface')
                ->with('success', 'Beneficiary added successfully!');

        } catch (DecryptException $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Invalid Barangay ID'], 400);
            }
            return back()->withErrors(['barangay_id' => 'Invalid Barangay ID']);
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

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

    public function searchBarangays(Request $request)
    {
        // Case 1: Searching via encrypted Barangay ID
        if ($request->has('barangay')) {
            $raw = $request->query('barangay');

            // Reject numeric or short values
            if (ctype_digit((string) $raw) || strlen($raw) < 32) {
                return response()->json(['message' => 'Invalid parameter'], 400);
            }

            try {
                $decryptedId = Crypt::decryptString($raw);
            } catch (\Throwable $e) {
                return response()->json(['message' => 'Invalid or corrupted parameter'], 400);
            }

            $barangay = Barangay::with('seniorCitizenBeneficiaries')->find((int) $decryptedId);
            if (!$barangay) {
                return response()->json(['message' => 'Barangay not found'], 404);
            }

            return response()->json([
                'barangay_name' => $barangay->barangay_name,
                'encrypted_id' => Crypt::encryptString($barangay->id),
                'beneficiaries' => $barangay->seniorCitizenBeneficiaries
            ]);
        }

        // Case 2: Live search by name
        $search = $request->input('search');
        $barangays = Barangay::when($search, fn($q, $s) =>
                $q->where('barangay_name', 'LIKE', "%{$s}%")
            )
            ->orderBy('barangay_name')
            ->get()
            ->map(function ($barangay) {
                $barangay->encrypted_id = Crypt::encrypt($barangay->id);
                unset($barangay->id);
                return $barangay;
            });

        return response()->json($barangays);
    }


    public function viewSeniorBeneficiaries(Request $request, $encryptedBarangayId)
    {
        try {
            $barangayId = Crypt::decrypt($encryptedBarangayId);
            $barangay = Barangay::findOrFail($barangayId);
            $search = $request->input('search');
            $remarksFilter = $request->input('remarks');

            // new filters
            $genderFilter = $request->input('gender');
            $civilStatusFilter = $request->input('civil_status');

            // Get all unique remarks for filter dropdown
            $allRemarks = SeniorCitizenBeneficiary::where('barangay_id', $barangayId)
                ->whereNotNull('remarks')
                ->distinct()
                ->pluck('remarks')
                ->filter()
                ->values();

            // collect distinct genders and civil_status values for filter selects
            $allGenders = SeniorCitizenBeneficiary::where('barangay_id', $barangayId)
                ->whereNotNull('gender')
                ->distinct()
                ->pluck('gender')
                ->map(fn($g) => trim($g))
                ->filter()
                ->values();

            $allCivilStatuses = SeniorCitizenBeneficiary::where('barangay_id', $barangayId)
                ->whereNotNull('civil_status')
                ->distinct()
                ->pluck('civil_status')
                ->map(fn($c) => trim($c))
                ->filter()
                ->values();

            $beneficiaries = SeniorCitizenBeneficiary::where('barangay_id', $barangayId)
                ->when($search, function ($query, $search) {
                    return $query->where(function ($q) use ($search) {
                        $q->where('last_name', 'LIKE', "%{$search}%")
                          ->orWhere('first_name', 'LIKE', "%{$search}%")
                          ->orWhere('middle_name', 'LIKE', "%{$search}%")
                          ->orWhere('osca_number', 'LIKE', "%{$search}%");
                    });
                })
                ->when($remarksFilter, function ($query, $remarksFilter) {
                    return $query->where('remarks', $remarksFilter);
                })
                ->when($genderFilter, function ($query, $genderFilter) {
                    $g = strtoupper(trim($genderFilter));
                    if (in_array($g, ['M','F'])) {
                        // Match first character of stored gender to handle "M", "Male", "Male " etc.
                        return $query->whereRaw('LOWER(LEFT(COALESCE(gender, \'\'),1)) = ?', [strtolower($g)]);
                    }
                    // fallback: case-insensitive exact match
                    return $query->whereRaw('LOWER(gender) = ?', [strtolower($genderFilter)]);
                })
                ->when($civilStatusFilter, function ($query, $civilStatusFilter) {
                    $cs = trim($civilStatusFilter);
                    $map = [
                        'Married'   => ['married','m','marr'],
                        'Widowed'   => ['widow','widowed','w'],
                        'Single'    => ['single','s'],
                        'Separated' => ['separated','sep','sp'],
                        'Divorced'  => ['divorced','div','d'],
                    ];
                    $patterns = $map[$cs] ?? [strtolower($cs)];
                    return $query->where(function($q) use ($patterns) {
                        foreach ($patterns as $p) {
                            $q->orWhereRaw('LOWER(civil_status) LIKE ?', ['%'.strtolower($p).'%']);
                        }
                    });
                })
                ->orderBy('last_name', 'asc')
                ->paginate(20);

            return view('content.admin-interface.beneficiaries.senior-citizen.view-senior-citizen.view-senior-citizen', compact(
                'barangay',
                'beneficiaries',
                'search',
                'allRemarks',
                'allGenders',
                'allCivilStatuses',
                'genderFilter',
                'civilStatusFilter'
            ));
        } catch (DecryptException $e) {
            abort(404, 'Invalid Barangay ID');
        }
    }

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

        $filePath = $request->file('csv_file')->getRealPath();
        $file = @fopen($filePath, 'r');
        if (!$file) {
            return response()->json(['error' => 'Unable to open the CSV file. Please ensure the file is readable.'], 400);
        }

        try {
            // Sample lines to choose delimiter (prefer tab if more tabs)
            $sample = [];
            rewind($file);
            $i = 0;
            while ($i < 20 && ($line = fgets($file)) !== false) {
                if (trim($line) !== '') $sample[] = $line;
                $i++;
            }
            if (empty($sample)) {
                fclose($file);
                return response()->json(['error' => 'CSV file is empty.'], 400);
            }
            $commaCount = 0; $tabCount = 0;
            foreach ($sample as $s) { $commaCount += substr_count($s, ','); $tabCount += substr_count($s, "\t"); }
            $delimiter = $tabCount > $commaCount ? "\t" : ",";

            // Helper: normalize header text
            $normalizeHeader = function ($s) {
                $s = (string)$s;
                $s = preg_replace('/^\x{FEFF}/u', '', $s); // remove BOM
                $s = trim($s);
                $s = mb_strtolower($s, 'UTF-8');
                $s = preg_replace('/[^a-z0-9\s]+/u', ' ', $s);
                $s = preg_replace('/\s+/', ' ', $s);
                return trim($s);
            };

            // Find header row (skip preamble/blank lines), allow up to 200 lines
            rewind($file);
            $header = null;
            $headerNorm = [];
            $maxSearch = 200;
            $searched = 0;
            while (($row = fgetcsv($file, 0, $delimiter)) !== false && $searched < $maxSearch) {
                $searched++;
                // skip blank rows
                if (count(array_filter($row, fn($v) => trim((string)$v) !== '')) === 0) continue;
                $cells = array_map($normalizeHeader, $row);
                $hasLast = false; $hasFirst = false;
                foreach ($cells as $c) {
                    if (str_contains($c, 'last')) $hasLast = true;
                    if (str_contains($c, 'first')) $hasFirst = true;
                }
                if ($hasLast && $hasFirst) {
                    $header = $row;
                    $headerNorm = $cells;
                    break;
                }
            }

            if (!$header) {
                fclose($file);
                return response()->json(['error' => 'CSV file does not contain expected columns. Expected at least: Last Name, First Name.'], 400);
            }

            // Build column index map for flexible column positions
            $colIndex = [];
            foreach ($headerNorm as $idx => $h) {
                if ($h === '') continue;
                if (str_contains($h, 'last')) $colIndex['last_name'] = $idx;
                if (str_contains($h, 'first')) $colIndex['first_name'] = $idx;
                if (str_contains($h, 'middle')) $colIndex['middle_name'] = $idx;
                if (str_contains($h, 'birth') || str_contains($h, 'date of birth') || str_contains($h, 'birthday')) $colIndex['birthday'] = $idx;
                if (str_contains($h, 'age')) $colIndex['age'] = $idx;
                if (str_contains($h, 'gender')) $colIndex['gender'] = $idx;
                if (str_contains($h, 'civil')) $colIndex['civil_status'] = $idx;
                if (str_contains($h, 'osca')) $colIndex['osca_number'] = $idx;
                if (str_contains($h, 'issued') || str_contains($h, 'date issued')) $colIndex['date_issued'] = $idx;
                if (str_contains($h, 'remark')) $colIndex['remarks'] = $idx;
                if (str_contains($h, 'national')) $colIndex['national_id'] = $idx;
                if (str_contains($h, 'pkn')) $colIndex['pkn'] = $idx;
                if (str_contains($h, 'rrn')) $colIndex['rrn'] = $idx;
            }

            if (!isset($colIndex['last_name']) || !isset($colIndex['first_name'])) {
                fclose($file);
                return response()->json(['error' => 'CSV file does not contain expected columns. Expected at least: Last Name, First Name.'], 400);
            }

            // Read data rows (pointer is after header)
            $totalRows = 0;
            $insertedRows = 0;
            $skippedRows = 0;
            $errors = [];
            $maxErrors = 25;
            $insertedIds = [];
            while (($row = fgetcsv($file, 0, $delimiter)) !== false) {
                // skip blank rows
                if (count(array_filter($row, fn($v) => trim((string)$v) !== '')) === 0) continue;
                $totalRows++;

                // convert encoding
                $row = array_map(fn($f) => mb_convert_encoding((string)$f, 'UTF-8', 'auto'), $row);

                try {
                    $last = trim($row[$colIndex['last_name']] ?? '');
                    $first = trim($row[$colIndex['first_name']] ?? '');
                    $birthdayRaw = isset($colIndex['birthday']) ? trim($row[$colIndex['birthday']] ?? '') : '';
                    $birthday = $this->normalizeDate($birthdayRaw);

                    // Duplicate check: same barangay, last name, first name, birthday
                    $exists = SeniorCitizenBeneficiary::where('barangay_id', $barangayId)
                        ->where('last_name', $last)
                        ->where('first_name', $first)
                        ->where('birthday', $birthday)
                        ->exists();

                    if ($exists) {
                        $skippedRows++;
                        continue;
                    }

                    $middle = isset($colIndex['middle_name']) ? trim($row[$colIndex['middle_name']] ?? '') : null;
                    $age = isset($colIndex['age']) && is_numeric($row[$colIndex['age']]) ? (int)$row[$colIndex['age']] : null;
                    $dateIssuedRaw = isset($colIndex['date_issued']) ? trim($row[$colIndex['date_issued']] ?? '') : '';
                    $dateIssued = $this->normalizeDate($dateIssuedRaw);

                    $payload = [
                        'barangay_id' => $barangayId,
                        'last_name' => $last,
                        'first_name' => $first,
                        'middle_name' => $middle ?: null,
                        'birthday' => $birthday,
                        'age' => $age,
                        'gender' => isset($colIndex['gender']) ? trim($row[$colIndex['gender']] ?? '') : null,
                        'civil_status' => isset($colIndex['civil_status']) ? trim($row[$colIndex['civil_status']] ?? '') : null,
                        'osca_number' => !empty($colIndex['osca_number'] && ($row[$colIndex['osca_number']] ?? '') ) ? Crypt::encrypt(trim($row[$colIndex['osca_number']])) : null,
                        'date_issued' => $dateIssued,
                        'remarks' => isset($colIndex['remarks']) ? trim($row[$colIndex['remarks']] ?? '') : null,
                        'national_id' => !empty($colIndex['national_id'] && ($row[$colIndex['national_id']] ?? '') ) ? Crypt::encrypt(trim($row[$colIndex['national_id']])) : null,
                        'pkn' => !empty($colIndex['pkn'] && ($row[$colIndex['pkn']] ?? '') ) ? Crypt::encrypt(trim($row[$colIndex['pkn']])) : null,
                        'rrn' => !empty($colIndex['rrn'] && ($row[$colIndex['rrn']] ?? '') ) ? Crypt::encrypt(trim($row[$colIndex['rrn']])) : null,
                    ];

                    $beneficiary = SeniorCitizenBeneficiary::create($payload);
                    $insertedRows++;
                    $insertedIds[] = $beneficiary->id;
                } catch (\Throwable $e) {
                    if (count($errors) < $maxErrors) {
                        $errors[] = "Row {$totalRows}: " . $e->getMessage();
                    }
                }
            }

            fclose($file);

            if ($totalRows === 0) {
                return response()->json(['error' => 'CSV file contains no valid data rows.'], 400);
            }

            if ($insertedRows === 0) {
                $msg = $skippedRows > 0
                    ? 'Warning: All data in your file already exists in the database. No new beneficiaries were imported.'
                    : 'No beneficiaries were imported. Please check your CSV format and data.';
                return response()->json(['warning' => $msg, 'skippedRows' => $skippedRows, 'errors' => $errors], 200);
            }

            // Save metadata for undo
            if (!empty($insertedIds)) {
                $metaDir = storage_path('app/senior_import_meta');
                if (!is_dir($metaDir)) @mkdir($metaDir, 0755, true);
                file_put_contents($metaDir . "/last_import_{$barangayId}.json", json_encode([
                    'created_at' => now()->toDateTimeString(),
                    'inserted_ids' => $insertedIds,
                ]));
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully imported {$insertedRows} beneficiary(ies) out of {$totalRows} rows. Skipped {$skippedRows} duplicate(s).",
                'totalRows' => $totalRows,
                'insertedRows' => $insertedRows,
                'skippedRows' => $skippedRows,
                'insertedIds' => $insertedIds,
                'errors' => $errors,
            ]);
        } catch (\Throwable $e) {
            if (is_resource($file)) fclose($file);
            return response()->json(['error' => 'An error occurred while processing the CSV: ' . $e->getMessage()], 400);
        }
    }

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

    public function editSeniorBeneficiary($id)
    {
        $beneficiary = SeniorCitizenBeneficiary::findOrFail($id);
        $barangay = $beneficiary->barangay;
        return view('content.admin-interface.beneficiaries.senior-citizen.edit-beneficiary.edit-beneficiary', compact('beneficiary', 'barangay'));
    }

    public function updateSeniorBeneficiary(Request $request, $id)
    {
        $beneficiary = SeniorCitizenBeneficiary::findOrFail($id);

        $validated = $request->validate([
            'last_name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'birthday' => 'required|date',
            'age' => 'required|integer|min:60',
            'gender' => 'required|string',
            'civil_status' => 'required|string|max:255',
            'osca_number' => 'required|string|max:255|unique:senior_citizen_beneficiaries,osca_number,' . $id,
            'date_issued' => 'required|date',
            'remarks' => 'nullable|string|max:255',
            'national_id' => 'nullable|string|max:255',
            'pkn' => 'nullable|string|max:255',
            'rrn' => 'nullable|string|max:255',
        ]);

        $validated['osca_number'] = Crypt::encrypt($validated['osca_number']);
        $validated['national_id'] = $validated['national_id'] ? Crypt::encrypt($validated['national_id']) : null;
        $validated['pkn'] = $validated['pkn'] ? Crypt::encrypt($validated['pkn']) : null;
        $validated['rrn'] = $validated['rrn'] ? Crypt::encrypt($validated['rrn']) : null;

        $beneficiary->update($validated);

        return redirect()->back()->with('success', 'Beneficiary updated successfully!');
    }

    public function deleteSeniorBeneficiary($id)
    {
        $beneficiary = SeniorCitizenBeneficiary::findOrFail($id);
        $beneficiary->delete();
        return redirect()->back()->with('success', 'Beneficiary deleted successfully!');
    }

    // Export CSV
    public function exportSeniorBeneficiaries($encryptedBarangayId)
    {
        try {
            $barangayId = Crypt::decrypt($encryptedBarangayId);
            $beneficiaries = SeniorCitizenBeneficiary::where('barangay_id', $barangayId)->get();

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="senior_citizen_beneficiaries.csv"',
            ];

            $columns = [
                'last_name', 'first_name', 'middle_name', 'birthday', 'age', 'gender',
                'civil_status', 'osca_number', 'date_issued', 'remarks', 'national_id', 'pkn', 'rrn'
            ];

            $callback = function () use ($beneficiaries, $columns) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $columns);

                foreach ($beneficiaries as $beneficiary) {
                    $row = [];
                    foreach ($columns as $col) {
                        $value = $beneficiary->$col;
                        if (in_array($col, ['osca_number', 'national_id', 'pkn', 'rrn']) && $value) {
                            $value = Crypt::decrypt($value);
                        }
                        $row[] = $value;
                    }
                    fputcsv($file, $row);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to export CSV.');
        }
    }
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="senior_beneficiaries_template.csv"',
        ];

        // CSV columns EXACTLY matching import() processing structure
        $columns = [
            'No',               // index 0
            'Last Name',        // index 1
            'First Name',       // index 2
            'Middle Name',      // index 3
            'Birthday',         // index 4
            'Age',              // index 5
            'Gender',           // index 6
            'Civil Status',     // index 7
            'OSCA Number',      // index 8
            'Date Issued',      // index 9
            'Remarks',          // index 10
            'National ID',      // index 11
            'PKN',              // index 12
            'RRN',              // index 13
        ];

        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            // Add example row to guide users
            fputcsv($file, [
                1,
                'Dela Cruz',
                'Juan',
                'Santos',
                '1950-04-12',
                74,
                'Male',
                'Married',
                'OSCA-12345',
                '2022-01-01',
                'Active',
                '1234-5678-91011',
                'PKN123',
                'RRN987',
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function undoImport(Request $request)
    {
        $ids = $request->input('ids', []);
        $importHash = $request->input('import_batch_hash', null);
        $deleted = 0;
        $metaPath = null;

        // If import hash provided, read metadata file to get IDs
        if ($importHash && $request->filled('encrypted_barangay_id')) {
            try {
                $barangayId = Crypt::decrypt($request->input('encrypted_barangay_id'));
            } catch (\Throwable $e) {
                return response()->json(['success' => false, 'error' => 'Invalid barangay id.'], 400);
            }
            $metaPath = storage_path("app/senior_import_meta/{$importHash}.json");
            if (!file_exists($metaPath)) {
                return response()->json(['success' => false, 'error' => 'Import batch not found.'], 404);
            }
            $meta = json_decode(file_get_contents($metaPath), true);
            $ids = $meta['inserted_ids'] ?? [];
        } else if ($request->filled('encrypted_barangay_id')) {
            try {
                $barangayId = Crypt::decrypt($request->input('encrypted_barangay_id'));
            } catch (\Throwable $e) {
                return response()->json(['success' => false, 'error' => 'Invalid barangay id.'], 400);
            }
            $metaPath = storage_path("app/senior_import_meta/last_import_{$barangayId}.json");
            if (file_exists($metaPath)) {
                $meta = json_decode(file_get_contents($metaPath), true);
                $ids = $meta['inserted_ids'] ?? [];
            }
        }

        if (!is_array($ids) || empty($ids)) {
            return response()->json(['success' => false, 'error' => 'No import IDs provided.'], 400);
        }

        $deleted = SeniorCitizenBeneficiary::whereIn('id', $ids)->delete();

        // Remove the metadata file after undo
        if ($metaPath && file_exists($metaPath)) {
            @unlink($metaPath);
        }

        return response()->json(['success' => true, 'deleted' => $deleted]);
    }

    public function relatedSearch(Request $request)
    {
        $search = $request->input('search');
        $page = max(1, (int)$request->input('page', 1));
        $perPage = 10;
        $results = [];
        $total = 0;

        if ($search) {
            $query = SeniorCitizenBeneficiary::with('barangay'); // eager load barangay

            if ($request->has('barangay_id')) {
                $query->where('barangay_id', $request->input('barangay_id'));
            }

            $records = $query->get(['barangay_id','first_name','last_name','middle_name','age','gender','osca_number','birthday','remarks']);

            $filtered = [];
            foreach ($records as $r) {
                $decryptedOsca = '';
                if ($r->osca_number) {
                    try {
                        $decryptedOsca = \Illuminate\Support\Facades\Crypt::decrypt($r->osca_number);
                    } catch (\Exception $e) {
                        $decryptedOsca = '';
                    }
                }
                if (
                    stripos($decryptedOsca, $search) !== false ||
                    stripos($r->first_name, $search) !== false ||
                    stripos($r->last_name, $search) !== false ||
                    stripos($r->middle_name ?? '', $search) !== false ||
                    stripos($r->birthday, $search) !== false
                ) {
                    $filtered[] = [
                        'first_name' => $r->first_name,
                        'last_name' => $r->last_name,
                        'middle_name' => $r->middle_name,
                        'age' => $r->age,
                        'gender' => $r->gender,
                        'osca_number' => $decryptedOsca,
                        'birthday' => $r->birthday,
                        'remarks' => $r->remarks,
                        'barangay_name' => $r->barangay->barangay_name ?? 'N/A',
                    ];
                }
            }

            $total = count($filtered);
            $results = array_slice($filtered, ($page - 1) * $perPage, $perPage);
        }

        return response()->json([
            'results' => $results,
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => $total > 0 ? ceil($total / $perPage) : 1,
            ]
        ]);
    }
}
