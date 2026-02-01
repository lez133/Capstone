<?php

namespace App\Http\Controllers\BrgyRepresentative;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Beneficiary;
use App\Models\BeneficiaryDocument;
use App\Models\Requirement;
use App\Models\AidProgram;
use App\Models\Schedule;
use App\Models\AidProgramRequirementBeneficiary;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


class SubmitDocumentController extends Controller
{
    private const ALLOWED_ROLE = 'Barangay Representative';
    private const ALLOWED_MIMES = ['jpeg', 'png', 'jpg', 'pdf', 'doc', 'docx'];
    private const MAX_FILE_SIZE = 5120; // KB

    private function isAuthorizedRep($rep): bool
    {
        $barangayId = (int) ($rep->barangay_id ?? 0);
        return $rep && $rep->role === self::ALLOWED_ROLE && $barangayId > 0;
    }

    public function create(Request $request)
    {
        $rep = Auth::guard('brgyrep')->user();
        if (!$rep || !$this->isAuthorizedRep($rep)) abort(403, 'Unauthorized');

        $barangayId = (int) ($rep->barangay_id ?? 0);
        if ($barangayId <= 0) abort(500, 'Representative has no assigned barangay');

        $beneficiariesTable = (new Beneficiary)->getTable();
        $hasVerifiedCol = Schema::hasColumn($beneficiariesTable, 'verified');
        $hasVerifiedAtCol = Schema::hasColumn($beneficiariesTable, 'verified_at');

        $applyVerifiedFilter = function ($query) use ($hasVerifiedCol, $hasVerifiedAtCol) {
            if ($hasVerifiedCol) {
                $query->where(function ($q) {
                    $q->where('verified', true)->orWhere('verified', 1);
                });
            } elseif ($hasVerifiedAtCol) {
                $query->whereNotNull('verified_at');
            }
        };

        $seniorCitizens = Beneficiary::where('barangay_id', $barangayId)
            ->where('beneficiary_type', 'Senior Citizen')
            ->when(true, $applyVerifiedFilter)
            ->orderBy('last_name')
            ->get();

        $pwdList = Beneficiary::where('barangay_id', $barangayId)
            ->where('beneficiary_type', 'PWD')
            ->when(true, $applyVerifiedFilter)
            ->orderBy('last_name')
            ->get();

        $aidProgramTable = (new AidProgram)->getTable();
        $aidQuery = AidProgram::query();

        $today = Carbon::today()->toDateString();

        // Only programs with status 'ongoing' and not past end_date
        if (Schema::hasColumn($aidProgramTable, 'status')) {
            $aidQuery->whereRaw("LOWER(`status`) = ?", ['ongoing']);
        }
        if (Schema::hasColumn($aidProgramTable, 'end_date')) {
            $aidQuery->whereDate('end_date', '>=', $today);
        }
        if (Schema::hasColumn($aidProgramTable, 'start_date')) {
            $aidQuery->whereDate('start_date', '<=', $today);
        }

        // Filter by barangay
        if (Schema::hasColumn($aidProgramTable, 'barangay_ids')) {
            $aidQuery->where(function ($q) use ($barangayId) {
                $q->whereJsonContains('barangay_ids', (string)$barangayId)
                  ->orWhereJsonContains('barangay_ids', $barangayId);
            });
        } elseif (Schema::hasColumn($aidProgramTable, 'barangay_id')) {
            $aidQuery->where('barangay_id', $barangayId);
        }

        $nameCol = collect(['aid_program_name', 'name', 'program_name', 'title'])
            ->first(fn($c) => Schema::hasColumn($aidProgramTable, $c)) ?? 'id';
        if (Schema::hasColumn($aidProgramTable, $nameCol)) {
            $aidQuery->orderBy($nameCol);
        }

        $aidPrograms = $aidQuery->pluck($nameCol, 'id')->toArray();

        if ($request->filled('aid_type') && is_numeric($request->aid_type)) {
            $selectedId = (int)$request->aid_type;
            if (!array_key_exists($selectedId, $aidPrograms)) {
                $sel = AidProgram::find($selectedId);
                if ($sel) {
                    $label = Schema::hasColumn($aidProgramTable, $nameCol)
                        ? $sel->{$nameCol}
                        : ($sel->aid_program_name ?? $sel->name ?? 'Unknown');
                    $aidPrograms = array_merge([$selectedId => $label], $aidPrograms);
                }
            }
        }

        $aidProgramId = $request->filled('aid_type') ? (int)$request->aid_type : null;
        $documentTypes = [];

        if ($aidProgramId) {
            $documentTypes = Requirement::query()
                ->join('aid_program_requirement', 'requirements.id', '=', 'aid_program_requirement.requirement_id')
                ->where('aid_program_requirement.aid_program_id', $aidProgramId)
                ->orderBy('requirements.document_requirement')
                ->pluck('requirements.document_requirement', 'requirements.id')
                ->toArray();
        }

        $stats = [
            'total' => BeneficiaryDocument::whereHas('beneficiary', fn($q) => $q->where('barangay_id', $barangayId))->count(),
            'pending' => BeneficiaryDocument::whereHas('beneficiary', fn($q) => $q->where('barangay_id', $barangayId))->whereRaw('LOWER(status) LIKE ?', ['%pending%'])->count(),
            'in_progress' => BeneficiaryDocument::whereHas('beneficiary', fn($q) => $q->where('barangay_id', $barangayId))->whereRaw('LOWER(status) LIKE ?', ['%progress%'])->count(),
            'completed' => BeneficiaryDocument::whereHas('beneficiary', fn($q) => $q->where('barangay_id', $barangayId))->whereRaw('LOWER(status) = ?', ['completed'])->count(),
        ];

        $recentDocuments = BeneficiaryDocument::with('beneficiary')
            ->whereHas('beneficiary', fn($q) => $q->where('barangay_id', $barangayId))
            ->latest()
            ->take(10)
            ->get();


        return view('content.brgyrepresentative-interface.submit-aid-request.submit-aid-requests', compact(
            'seniorCitizens', 'pwdList', 'documentTypes', 'aidPrograms', 'stats', 'recentDocuments'
        ));
    }

    public function store(Request $request)
    {
        $rep = Auth::guard('brgyrep')->user();
        if (!$rep || !$this->isAuthorizedRep($rep)) {
            Log::warning('Unauthorized access attempt to submit-document.store', [
                'user_id' => Auth::id(),
                'guard' => 'brgyrep'
            ]);
            abort(403, 'Unauthorized');
        }

        $barangayId = (int) ($rep->barangay_id ?? 0);
        if ($barangayId <= 0) {
            Log::error('Representative missing valid barangay_id on store', ['rep_id' => $rep->id]);
            abort(500, 'Configuration error');
        }

        $validated = $request->validate([
            'beneficiary_id' => 'required|integer|exists:beneficiaries,id',
            'requirement' => 'required|integer|exists:requirements,id',
            'aid_type' => 'required|integer|exists:aid_programs,id',
            'document_file' => 'required|file|mimes:' . implode(',', self::ALLOWED_MIMES) . '|max:' . self::MAX_FILE_SIZE,
        ]);

        try {
            $beneficiary = Beneficiary::findOrFail($validated['beneficiary_id']);
            if ((int)$beneficiary->barangay_id !== $barangayId) {
                Log::warning('Unauthorized document submission attempt (barangay mismatch)', [
                    'rep_barangay_id' => $barangayId,
                    'beneficiary_barangay_id' => $beneficiary->barangay_id,
                    'rep_id' => $rep->id,
                    'beneficiary_id' => $beneficiary->id
                ]);
                return back()->withErrors([
                    'error' => 'You can only submit documents for beneficiaries in your assigned barangay.'
                ])->withInput();
            }

            $beneficiariesTable = (new Beneficiary)->getTable();
            $hasVerifiedCol = Schema::hasColumn($beneficiariesTable, 'verified');
            $hasVerifiedAtCol = Schema::hasColumn($beneficiariesTable, 'verified_at');
            $isVerified = $hasVerifiedCol ? (bool) ($beneficiary->verified ?? false)
                : ($hasVerifiedAtCol ? !empty($beneficiary->verified_at) : true);

            if (!$isVerified) {
                return back()->withErrors([
                    'error' => 'This beneficiary has not been verified yet.'
                ])->withInput();
            }

            $aidProgram = AidProgram::findOrFail($validated['aid_type']);
            $file = $request->file('document_file');
            if (!$file || !$file->isValid()) {
                return back()->withErrors([
                    'document_file' => 'File upload failed. Please try again.'
                ])->withInput();
            }

            $filename = uniqid('doc_', true) . '_' . hash('sha256', $file->getClientOriginalName()) . '.' . $file->getClientOriginalExtension();
            try {
                $filePath = $file->storeAs('beneficiary_documents', $filename, 'private');
                if (!$filePath) throw new \Exception('File storage returned null');
            } catch (\Throwable $e) {
                Log::error('File storage failed', [
                    'error' => $e->getMessage(),
                    'rep_id' => $rep->id,
                    'beneficiary_id' => $beneficiary->id
                ]);
                return back()->withErrors([
                    'document_file' => 'Failed to store file. Please try again.'
                ])->withInput();
            }

            $requirementModel = Requirement::findOrFail($validated['requirement']);
            $doc = BeneficiaryDocument::create([
                'beneficiary_id' => $beneficiary->id,
                'document_type' => $requirementModel->document_requirement,
                'file_path' => $filePath,
                'status' => 'Pending Review',
                'uploaded_at' => now(),
                'active' => true,
                'assisted_by' => $rep->id,
            ]);

            try {
                AidProgramRequirementBeneficiary::create([
                    'beneficiary_id' => $beneficiary->id,
                    'barangay_id' => $barangayId,
                    'aid_program_id' => $aidProgram->id,
                    'requirement_id' => $validated['requirement'],
                    'beneficiary_document_id' => $doc->id,
                    'status' => 'Submitted',
                    'assisted_by' => $rep->id,
                ]);
            } catch (\Throwable $e) {
                Log::warning('Failed to create ARB link for submitted document: '.$e->getMessage(), [
                    'doc_id' => $doc->id,
                    'beneficiary_id' => $beneficiary->id
                ]);
            }

            Log::info('Document submitted successfully', [
                'rep_id' => $rep->id,
                'beneficiary_id' => $beneficiary->id,
                'requirement_id' => $validated['requirement'],
                'requirement' => $requirementModel->document_requirement,
                'aid_program' => $aidProgram->aid_program_name
            ]);

            return redirect()
                ->route('brgyrep.submit-document.create')
                ->with('success', "Document requirement submitted successfully!");

        } catch (\Throwable $e) {
            dd($e->getMessage(), $e->getTraceAsString());
            Log::error('Unexpected error in document submission', [
                'error' => $e->getMessage(),
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors([
                'error' => 'An unexpected error occurred. Please try again.'
            ])->withInput();
        }
    }

    public function show($id)
    {
        $rep = Auth::guard('brgyrep')->user();
        if (!$rep || !$this->isAuthorizedRep($rep)) abort(403, 'Unauthorized');

        $barangayId = (int) ($rep->barangay_id ?? 0);
        if ($barangayId <= 0) abort(500, 'Configuration error');

        $document = BeneficiaryDocument::with('beneficiary')->findOrFail($id);
        if (!$document->beneficiary || (int)$document->beneficiary->barangay_id !== $barangayId) abort(403, 'Unauthorized');

        return view('content.brgyrepresentative-interface.submit-aid-request.show', compact('document'));
    }

    public function viewSubmittedDocuments($id)
    {
        $rep = Auth::guard('brgyrep')->user();
        if (!$rep || !$this->isAuthorizedRep($rep)) abort(403, 'Unauthorized');

        $barangayId = (int) ($rep->barangay_id ?? 0);
        if ($barangayId <= 0) abort(500, 'Configuration error');

        $beneficiary = Beneficiary::findOrFail($id);
        $documents = BeneficiaryDocument::where('beneficiary_id', $id)->with('beneficiary')->get();

        return view('content.brgyrepresentative-interface.submit-aid-request.view-submitted-documents', compact('beneficiary', 'documents'));
    }

    public function viewDocument(Request $request, $id)
    {
        $rep = Auth::guard('brgyrep')->user();
        if (!$rep || !$this->isAuthorizedRep($rep)) abort(403, 'Unauthorized');

        $document = BeneficiaryDocument::with('beneficiary')->findOrFail($id);

        if (!$document->beneficiary || (int)$document->beneficiary->barangay_id !== (int)$rep->barangay_id) {
            abort(403, 'Unauthorized');
        }

        $filePath = $document->file_path ?? $document->document_file;
        $disk = Storage::disk('private')->exists($filePath) ? 'private' : (Storage::disk('public')->exists($filePath) ? 'public' : null);

        if (!$filePath || !$disk) {
            abort(404, 'File not found');
        }

        $fullPath = Storage::disk($disk)->path($filePath);
        // Determine mime type using file path to avoid calling undefined Storage::mimeType in some environments
        if (function_exists('mime_content_type')) {
            $mime = mime_content_type($fullPath);
        } else {
            try {
                $mime = (new \Symfony\Component\HttpFoundation\File\File($fullPath))->getMimeType();
            } catch (\Throwable $e) {
                $mime = 'application/octet-stream';
            }
        }

        return response()->file($fullPath, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . basename($filePath) . '"'
        ]);
    }

    public function markAsReviewed($id)
    {
        $rep = Auth::guard('brgyrep')->user();
        if (!$rep || !$this->isAuthorizedRep($rep)) abort(403, 'Unauthorized');

        $document = BeneficiaryDocument::with('beneficiary')->findOrFail($id);

        if (!$document->beneficiary || (int)$document->beneficiary->barangay_id !== (int)$rep->barangay_id) {
            abort(403, 'Unauthorized');
        }

        // mark status and record the rep who reviewed it
        $document->status = 'reviewed, waiting for admin approval';
        $document->assisted_by = $rep->id;
        $document->save();

        // also update the related AidProgramRequirementBeneficiary.assisted_by if present
        try {
            $arb = AidProgramRequirementBeneficiary::where('beneficiary_document_id', $document->id)->first();
            if ($arb) {
                $arb->assisted_by = $rep->id;
                $arb->save();
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to update ARB assisted_by on review: '.$e->getMessage(), [
                'doc_id' => $document->id,
                'rep_id' => $rep->id
            ]);
        }

        return back()->with('success', 'Document marked as reviewed and waiting for admin approval.');
    }

    public function getRequirements(Request $request)
    {
        $rep = Auth::guard('brgyrep')->user();
        if (!$rep || !$this->isAuthorizedRep($rep)) return response()->json(['error' => 'Unauthorized'], 403);

        $aidProgramId = (int) $request->input('aid_type');
        if (!$aidProgramId) return response()->json(['error' => 'Invalid aid program'], 400);

        $documentTypes = Requirement::query()
            ->join('aid_program_requirement', 'requirements.id', '=', 'aid_program_requirement.requirement_id')
            ->where('aid_program_requirement.aid_program_id', $aidProgramId)
            ->orderBy('requirements.document_requirement')
            ->pluck('requirements.document_requirement', 'requirements.id')
            ->toArray();

        return response()->json(['requirements' => $documentTypes]);
    }
}
