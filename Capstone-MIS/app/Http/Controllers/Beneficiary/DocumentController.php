<?php

namespace App\Http\Controllers\Beneficiary;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\BeneficiaryDocument;
use App\Models\AidProgramRequirementBeneficiary;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\AidProgram;

class DocumentController extends Controller
{
    public function index()
    {
        $user = Auth::guard('beneficiary')->user();
        $documents = BeneficiaryDocument::where('beneficiary_id', $user->id)->get();

        // normalize and count statuses
        $validatedCount = $documents->filter(function ($d) {
            return strtolower(trim((string) $d->status)) === 'validated';
        })->count();

        $pendingCount = $documents->filter(function ($d) {
            $s = strtolower(trim((string) $d->status));
            return $s === 'pending review' || $s === 'pending';
        })->count();

        $actionRequiredCount = $documents->filter(function ($d) {
            $s = strtolower(trim((string) $d->status));
            return in_array($s, ['action required', 'rejected', 'action_required', 'require action']);
        })->count();

        $completion = $documents->count()
            ? intval(($validatedCount / $documents->count()) * 100)
            : 0;

        $stats = [
            'validated' => $validatedCount,
            'pending' => $pendingCount,
            'action_required' => $actionRequiredCount,
            'completion' => $completion,
        ];

        // attach associated aid program names + submission date (read from ARB.created_at) to each document
        try {
            $docIds = $documents->pluck('id')->filter()->values()->toArray();

            if (!empty($docIds)) {
                $arbs = AidProgramRequirementBeneficiary::whereIn('beneficiary_document_id', $docIds)
                    ->get()
                    ->groupBy('beneficiary_document_id');

                $allProgramIds = $arbs->flatten(1)->pluck('aid_program_id')->unique()->filter()->values()->toArray();

                $programNames = [];
                if (!empty($allProgramIds)) {
                    $programNames = AidProgram::whereIn('id', $allProgramIds)
                        ->pluck('aid_program_name', 'id')
                        ->toArray();
                }

                foreach ($documents as $doc) {
                    $assoc = [];
                    if (isset($arbs[$doc->id])) {
                        $ids = $arbs[$doc->id]->pluck('aid_program_id')->unique()->toArray();
                        foreach ($ids as $pid) {
                            $pname = $programNames[$pid] ?? null;
                            if ($pname) {
                                $arbForPid = $arbs[$doc->id]->firstWhere('aid_program_id', $pid);
                                $submittedAt = null;
                                if ($arbForPid && !empty($arbForPid->created_at)) {
                                    try {
                                        $submittedAt = Carbon::parse($arbForPid->created_at)->format('m/d/Y');
                                    } catch (\Throwable $e) {
                                        $submittedAt = null;
                                    }
                                }
                                // single string label (keeps UI unchanged): "Program Name (MM/DD/YYYY)" when date exists
                                $label = $pname;
                                if ($submittedAt) {
                                    $label .= " ({$submittedAt})";
                                }
                                $assoc[] = $label;
                            }
                        }
                    }
                    $doc->associated_programs = $assoc;
                }
            } else {
                foreach ($documents as $doc) {
                    $doc->associated_programs = [];
                }
            }
        } catch (\Throwable $e) {
            foreach ($documents as $doc) {
                $doc->associated_programs = [];
            }
        }

        return view('content.beneficiary-interface.document.documents', compact('documents', 'stats'));
    }

    public function submit(Request $request)
    {
        $request->validate([
            'document_type' => 'required|string|max:255',
            'document_file' => 'required|file|mimes:jpeg,png,jpg,pdf,doc,docx|max:5120',
        ]);

        $user = Auth::guard('beneficiary')->user();
        $file = $request->file('document_file');
        $filename = uniqid() . '.pdf';
        $storePath = 'documents/' . $filename;

        if (in_array($file->extension(), ['jpg', 'jpeg', 'png'])) {

            $imageData = base64_encode(file_get_contents($file->getPathname()));
            $imgType = $file->extension();
            $html = '<!doctype html><html><head><meta charset="utf-8"><style>
                @page { margin: 0.6in; }
                html,body { height:100%; margin:0; }
                body { display:flex; align-items:center; justify-content:center; }
                .wrap { max-width:100%; max-height:100%; display:flex; align-items:center; justify-content:center; }
                img { max-width:100%; max-height:100%; object-fit:contain; }
                </style></head><body><div class="wrap">
                <img src="data:image/' . $imgType . ';base64,' . $imageData . '" alt="document-image" />
                </div></body></html>';
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHtml($html)->setPaper('A4', 'portrait');
            Storage::put($storePath, $pdf->output());
        } elseif (in_array($file->extension(), ['doc', 'docx'])) {
            $wordPath = $file->storeAs('documents/tmp', uniqid() . '.' . $file->extension());
            $wordFullPath = storage_path('app/' . $wordPath);
            $pdfFullPath = storage_path('app/documents/' . $filename);
            exec("libreoffice --headless --convert-to pdf --outdir " . escapeshellarg(dirname($pdfFullPath)) . " " . escapeshellarg($wordFullPath));
            Storage::delete($wordPath);
            // Ensure resulting PDF exists at expected path (libreoffice writes by filename without uniqid in some envs)
            if (! Storage::exists($storePath) && file_exists($pdfFullPath)) {
                Storage::put($storePath, file_get_contents($pdfFullPath));
                @unlink($pdfFullPath);
            }
        } else {
            $file->storeAs('documents', $filename);
        }

        // If a document of same type already exists for this beneficiary, remove its file and update the record
        $existing = BeneficiaryDocument::where('beneficiary_id', $user->id)
            ->where('document_type', $request->document_type)
            ->first();

        if ($existing) {
            // delete old file if present
            if ($existing->file_path && Storage::exists($existing->file_path)) {
                try {
                    Storage::delete($existing->file_path);
                } catch (\Throwable $e) {
                    // swallow deletion error but continue to update record
                }
            }

            $existing->update([
                'file_path' => $storePath,
                'status' => 'Pending Review',
                'uploaded_at' => Carbon::now(),
            ]);
        } else {
            BeneficiaryDocument::create([
                'beneficiary_id' => $user->id,
                'document_type' => $request->document_type,
                'file_path' => $storePath,
                'status' => 'Pending Review',
                'uploaded_at' => Carbon::now(),
            ]);
        }

        return redirect()->back()->with('success', 'Document submitted successfully!');
    }

    public function download($id)
    {
        $document = BeneficiaryDocument::findOrFail($id);

        // Ensure only the owner can download
        if ($document->beneficiary_id !== Auth::guard('beneficiary')->id()) {
            abort(403);
        }

        $path = $document->file_path;
        $candidateDisks = array_values(array_unique([config('filesystems.default'), 'public', 'local']));
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        foreach ($candidateDisks as $diskName) {
            try {
                $disk = Storage::disk($diskName);
                if (! $disk->exists($path)) {
                    continue;
                }

                // Images: convert to PDF on-the-fly and return PDF download
                if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                    $contents = null;
                    if (method_exists($disk, 'path')) {
                        $fullPath = $disk->path($path);
                        $contents = @file_get_contents($fullPath);
                    } elseif (method_exists($disk, 'readStream')) {
                        $stream = $disk->readStream($path);
                        if ($stream !== false) {
                            $contents = stream_get_contents($stream);
                            fclose($stream);
                        }
                    }

                    if (empty($contents)) {
                        continue;
                    }

                    // detect mime for image
                    $mime = 'image/' . $ext;
                    if (function_exists('finfo_open')) {
                        try {
                            $finfo = finfo_open(FILEINFO_MIME_TYPE);
                            $m = finfo_buffer($finfo, $contents);
                            finfo_close($finfo);
                            if ($m) {
                                $mime = $m;
                            }
                        } catch (\Throwable $e) { /* ignore */ }
                    }

                    $base64 = base64_encode($contents);
                    $html = '<!doctype html><html><head><meta charset="utf-8"><style>body{margin:0;padding:0;}img{display:block;margin:0 auto;max-width:100%;height:auto;}</style></head><body><img src="data:' . $mime . ';base64,' . $base64 . '"/></body></html>';
                    $pdf = Pdf::loadHtml($html)->setPaper('A4', 'portrait');
                    $pdfOutput = $pdf->output();

                    $downloadName = $document->document_type . '.pdf';
                    return response($pdfOutput, 200, [
                        'Content-Type' => 'application/pdf',
                        'Content-Disposition' => 'attachment; filename="' . $downloadName . '"',
                    ]);
                }

                // If already a PDF: serve it safely (local path preferred, else stream)
                if ($ext === 'pdf') {
                    if (method_exists($disk, 'path')) {
                        $fullPath = $disk->path($path);
                        if (file_exists($fullPath)) {
                            return response()->download($fullPath, $document->document_type . '.pdf');
                        }
                    }

                    if (method_exists($disk, 'readStream')) {
                        $stream = $disk->readStream($path);
                        if ($stream !== false) {
                            return response()->stream(function () use ($stream) {
                                fpassthru($stream);
                                fclose($stream);
                            }, 200, [
                                'Content-Type' => 'application/pdf',
                                'Content-Disposition' => 'attachment; filename="' . $document->document_type . '.pdf"',
                            ]);
                        }
                    }
                }

                // DOC/DOCX: attempt local libreoffice conversion (only when disk provides local path)
                if (in_array($ext, ['doc', 'docx']) && method_exists($disk, 'path')) {
                    $fullPath = $disk->path($path);
                    if (file_exists($fullPath)) {
                        $tmpDir = sys_get_temp_dir();
                        $tmpPdf = $tmpDir . '/' . uniqid('conv_') . '.pdf';
                        // LibreOffice typically writes PDF in same dir as source; use temp output dir for safety
                        $cmd = 'libreoffice --headless --convert-to pdf --outdir ' . escapeshellarg($tmpDir) . ' ' . escapeshellarg($fullPath) . ' 2>&1';
                        @exec($cmd, $out, $rc);
                        // expected filename created by libreoffice
                        $expected = $tmpDir . '/' . pathinfo($fullPath, PATHINFO_FILENAME) . '.pdf';
                        if (!file_exists($expected) && file_exists($tmpPdf)) {
                            $expected = $tmpPdf;
                        }
                        if (file_exists($expected)) {
                            $pdfContents = @file_get_contents($expected);
                            @unlink($expected);
                            if ($pdfContents !== false) {
                                return response($pdfContents, 200, [
                                    'Content-Type' => 'application/pdf',
                                    'Content-Disposition' => 'attachment; filename="' . $document->document_type . '.pdf"',
                                ]);
                            }
                        }
                    }
                }

                // Fallback: redirect to url if available (S3/public)
                if (is_callable([$disk, 'url'])) {
                    try {
                        $url = call_user_func([$disk, 'url'], $path);
                        if ($url) {
                            return redirect()->away($url);
                        }
                    } catch (\Throwable $e) {
                        // ignore and continue
                    }
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

        abort(404, 'Document file not found.');
    }

    public function view($id)
    {
        $document = BeneficiaryDocument::findOrFail($id);

        if ($document->beneficiary_id !== auth()->guard('beneficiary')->id()) {
            abort(403);
        }

        $path = $document->file_path;
        $candidateDisks = array_values(array_unique([config('filesystems.default'), 'public', 'local']));

        foreach ($candidateDisks as $diskName) {
            try {
                $disk = \Illuminate\Support\Facades\Storage::disk($diskName);

                if (! $disk->exists($path)) {
                    continue;
                }

                // serve local filesystem files inline
                if (method_exists($disk, 'path')) {
                    $fullPath = $disk->path($path);
                    if (file_exists($fullPath)) {
                        $mime = null;

                        // Prefer finfo for reliable MIME detection on local files, fallback to mime_content_type
                        if (function_exists('finfo_open')) {
                            try {
                                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                                if ($finfo !== false) {
                                    $mime = finfo_file($finfo, $fullPath) ?: null;
                                    finfo_close($finfo);
                                }
                            } catch (\Throwable $e) {
                                // fallback below
                                $mime = null;
                            }
                        }

                        if (empty($mime)) {
                            $mime = @mime_content_type($fullPath) ?: null;
                        }

                        $mime = $mime ?: 'application/octet-stream';

                        return response()->file($fullPath, [
                            'Content-Type' => $mime,
                            'Content-Disposition' => 'inline; filename="'.basename($path).'"',
                        ]);
                    }
                }

                // redirect to public/remote URL (S3, public disk)
                if (is_object($disk) && is_callable([$disk, 'url'])) {
                    try {
                        $url = call_user_func([$disk, 'url'], $path);
                        if ($url) {
                            return redirect()->away($url);
                        }
                    } catch (\Throwable $e) {
                        // ignore and continue to other methods
                    }
                }

                // fallback: stream from disk
                if (method_exists($disk, 'readStream')) {
                    $stream = $disk->readStream($path);
                    if ($stream !== false) {
                        $mime = null;

                        // Prefer calling mimeType if the disk exposes it; use is_callable + call_user_func
                        // to avoid static analysis / undefined-method errors while remaining safe at runtime.
                        if (is_object($disk) && is_callable([$disk, 'mimeType'])) {
                            try {
                                $mime = call_user_func([$disk, 'mimeType'], $path);
                            } catch (\Throwable $e) {
                                // ignore and fall back
                                $mime = null;
                            }
                        }

                        // Fallback: try to detect MIME from a stream URI using finfo if available
                        if (empty($mime) && function_exists('finfo_open')) {
                            try {
                                $meta = stream_get_meta_data($stream);
                                if (!empty($meta['uri']) && file_exists($meta['uri'])) {
                                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                                    if ($finfo !== false) {
                                        $detected = finfo_file($finfo, $meta['uri']);
                                        finfo_close($finfo);
                                        if ($detected) {
                                            $mime = $detected;
                                        }
                                    }
                                }
                            } catch (\Throwable $e) {
                                // ignore and fall back
                            }
                        }

                        // Last resort fallback
                        $mime = $mime ?? 'application/octet-stream';

                        return response()->stream(function() use ($stream) {
                            fpassthru($stream);
                            fclose($stream);
                        }, 200, [
                            'Content-Type' => $mime,
                            'Content-Disposition' => 'inline; filename="'.basename($path).'"',
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

        abort(404, 'Document not found.');
    }

    /**
     * Delete a beneficiary document and its storage file (retract).
     * Also clears any AidProgramRequirementBeneficiary references and recalculates progress.
     */
    public function destroy($id)
    {
        $document = BeneficiaryDocument::findOrFail($id);

        if ($document->beneficiary_id !== Auth::guard('beneficiary')->id()) {
            abort(403);
        }

        $userId = $document->beneficiary_id;

        // 1) Find ARB records that reference this document and clear them
        try {
            $affectedArbs = AidProgramRequirementBeneficiary::where('beneficiary_document_id', $document->id)->get();
        } catch (\Throwable $e) {
            $affectedArbs = collect();
        }

        $affectedProgramIds = [];
        foreach ($affectedArbs as $arb) {
            $affectedProgramIds[] = $arb->aid_program_id;
            $arb->beneficiary_document_id = null;
            // If your ARB has a status column, reset it safely
            if (Schema::hasColumn($arb->getTable(), 'status')) {
                $arb->status = 'Not Submitted';
            }
            try {
                $arb->save();
            } catch (\Throwable $e) {
                // continue even if one save fails
            }
        }
        $affectedProgramIds = array_values(array_unique($affectedProgramIds));

        // 2) Recalculate progress for affected aid programs for this beneficiary
        foreach ($affectedProgramIds as $aidProgramId) {
            try {
                $totalRequirements = AidProgramRequirementBeneficiary::where('beneficiary_id', $userId)
                    ->where('aid_program_id', $aidProgramId)
                    ->count();

                $submittedCount = AidProgramRequirementBeneficiary::where('beneficiary_id', $userId)
                    ->where('aid_program_id', $aidProgramId)
                    ->whereNotNull('beneficiary_document_id')
                    ->count();

                $progress = $totalRequirements ? intval(($submittedCount / $totalRequirements) * 100) : 0;
            } catch (\Throwable $e) {
                $progress = 0;
            }

            // Persist progress into likely application tables if they exist (safe checks)
            $candidates = [
                'applications',
                'aid_applications',
                'beneficiary_applications',
                'program_applications',
            ];
            foreach ($candidates as $table) {
                if (Schema::hasTable($table)
                    && Schema::hasColumn($table, 'beneficiary_id')
                    && Schema::hasColumn($table, 'aid_program_id')
                    && Schema::hasColumn($table, 'progress')) {
                    try {
                        DB::table($table)
                            ->where('beneficiary_id', $userId)
                            ->where('aid_program_id', $aidProgramId)
                            ->update(['progress' => $progress]);
                    } catch (\Throwable $e) {
                        // ignore and continue
                    }
                }
            }
        }

        // 3) delete physical file if exists
        if ($document->file_path && Storage::exists($document->file_path)) {
            try {
                Storage::delete($document->file_path);
            } catch (\Throwable $e) {
                // ignore deletion errors
            }
        }

        // 4) remove DB record
        try {
            $document->delete();
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Unable to remove document record. Please contact support.');
        }

        return redirect()->back()->with('success', 'Document cancelled, file removed, and progress recalculated.');
    }
}
