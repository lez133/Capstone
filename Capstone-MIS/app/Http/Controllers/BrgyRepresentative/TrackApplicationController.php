<?php

namespace App\Http\Controllers\BrgyRepresentative;

use App\Http\Controllers\Controller;
use App\Models\BeneficiaryDocument;
use App\Models\AidProgram;
use App\Models\Schedule;
use App\Models\Beneficiary;
use App\Models\AidProgramRequirementBeneficiary;

class TrackApplicationController extends Controller
{
    public function index()
    {
        $brgyRep = auth()->guard('brgyrep')->user();
        $barangayId = $brgyRep->barangay_id;

        // Get all aid programs with schedules for this barangay
        $schedules = Schedule::with('aidProgram')
            ->where('published', true)
            ->where(function ($q) use ($barangayId) {
                $q->whereJsonContains('barangay_ids', (string) $barangayId)
                  ->orWhereJsonContains('barangay_ids', $barangayId);
            })
            ->get();

        $aidPrograms = $schedules->pluck('aidProgram')->unique('id')->filter();

        // Get all beneficiaries in this barangay
        $beneficiaries = Beneficiary::where('barangay_id', $barangayId)->get();

        // Prepare data: for each program, list all beneficiaries and their progress
        $programsData = [];
        foreach ($aidPrograms as $program) {
            $programBeneficiaries = [];
            foreach ($beneficiaries as $ben) {
                // Calculate progress for this beneficiary and program
                $requirements = $program->requirements;
                $totalReq = $requirements->count();
                $validatedReq = AidProgramRequirementBeneficiary::where('beneficiary_id', $ben->id)
                    ->where('aid_program_id', $program->id)
                    ->where('status', 'Validated')
                    ->count();
                $progress = $totalReq > 0 ? round(($validatedReq / $totalReq) * 100) : 0;

                $programBeneficiaries[] = [
                    'beneficiary' => $ben,
                    'progress' => $progress,
                    // You can add more fields here (status, submitted docs, etc.)
                ];
            }
            $programsData[] = [
                'program' => $program,
                'beneficiaries' => $programBeneficiaries,
            ];
        }

        return view('content.brgyrepresentative-interface.track-application.track-applications', compact('programsData'));
    }

    public function download($documentId)
    {
        $document = \App\Models\BeneficiaryDocument::findOrFail($documentId);

        // Adjust path and filename logic as needed for your storage
        $filePath = storage_path('app/' . $document->file_path);
        $fileName = basename($filePath);

        if (!file_exists($filePath)) {
            return redirect()->back()->with('error', 'File not found.');
        }

        return response()->download($filePath, $fileName);
    }

    public function show($aidProgramId, $beneficiaryId)
    {
        $aidProgram = AidProgram::with('requirements')->findOrFail($aidProgramId);
        $beneficiary = \App\Models\Beneficiary::findOrFail($beneficiaryId);

        // Get requirement statuses and documents for this beneficiary/program
        $requirements = [];
        foreach ($aidProgram->requirements as $req) {
            $statusRow = AidProgramRequirementBeneficiary::where('beneficiary_id', $beneficiaryId)
                ->where('aid_program_id', $aidProgramId)
                ->where('requirement_id', $req->id)
                ->first();

            // Use the pivot table to get the document
            $document = null;
            if ($statusRow && $statusRow->beneficiary_document_id) {
                $document = BeneficiaryDocument::find($statusRow->beneficiary_document_id);
            }

            $requirements[] = [
                'requirement' => $req,
                'status' => $statusRow ? $statusRow->status : 'Not Submitted',
                'document' => $document,
            ];
        }

        return view('content.brgyrepresentative-interface.track-application.view-application', compact('aidProgram', 'beneficiary', 'requirements'));
    }

    public function review($aidProgramId, $beneficiaryId, $requirementId)
    {
        $statusRow = AidProgramRequirementBeneficiary::where('beneficiary_id', $beneficiaryId)
            ->where('aid_program_id', $aidProgramId)
            ->where('requirement_id', $requirementId)
            ->first();

        if ($statusRow) {
            $statusRow->status = 'Reviewed';
            $statusRow->save();
            return redirect()->back()->with('success', 'Marked as reviewed. Waiting for admin approval.');
        }

        return redirect()->back()->with('error', 'Requirement not found.');
    }
}
