<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PWDBeneficiary;
use App\Models\Barangay;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class PWDBeneficiariesController extends Controller
{
    public function SelectBrgyInterface(Request $request)
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

        return view('content.admin-interface.beneficiaries.pwd.pwd-interface',compact('barangays', 'beneficiaries'));
    }

    public function viewPWDBeneficiaries(Request $request, $encryptedBarangayId)
    {
        try {
            $barangayId = Crypt::decrypt($encryptedBarangayId);
            $barangay = Barangay::findOrFail($barangayId);
            $search = $request->input('search');
            $typeFilter = $request->input('type_of_disability');
            $validityFilter = $request->input('validity_years');
            $remarksFilter = $request->input('remarks');

            // Get all unique disabilities, validity years, and remarks for filter dropdowns
            $allDisabilities = PWDBeneficiary::where('barangay_id', $barangayId)
                ->whereNotNull('type_of_disability')
                ->distinct()
                ->pluck('type_of_disability')
                ->filter()
                ->values();

            $allValidityYears = PWDBeneficiary::where('barangay_id', $barangayId)
                ->whereNotNull('validity_years')
                ->distinct()
                ->pluck('validity_years')
                ->filter()
                ->values();

            $allRemarks = PWDBeneficiary::where('barangay_id', $barangayId)
                ->whereNotNull('remarks')
                ->distinct()
                ->pluck('remarks')
                ->filter()
                ->values();

            $beneficiaries = \App\Models\PWDBeneficiary::where('barangay_id', $barangayId)
                ->when($search, function ($query, $search) {
                    return $query->where(function ($q) use ($search) {
                        $q->where('last_name', 'LIKE', "%{$search}%")
                          ->orWhere('first_name', 'LIKE', "%{$search}%")
                          ->orWhere('middle_name', 'LIKE', "%{$search}%")
                          ->orWhere('pwd_id_number', 'LIKE', "%{$search}%");
                    });
                })
                ->when($typeFilter, function ($query, $typeFilter) {
                    return $query->where('type_of_disability', $typeFilter);
                })
                ->when($validityFilter, function ($query, $validityFilter) {
                    return $query->where('validity_years', $validityFilter);
                })
                ->when($remarksFilter, function ($query, $remarksFilter) {
                    return $query->where('remarks', $remarksFilter);
                })
                ->orderBy('last_name', 'asc')
                ->paginate(20);

            return view('content.admin-interface.beneficiaries.pwd.view-pwd.view-pwds', compact(
                'barangay', 'beneficiaries', 'search', 'allDisabilities', 'allValidityYears', 'allRemarks'
            ));
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404, 'Invalid Barangay ID');
        }
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

            $barangay = Barangay::with('pwdBeneficiaries')->find((int) $decryptedId);
            if (!$barangay) {
                return response()->json(['message' => 'Barangay not found'], 404);
            }

            return response()->json([
                'barangay_name' => $barangay->barangay_name,
                'encrypted_id' => Crypt::encryptString($barangay->id),
                'beneficiaries' => $barangay->pwdBeneficiaries
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


    public function store(Request $request)
    {
        $validated = $request->validate([
            'last_name' => 'required|string|max:100',
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'barangay_id' => 'required|exists:barangays,id',
            'gender' => 'required|in:M,F',
            'type_of_disability' => 'required|string',
            'pwd_id_number' => 'required|string|max:50',
            'remarks' => 'nullable|string',
            // birthday is now required for manual add; also must be a valid date not in the future
            'birthday' => 'required|date|before_or_equal:today',
            'age' => 'nullable|integer|min:0|max:150',
            'validity_years' => 'required|integer|min:1|max:10',
        ]);

        // Additional uniqueness guard: no two beneficiaries in the same barangay may have the same
        // last_name + first_name + birthday combination (and we also guard pwd_id_number).
        $existsSameNameBirthday = PWDBeneficiary::where('barangay_id', $request->input('barangay_id'))
            ->where('last_name', $request->input('last_name'))
            ->where('first_name', $request->input('first_name'))
            ->whereDate('birthday', $request->input('birthday'))
            ->exists();

        if ($existsSameNameBirthday) {
            return back()->withInput()->with('error', 'A beneficiary with the same name and birthday already exists in this barangay.');
        }

        $existsPwdId = PWDBeneficiary::where('barangay_id', $request->input('barangay_id'))
            ->where('pwd_id_number', $request->input('pwd_id_number'))
            ->exists();

        if ($existsPwdId) {
            return back()->withInput()->with('error', 'PWD ID Number already exists for a beneficiary in this barangay.');
        }

        $years = (int) $validated['validity_years'];

        $validated['valid_from'] = Carbon::now()->toDateString();
        $validated['valid_to'] = Carbon::now()->addYears($years)->toDateString();

        PWDBeneficiary::create($validated);

        return redirect()->back()->with('success', 'PWD Beneficiary added successfully!');
    }


    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'last_name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'gender' => 'required|in:M,F',
            'birthday' => 'nullable|date', // <-- make birthday nullable
            'age' => 'nullable|integer|min:0',
            'type_of_disability' => 'required|string|max:255',
            'pwd_id_number' => 'required|string|max:255',
            'validity_years' => 'required|integer|min:1',
            'remarks' => 'nullable|string|max:255',
            'barangay_id' => 'required|integer|exists:barangays,id',
        ]);

        $beneficiary = PWDBeneficiary::findOrFail($id);
        $beneficiary->update($validated);

        return redirect()->back()->with('success', 'PWD Beneficiary updated successfully!');
    }

    public function destroy($id)
    {
        $beneficiary = PWDBeneficiary::findOrFail($id);
        $beneficiary->delete();

        // Use back() so deletion from modal returns to current list (avoid missing route names)
        return redirect()->back()->with('success', 'PWD Beneficiary deleted successfully!');
    }


    public function create(Request $request)
    {
        try {
            if (!$request->has('barangay') || empty($request->query('barangay'))) {
                abort(400, 'Invalid Barangay ID');
            }

            $encryptedBarangayId = $request->query('barangay');
            $barangayId = Crypt::decrypt($encryptedBarangayId);

            $barangay = Barangay::findOrFail($barangayId);

            // Pull distinct type_of_disability and remarks to show as selectable options
            $allDisabilities = \App\Models\PWDBeneficiary::where('barangay_id', $barangayId)
                ->whereNotNull('type_of_disability')
                ->distinct()
                ->pluck('type_of_disability')
                ->filter()
                ->values();

            $allRemarks = \App\Models\PWDBeneficiary::where('barangay_id', $barangayId)
                ->whereNotNull('remarks')
                ->distinct()
                ->pluck('remarks')
                ->filter()
                ->values();

            return view(
                'content.admin-interface.beneficiaries.pwd.add-beneficiary.add-beneficiary',
                compact('barangay', 'allDisabilities', 'allRemarks')
            );
        } catch (DecryptException $e) {
            abort(404, 'Invalid Barangay ID');
        }
    }

    public function importCsv(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt,csv',
            'encrypted_barangay_id' => 'required|string',
            'validity_years_import' => 'required|integer|min:1|max:10',
        ]);

        try {
            $barangayId = Crypt::decrypt($request->input('encrypted_barangay_id'));
        } catch (DecryptException $e) {
            return $request->wantsJson()
                ? response()->json(['success' => false, 'error' => 'Invalid Barangay ID'], 400)
                : back()->with('error', 'Invalid Barangay ID');
        }

        $filePath = $request->file('csv_file')->getRealPath();
        $file = @fopen($filePath, 'r');
        if (!$file) {
            return $request->wantsJson()
                ? response()->json(['success' => false, 'error' => 'Unable to open CSV file.'], 400)
                : back()->with('error', 'Unable to open CSV file.');
        }

        try {
            // Detect delimiter using first non-empty line
            $firstLine = '';
            while (($line = fgets($file)) !== false) {
                if (trim($line) !== '') { $firstLine = $line; break; }
            }
            if (!$firstLine) { fclose($file); return back()->with('error', 'CSV file is empty.'); }
            $delimiter = str_contains($firstLine, "\t") ? "\t" : ",";

            rewind($file);

            // Read header
            $header = fgetcsv($file, 4096, $delimiter);
            if (!$header || count($header) < 2) {
                fclose($file);
                return $request->wantsJson()
                    ? response()->json(['success' => false, 'error' => 'Invalid CSV format. Unable to read header.'], 400)
                    : back()->with('error', 'Invalid CSV format. Unable to read header.');
            }

            $totalRows = 0;
            $insertedRows = 0;
            $skippedRows = 0;
            $errors = [];
            $maxErrors = 25;
            $insertedIds = [];

            // Helper to normalize values for comparison
            $norm = function ($v) {
                if ($v === null) return '';
                return trim((string)$v);
            };

            while (($row = fgetcsv($file, 4096, $delimiter)) !== false) {
                $totalRows++;

                // Skip entirely empty rows
                if (count(array_filter($row, fn($v) => $v !== null && trim((string)$v) !== '')) === 0) {
                    continue;
                }

                try {
                    // Map columns loosely by index (template expected order):
                    // 0: No (optional) 1: Last Name 2: First Name 3: Middle Name 4: Gender or Birthday depending on template
                    // To be defensive, attempt to read known positions; fallback to empty string.
                    $lastName = $norm($row[0] ?? $row[1] ?? '');
                    $firstName = $norm($row[1] ?? $row[2] ?? '');
                    $middleName = $norm($row[2] ?? $row[3] ?? '');
                    $gender = $norm($row[3] ?? $row[4] ?? '');
                    $typeOfDisability = $norm($row[4] ?? $row[5] ?? '');
                    $pwdIdNumber = $norm($row[5] ?? $row[6] ?? '');
                    $remarks = $norm($row[6] ?? $row[7] ?? '');
                    $birthdayRaw = $norm($row[7] ?? $row[8] ?? '');
                    $ageRaw = $norm($row[8] ?? $row[9] ?? '');

                    // Try to normalize birthday to Y-m-d if present
                    $birthday = null;
                    if ($birthdayRaw !== '') {
                        try {
                            $birthday = Carbon::parse($birthdayRaw)->toDateString();
                        } catch (\Throwable $e) {
                            // attempt common format Y-m-d or leave null
                            $birthday = preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthdayRaw) ? $birthdayRaw : null;
                        }
                    }

                    $age = is_numeric($ageRaw) ? (int)$ageRaw : null;

                    // Basic required checks (last + first required)
                    if ($lastName === '' || $firstName === '') {
                        throw new \Exception('Last name and First name are required.');
                    }

                    // Build normalized candidate array for comparison / insertion
                    $candidate = [
                        'barangay_id' => $barangayId,
                        'last_name' => $lastName,
                        'first_name' => $firstName,
                        'middle_name' => $middleName !== '' ? $middleName : null,
                        'gender' => $gender !== '' ? $gender : null,
                        'type_of_disability' => $typeOfDisability !== '' ? $typeOfDisability : null,
                        'pwd_id_number' => $pwdIdNumber !== '' ? $pwdIdNumber : null,
                        'remarks' => $remarks !== '' ? $remarks : null,
                        'birthday' => $birthday,
                        'age' => $age,
                    ];

                    // Determine if an EXACT identical record already exists.
                    // Strategy: query candidates by same barangay + last + first and then compare all fields in PHP.
                    $candidates = PWDBeneficiary::where('barangay_id', $barangayId)
                        ->where('last_name', $lastName)
                        ->where('first_name', $firstName)
                        ->get();

                    $foundExact = false;
                    foreach ($candidates as $c) {
                        $matches = true;

                        // compare each relevant field normalized as strings
                        $pairs = [
                            ['db' => $c->middle_name, 'val' => $candidate['middle_name']],
                            ['db' => $c->gender, 'val' => $candidate['gender']],
                            ['db' => $c->type_of_disability, 'val' => $candidate['type_of_disability']],
                            ['db' => $c->pwd_id_number, 'val' => $candidate['pwd_id_number']],
                            ['db' => $c->remarks, 'val' => $candidate['remarks']],
                            ['db' => $c->birthday ? Carbon::parse($c->birthday)->toDateString() : null, 'val' => $candidate['birthday']],
                            ['db' => $c->age !== null ? (string)$c->age : null, 'val' => $candidate['age'] !== null ? (string)$candidate['age'] : null],
                        ];

                        foreach ($pairs as $p) {
                            $dbv = $p['db'] === null ? '' : (string)trim($p['db']);
                            $rv = $p['val'] === null ? '' : (string)trim($p['val']);
                            if ($dbv !== $rv) {
                                $matches = false;
                                break;
                            }
                        }

                        if ($matches) {
                            $foundExact = true;
                            break;
                        }
                    }

                    if ($foundExact) {
                        $skippedRows++;
                        continue; // skip insert for exact duplicate
                    }

                    // Not an exact duplicate -> create new record
                    $createData = [
                        'barangay_id' => $candidate['barangay_id'],
                        'last_name' => $candidate['last_name'],
                        'first_name' => $candidate['first_name'],
                        'middle_name' => $candidate['middle_name'],
                        'gender' => $candidate['gender'],
                        'type_of_disability' => $candidate['type_of_disability'],
                        'pwd_id_number' => $candidate['pwd_id_number'],
                        'remarks' => $candidate['remarks'],
                        'birthday' => $candidate['birthday'],
                        'age' => $candidate['age'],
                    ];

                    // Add validity window based on provided import years
                    $years = (int) $request->input('validity_years_import', 5);
                    $createData['valid_from'] = Carbon::now()->toDateString();
                    $createData['valid_to'] = Carbon::now()->addYears($years)->toDateString();

                    $new = PWDBeneficiary::create($createData);
                    $insertedRows++;
                    $insertedIds[] = $new->id;

                } catch (\Throwable $e) {
                    if (count($errors) < $maxErrors) {
                        $errors[] = "Row {$totalRows}: " . $e->getMessage();
                    }
                }
            }

            fclose($file);

            if ($insertedRows === 0 && $skippedRows > 0) {
                $msg = "No new records inserted. {$skippedRows} row(s) were exact duplicates and skipped.";
            } else {
                $msg = "Imported {$insertedRows} new record(s). Skipped {$skippedRows} exact-duplicate row(s).";
            }

            // Optionally store last import IDs for undo feature (if you maintain that metadata)
            if (!empty($insertedIds)) {
                // example: store as simple json file per barangay (non-blocking)
                try {
                    $metaDir = storage_path('app/pwd_import_meta');
                    if (!is_dir($metaDir)) @mkdir($metaDir, 0755, true);
                    file_put_contents($metaDir . "/last_import_{$barangayId}.json", json_encode([
                        'created_at' => now()->toDateTimeString(),
                        'inserted_ids' => $insertedIds,
                    ]));
                } catch (\Throwable $e) {
                    // swallow metadata errors; they are non-critical
                }
            }

            return $request->wantsJson()
                ? response()->json([
                    'success' => true,
                    'message' => $msg,
                    'totalRows' => $totalRows,
                    'insertedRows' => $insertedRows,
                    'skippedRows' => $skippedRows,
                    'errors' => $errors,
                    'insertedIds' => $insertedIds,
                ])
                : back()->with('success', $msg);

        } catch (\Throwable $e) {
            if (is_resource($file)) fclose($file);
            return $request->wantsJson()
                ? response()->json(['success' => false, 'error' => 'Error processing CSV: ' . $e->getMessage()], 500)
                : back()->with('error', 'Error processing CSV: ' . $e->getMessage());
        }
    }

    public function undoImport(Request $request)
    {
        $ids = $request->input('ids', []);
        $importHash = $request->input('import_batch_hash', null);
        $deleted = 0;

        // If import hash provided, read metadata file to get IDs
        if ($importHash && $request->filled('encrypted_barangay_id')) {
            try {
                $barangayId = Crypt::decrypt($request->input('encrypted_barangay_id'));
            } catch (\Throwable $e) {
                return response()->json(['success' => false, 'error' => 'Invalid barangay id.'], 400);
            }
            $metaPath = "imports/pwd/{$barangayId}/{$importHash}.json";
            if (!Storage::disk('local')->exists($metaPath)) {
                return response()->json(['success' => false, 'error' => 'Import batch not found.'], 404);
            }
            $meta = json_decode(Storage::disk('local')->get($metaPath), true);
            $ids = $meta['imported_ids'] ?? [];
        }

        if (!is_array($ids) || empty($ids)) {
            return response()->json(['success' => false, 'error' => 'No import IDs provided.'], 400);
        }

        $deleted = PWDBeneficiary::whereIn('id', $ids)->delete();

        // if import hash provided, also remove the metadata file
        if (isset($metaPath) && Storage::disk('local')->exists($metaPath)) {
            Storage::disk('local')->delete($metaPath);
        } else {
            // attempt to remove any metadata file that contains these ids (optional)
            // (skipped for performance; frontend can send import_batch_hash to explicitly delete)
        }

        return response()->json(['success' => true, 'deleted' => $deleted]);
    }

    /**
     * Return beneficiary JSON for modal editing
     */
    public function json(Request $request, $id)
    {
        $beneficiary = PWDBeneficiary::findOrFail($id);

        return response()->json([
            'id' => $beneficiary->id,
            'last_name' => $beneficiary->last_name,
            'first_name' => $beneficiary->first_name,
            'middle_name' => $beneficiary->middle_name,
            'gender' => $beneficiary->gender,
            'birthday' => $beneficiary->birthday ? Carbon::parse($beneficiary->birthday)->toDateString() : null,
            'age' => $beneficiary->age,
            'type_of_disability' => $beneficiary->type_of_disability,
            'pwd_id_number' => $beneficiary->pwd_id_number,
            'validity_years' => $beneficiary->validity_years,
            'remarks' => $beneficiary->remarks,
            'barangay_id' => $beneficiary->barangay_id,
        ]);
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="pwd_beneficiaries_template.csv"',
        ];

        // Columns expected by your importCsv() function
        $columns = [
            'Last Name',           // row[0]
            'First Name',          // row[1]
            'Middle Name',         // row[2]
            'Gender',        // row[3]
            'Type of Disability',  // row[4]
            'PWD ID Number',       // row[5]
            'Remarks',             // row[6]
            'Birthday (YYYY-MM-DD)',// row[7]
            'Age',                 // row[8]
        ];

        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');

            // Write header
            fputcsv($file, $columns);

            fputcsv($file, [
                'Dela Cruz',
                'Juan',
                'Santos',
                'M',
                'Visual Impairment',
                'PWD-12345',
                'Active',
                '1970-05-14',
                '55',
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function relatedSearch(Request $request)
    {
        $search = $request->input('search');
        $results = [];
        if ($search) {
            $query = PWDBeneficiary::with('barangay')
                ->limit(1000)
                ->get(['barangay_id','last_name','first_name','middle_name','gender','pwd_id_number','birthday','type_of_disability','remarks']);

            $filtered = [];
            foreach ($query as $r) {
                if (
                    stripos($r->pwd_id_number ?? '', $search) !== false ||
                    stripos($r->first_name ?? '', $search) !== false ||
                    stripos($r->last_name ?? '', $search) !== false ||
                    stripos($r->middle_name ?? '', $search) !== false
                ) {
                    $filtered[] = [
                        'last_name' => $r->last_name,
                        'first_name' => $r->first_name,
                        'pwd_id_number' => $r->pwd_id_number,
                        'gender' => $r->gender,
                        'barangay_name' => $r->barangay->barangay_name ?? 'N/A',
                    ];
                }
            }
            $results = $filtered;
        }
        return response()->json(['results' => $results]);
    }
}
