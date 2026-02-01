<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Http\Request;
use App\Models\SeniorCitizenBeneficiary;
use App\Models\Barangay;
use App\Models\AidProgram;
use App\Models\Beneficiary;
use App\Models\PWDBeneficiary;
use Illuminate\Support\Facades\Crypt;

class MswdDashboardController extends Controller
{
    public function index()
    {
        // Fetch total counts
        $totalSenior = SeniorCitizenBeneficiary::count();
        $totalPwd = PWDBeneficiary::count();
        $totalBeneficiaries = $totalSenior + $totalPwd;
        $totalBarangays = Barangay::count();
        $totalAidPrograms = AidProgram::count();

        // Fetch recent beneficiaries (limit to 5)
        $recentBeneficiaries = SeniorCitizenBeneficiary::with('barangay')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Fetch all aid programs
        $aidPrograms = AidProgram::orderBy('aid_program_name')->get();

        // Fetch verified registered accounts
        $totalVerifiedRegistered = Beneficiary::where('verified', true)->count();

        // Fetch unverified registered accounts
        $totalUnverifiedRegistered = Beneficiary::where('verified', false)->count();

        // Recent PWD Beneficiaries (last 5)
        $recentPWDBeneficiaries = PWDBeneficiary::with('barangay')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Recent Senior Citizen Beneficiaries (last 5)
        $recentSeniorBeneficiaries = SeniorCitizenBeneficiary::with('barangay')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Recent Registered Beneficiaries (last 5, verified)
        $recentVerifiedRegistered = Beneficiary::with('barangay')
            ->where('verified', true)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Recent Registered Beneficiaries (last 5, unverified)
        $recentUnverifiedRegistered = Beneficiary::with('barangay')
            ->where('verified', false)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('content.admin-interface.dashboard.mswd-interface', compact(
            'totalBeneficiaries',
            'totalBarangays',
            'totalAidPrograms',
            'recentBeneficiaries',
            'aidPrograms',
            'totalVerifiedRegistered',
            'totalUnverifiedRegistered',
            'recentPWDBeneficiaries',
            'recentSeniorBeneficiaries',
            'recentVerifiedRegistered',
            'recentUnverifiedRegistered'
        ));
    }

    public function totalBeneficiaries(Request $request)
    {
        $allBarangays = Barangay::orderBy('barangay_name')->get();

        $scRemarks = SeniorCitizenBeneficiary::pluck('remarks')->filter();
        $pwdRemarks = PWDBeneficiary::pluck('remarks')->filter();
        $allRemarks = $scRemarks->merge($pwdRemarks)->unique()->values();

        // Normalize gender filter
        $gender = $request->gender;
        if ($gender === 'Male') $gender = 'M';
        if ($gender === 'Female') $gender = 'F';

        // Only query the selected type
        $beneficiaries = collect();
        if ($request->type == 'Senior Citizen') {
            $beneficiaries = SeniorCitizenBeneficiary::with('barangay')
                ->when($request->barangay, fn($q) => $q->whereHas('barangay', fn($q2) => $q2->where('barangay_name', $request->barangay)))
                ->when($request->gender, fn($q) => $q->where(function($q2) use ($request, $gender) {
                    $q2->where('gender', $request->gender)
                       ->orWhere('gender', $gender);
                }))
                ->when($request->remarks, fn($q) => $q->where('remarks', $request->remarks))
                ->when($request->search, fn($q) => $q->where(function($q2) use ($request) {
                    $q2->where('last_name', 'like', '%'.$request->search.'%')
                       ->orWhere('first_name', 'like', '%'.$request->search.'%')
                       ->orWhere('middle_name', 'like', '%'.$request->search.'%')
                       ->orWhere('osca_number', 'like', '%'.$request->search.'%');
                }))
                ->get();
        } elseif ($request->type == 'PWD') {
            $beneficiaries = PWDBeneficiary::with('barangay')
                ->when($request->barangay, fn($q) => $q->whereHas('barangay', fn($q2) => $q2->where('barangay_name', $request->barangay)))
                ->when($request->gender, fn($q) => $q->where(function($q2) use ($request, $gender) {
                    $q2->where('gender', $request->gender)
                       ->orWhere('gender', $gender);
                }))
                ->when($request->remarks, fn($q) => $q->where('remarks', $request->remarks))
                ->when($request->search, fn($q) => $q->where(function($q2) use ($request) {
                    $q2->where('last_name', 'like', '%'.$request->search.'%')
                       ->orWhere('first_name', 'like', '%'.$request->search.'%')
                       ->orWhere('middle_name', 'like', '%'.$request->search.'%')
                       ->orWhere('pwd_id_number', 'like', '%'.$request->search.'%');
                }))
                ->get();
        } else {
            // No type selected: merge both
            $seniors = SeniorCitizenBeneficiary::with('barangay')
                ->when($request->barangay, fn($q) => $q->whereHas('barangay', fn($q2) => $q2->where('barangay_name', $request->barangay)))
                ->when($request->gender, fn($q) => $q->where(function($q2) use ($request, $gender) {
                    $q2->where('gender', $request->gender)
                       ->orWhere('gender', $gender);
                }))
                ->when($request->remarks, fn($q) => $q->where('remarks', $request->remarks))
                ->when($request->search, fn($q) => $q->where(function($q2) use ($request) {
                    $q2->where('last_name', 'like', '%'.$request->search.'%')
                       ->orWhere('first_name', 'like', '%'.$request->search.'%')
                       ->orWhere('middle_name', 'like', '%'.$request->search.'%')
                       ->orWhere('osca_number', 'like', '%'.$request->search.'%');
                }))
                ->get();

            $pwds = PWDBeneficiary::with('barangay')
                ->when($request->barangay, fn($q) => $q->whereHas('barangay', fn($q2) => $q2->where('barangay_name', $request->barangay)))
                ->when($request->gender, fn($q) => $q->where(function($q2) use ($request, $gender) {
                    $q2->where('gender', $request->gender)
                       ->orWhere('gender', $gender);
                }))
                ->when($request->remarks, fn($q) => $q->where('remarks', $request->remarks))
                ->when($request->search, fn($q) => $q->where(function($q2) use ($request) {
                    $q2->where('last_name', 'like', '%'.$request->search.'%')
                       ->orWhere('first_name', 'like', '%'.$request->search.'%')
                       ->orWhere('middle_name', 'like', '%'.$request->search.'%')
                       ->orWhere('pwd_id_number', 'like', '%'.$request->search.'%');
                }))
                ->get();

            $beneficiaries = $seniors->merge($pwds)->sortBy('last_name')->values();
        }

        // Paginate manually
        $perPage = 20;
        $page = $request->input('page', 1);
        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $beneficiaries->forPage($page, $perPage),
            $beneficiaries->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('content.admin-interface.dashboard.total-beneficiaries', compact(
            'paginated',
            'allBarangays',
            'allRemarks'
        ));
    }


    public function verifiedBeneficiaries()
    {
        $verifiedBeneficiaries = Beneficiary::with('barangay')->where('verified', true)->get();

        return view('content.admin-interface.dashboard.verified-benficiaries', compact('verifiedBeneficiaries'));
    }

    public function unverifiedBeneficiaries()
    {
        $unverifiedBeneficiaries = Beneficiary::with('barangay')->where('verified', false)->get();
        $verifiedBeneficiaries = Beneficiary::with('barangay')->where('verified', true)->get();

        return view('content.admin-interface.dashboard.unverified-beneficiaries', compact('unverifiedBeneficiaries', 'verifiedBeneficiaries'));
    }

    public function export(Request $request)
    {
        $seniors = SeniorCitizenBeneficiary::with('barangay')
            ->when($request->barangay, fn($q) => $q->whereHas('barangay', fn($q2) => $q2->where('barangay_name', $request->barangay)))
            ->when($request->gender, fn($q) => $q->where('gender', $request->gender))
            ->when($request->remarks, fn($q) => $q->where('remarks', $request->remarks))
            ->when(!$request->type || $request->type == 'Senior Citizen', fn($q) => $q)
            ->when($request->search, fn($q) => $q->where(function($q2) use ($request) {
                $q2->where('last_name', 'like', '%'.$request->search.'%')
                   ->orWhere('first_name', 'like', '%'.$request->search.'%')
                   ->orWhere('middle_name', 'like', '%'.$request->search.'%')
                   ->orWhere('osca_number', 'like', '%'.$request->search.'%');
            }))
            ->get();

        $pwds = PWDBeneficiary::with('barangay')
            ->when($request->barangay, fn($q) => $q->whereHas('barangay', fn($q2) => $q2->where('barangay_name', $request->barangay)))
            ->when($request->gender, fn($q) => $q->where('gender', $request->gender))
            ->when($request->remarks, fn($q) => $q->where('remarks', $request->remarks))
            ->when(!$request->type || $request->type == 'PWD', fn($q) => $q)
            ->when($request->search, fn($q) => $q->where(function($q2) use ($request) {
                $q2->where('last_name', 'like', '%'.$request->search.'%')
                   ->orWhere('first_name', 'like', '%'.$request->search.'%')
                   ->orWhere('middle_name', 'like', '%'.$request->search.'%')
                   ->orWhere('pwd_id_number', 'like', '%'.$request->search.'%');
            }))
            ->get();

        $beneficiaries = $seniors->merge($pwds)->sortBy('last_name')->values();

        $response = new \Symfony\Component\HttpFoundation\StreamedResponse(function() use ($beneficiaries) {
            $handle = fopen('php://output', 'w');
            // Header
            fputcsv($handle, [
                'Full Name', 'Barangay', 'Type', 'Gender', 'Birthday', 'Age', 'ID Number', 'Remarks'
            ]);
            // Rows
            foreach ($beneficiaries as $b) {
                $type = $b instanceof \App\Models\SeniorCitizenBeneficiary ? 'Senior Citizen' : 'PWD';
                $idNumber = '';
                if ($type === 'Senior Citizen') {
                    try {
                        $idNumber = $b->osca_number ? \Illuminate\Support\Facades\Crypt::decryptString($b->osca_number) : '';
                        // Remove s:N:"value"; pattern if present
                        $idNumber = preg_replace('/^s:\d+:"([^"]+)";$/', '$1', $idNumber);
                    } catch (\Exception $e) {
                        $idNumber = '';
                    }
                } else {
                    try {
                        $idNumber = $b->pwd_id_number ? \Illuminate\Support\Facades\Crypt::decryptString($b->pwd_id_number) : '';
                        $idNumber = preg_replace('/^s:\d+:"([^"]+)";$/', '$1', $idNumber);
                    } catch (\Exception $e) {
                        $idNumber = '';
                    }
                }
                fputcsv($handle, [
                    "{$b->last_name}, {$b->first_name} {$b->middle_name}",
                    $b->barangay->barangay_name ?? 'N/A',
                    $type,
                    $b->gender,
                    $b->birthday,
                    $b->age,
                    $idNumber,
                    $b->remarks ?? '',
                ]);
            }
            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="beneficiaries.csv"');

        return $response;
    }


}

