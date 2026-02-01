<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Barangay;
use App\Models\Beneficiary;
use App\Models\SeniorCitizenBeneficiary;
use App\Models\PWDBeneficiary;
use Illuminate\Support\Facades\Schema;

class BeneficiaryDashViewer extends Controller
{
    public function index()
    {
        // Make the Barangay selection page the dashboard
        return $this->selectBarangay();
    }

    public function selectBarangay()
    {
        $barangays = Barangay::orderBy('barangay_name')->get()->map(function ($barangay) {
            $id = $barangay->id;

            // specialized totals (if tables exist)
            $totalSeniorRegistered = class_exists(SeniorCitizenBeneficiary::class)
                ? SeniorCitizenBeneficiary::where('barangay_id', $id)->count()
                : 0;
            $totalPwdRegistered = class_exists(PWDBeneficiary::class)
                ? PWDBeneficiary::where('barangay_id', $id)->count()
                : 0;

            // keywords and exact matches to catch variations
            $seniorPatterns = ['senior', 'senior citizen', 'senior_citizen', 'senior-citizen'];
            $pwdPatterns    = ['pwd', 'person with disability', 'person with disabilities'];

            $matchBuilder = function ($query, array $patterns) {
                $query->where(function ($q) use ($patterns) {
                    foreach ($patterns as $p) {
                        $q->orWhereRaw('LOWER(beneficiary_type) LIKE ?', ['%'.strtolower($p).'%']);
                    }
                });
            };

            // total in main beneficiaries table
            $totalSeniorInMain = Beneficiary::where('barangay_id', $id)
                ->where(function($q) use ($matchBuilder, $seniorPatterns) {
                    $matchBuilder($q, $seniorPatterns);
                })->count();

            $totalPwdInMain = Beneficiary::where('barangay_id', $id)
                ->where(function($q) use ($matchBuilder, $pwdPatterns) {
                    $matchBuilder($q, $pwdPatterns);
                })->count();

            // verified (verified = 1)
            $verifiedSenior = Beneficiary::where('barangay_id', $id)
                ->where('verified', 1)
                ->where(function($q) use ($matchBuilder, $seniorPatterns) {
                    $matchBuilder($q, $seniorPatterns);
                })->count();

            $verifiedPwd = Beneficiary::where('barangay_id', $id)
                ->where('verified', 1)
                ->where(function($q) use ($matchBuilder, $pwdPatterns) {
                    $matchBuilder($q, $pwdPatterns);
                })->count();

            $unverifiedSenior = max(0, $totalSeniorInMain - $verifiedSenior);
            $unverifiedPwd    = max(0, $totalPwdInMain - $verifiedPwd);

            return (object) array_merge($barangay->toArray(), [
                'counts' => [
                    'total_senior_registered' => $totalSeniorRegistered,
                    'total_pwd_registered'    => $totalPwdRegistered,
                    'total_senior'            => $totalSeniorInMain,
                    'total_pwd'               => $totalPwdInMain,
                    'verified_senior'         => $verifiedSenior,
                    'verified_pwd'            => $verifiedPwd,
                    'unverified_senior'       => $unverifiedSenior,
                    'unverified_pwd'          => $unverifiedPwd,
                ],
            ]);
        });

        return view('content.admin-interface.beneficiaries.select-barangay', compact('barangays'));
    }

    public function showInterface($encryptedBarangayId)
    {
        $barangayId = decrypt($encryptedBarangayId);

        return view('admin.beneficiaries.dashboard', compact('barangayId'));
    }


    public function showBeneficiariesInterface($encryptedBarangayId)
    {
        $barangayId = decrypt($encryptedBarangayId);
        $selectedBarangay = Barangay::find($barangayId);

        // --- existing counts computation (keep as-is) ---
        $id = $barangayId;
        $totalSeniorRegistered = SeniorCitizenBeneficiary::where('barangay_id', $id)->count();
        $totalPwdRegistered    = PWDBeneficiary::where('barangay_id', $id)->count();

        $seniorKeywords = ['senior', 'senior citizen', 'senior_citizen'];
        $pwdKeywords    = ['pwd', 'person with disability', 'person with disabilities'];

        $matchKeywords = function ($query, array $keywords) {
            $query->where(function ($q) use ($keywords) {
                foreach ($keywords as $kw) {
                    $q->orWhereRaw('LOWER(beneficiary_type) LIKE ?', ['%'.strtolower($kw).'%']);
                }
            });
        };

        // renamed to match the counts keys used later
        $totalSeniorInMain = Beneficiary::where('barangay_id', $id)
            ->where(function($q) use ($matchKeywords, $seniorKeywords) {
                $matchKeywords($q, $seniorKeywords);
            })->count();

        $totalPwdInMain = Beneficiary::where('barangay_id', $id)
            ->where(function($q) use ($matchKeywords, $pwdKeywords) {
                $matchKeywords($q, $pwdKeywords);
            })->count();

        // use 'verified' naming to match counts array
        $verifiedSenior = Beneficiary::where('barangay_id', $id)
            ->where('verified', 1)
            ->where(function($q) use ($matchKeywords, $seniorKeywords) {
                $matchKeywords($q, $seniorKeywords);
            })->count();

        $verifiedPwd = Beneficiary::where('barangay_id', $id)
            ->where('verified', 1)
            ->where(function($q) use ($matchKeywords, $pwdKeywords) {
                $matchKeywords($q, $pwdKeywords);
            })->count();

        $unverifiedSenior = max(0, $totalSeniorInMain - $verifiedSenior);
        $unverifiedPwd    = max(0, $totalPwdInMain - $verifiedPwd);

        $counts = [
            'total_senior_registered' => $totalSeniorRegistered,
            'total_pwd_registered'    => $totalPwdRegistered,
            'total_senior'            => $totalSeniorInMain,
            'total_pwd'               => $totalPwdInMain,
            'verified_senior'         => $verifiedSenior,
            'verified_pwd'            => $verifiedPwd,
            'unverified_senior'       => $unverifiedSenior,
            'unverified_pwd'          => $unverifiedPwd,
        ];
        // --- end counts ---

        // Load actual beneficiaries for this barangay so the view can list records
        // choose a safe column to order by (fallbacks)
        $preferredOrderCols = ['lastname','last_name','surname','lname','firstname','id'];
        $orderBy = 'id';
        foreach ($preferredOrderCols as $col) {
            if (Schema::hasColumn('beneficiaries', $col)) {
                $orderBy = $col;
                break;
            }
        }
        $beneficiaries = Beneficiary::where('barangay_id', $barangayId)
            ->orderBy($orderBy)
            ->get();

        // also provide filtered collections if needed by type
        $seniors = $beneficiaries->filter(function($b){
            return stripos($b->beneficiary_type ?? '', 'senior') !== false;
        })->values();

        $pwds = $beneficiaries->filter(function($b){
            return stripos($b->beneficiary_type ?? '', 'pwd') !== false;
        })->values();

        return view(
            'content.admin-interface.beneficiaries.beneficiaries-interface',
            compact('selectedBarangay', 'counts', 'beneficiaries', 'seniors', 'pwds')
        );
    }

    public function search(Request $request)
    {
        $search = $request->get('search', '');
        $barangays = Barangay::where('barangay_name', 'like', "%{$search}%")
            ->orderBy('barangay_name')
            ->get()
            ->map(function ($barangay) {
                return [
                    'barangay_name' => $barangay->barangay_name,
                    'encrypted_id' => encrypt($barangay->id),
                ];
            });
        return response()->json($barangays);
    }
}
