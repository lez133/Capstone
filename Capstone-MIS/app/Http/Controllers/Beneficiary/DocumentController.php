<?php

namespace App\Http\Controllers\Beneficiary;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\BeneficiaryDocument;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class DocumentController extends Controller
{
    public function index()
    {
        $user = Auth::guard('beneficiary')->user();
        $documents = BeneficiaryDocument::where('beneficiary_id', $user->id)->get();

        $stats = [
            'validated' => $documents->where('status', 'Validated')->count(),
            'pending' => $documents->where('status', 'Pending Review')->count(),
            'action_required' => $documents->where('status', 'Action Required')->count(),
            'completion' => $documents->count() ? intval(($documents->where('status', 'Validated')->count() / $documents->count()) * 100) : 0,
        ];

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

        // Convert to PDF if not already PDF
        if (in_array($file->extension(), ['jpg', 'jpeg', 'png'])) {
            // Convert image to PDF using DomPDF
            $imageData = base64_encode(file_get_contents($file->getPathname()));
            $imgType = $file->extension();
            $html = '<img src="data:image/' . $imgType . ';base64,' . $imageData . '" style="width:100%;max-width:600px;">';
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHtml($html)->setPaper('A4');
            Storage::put('documents/' . $filename, $pdf->output());
        } elseif (in_array($file->extension(), ['doc', 'docx'])) {
            // Convert Word to PDF using LibreOffice (requires libreoffice installed on server)
            $wordPath = $file->storeAs('documents/tmp', uniqid() . '.' . $file->extension());
            $wordFullPath = storage_path('app/' . $wordPath);
            $pdfFullPath = storage_path('app/documents/' . $filename);
            // Convert using shell command (Linux only)
            exec("libreoffice --headless --convert-to pdf --outdir " . escapeshellarg(dirname($pdfFullPath)) . " " . escapeshellarg($wordFullPath));
            // Remove temp word file
            Storage::delete($wordPath);
        } else {
            // If already PDF, just store
            $file->storeAs('documents', $filename);
        }

        BeneficiaryDocument::create([
            'beneficiary_id' => $user->id,
            'document_type' => $request->document_type,
            'file_path' => 'documents/' . $filename,
            'status' => 'Pending Review',
            'uploaded_at' => Carbon::now(),
        ]);

        return redirect()->back()->with('success', 'Document submitted successfully!');
    }

    public function download($id)
    {
        $document = BeneficiaryDocument::findOrFail($id);

        // Ensure only the owner can download
        if ($document->beneficiary_id !== Auth::guard('beneficiary')->id()) {
            abort(403);
        }

        return Storage::download($document->file_path, $document->document_type . '.pdf');
    }
}
