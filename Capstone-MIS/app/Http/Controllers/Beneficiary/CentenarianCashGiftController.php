<?php
namespace App\Http\Controllers\Beneficiary;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\CentenarianCashGift;
use Carbon\Carbon;

class CentenarianCashGiftController extends Controller
{
    public function index()
    {
        $user = Auth::guard('beneficiary')->user();
        $beneficiary = $user->beneficiary ?? $user;

        // Restrict to Senior Citizen or age 70+
        $isSenior = strtolower($beneficiary->beneficiary_type) === 'senior citizen';
        $age = $beneficiary->birthday ? Carbon::parse($beneficiary->birthday)->age : null;

        if (!($isSenior || ($age !== null && $age >= 70))) {
            // Option 1: Show a message
            return view('content.beneficiary-interface.dashboard.centenarian-cash-gift-not-eligible');
            // Option 2: Redirect with error
            // return redirect()->route('beneficiary.dashboard')->with('error', 'This page is only for senior citizens aged 70 and above.');
        }

        $today = Carbon::today();
        $birthday = $beneficiary->birthday ?? null;

        $milestones = [80, 85, 90, 95, 100];
        if ($birthday) {
            $currentAge = Carbon::parse($birthday)->age;
            if ($currentAge > 100) {
                for ($i = 101; $i <= $currentAge; $i++) {
                    $milestones[] = $i;
                }
            }
        }

        $milestone_status = [];
        foreach ($milestones as $milestone_age) {
            $milestone_date = $birthday ? Carbon::parse($birthday)->addYears($milestone_age) : null;
            $gift = CentenarianCashGift::where('beneficiary_id', $beneficiary->id)
                ->where('milestone_age', $milestone_age)
                ->first();

            $amount = ($milestone_age == 100) ? 100000 : 10000;
            $milestone_status[] = [
                'age' => $milestone_age,
                'date' => $milestone_date ? $milestone_date->toDateString() : null,
                'is_today' => $milestone_date && $milestone_date->isToday(),
                'is_past' => $milestone_date && $milestone_date->lessThanOrEqualTo($today),
                'given' => $gift ? true : false,
                'given_at' => $gift ? $gift->given_at : null,
                'amount' => $amount,
            ];
        }

        return view('content.beneficiary-interface.dashboard.centenarian-cash-gift', compact('beneficiary', 'milestone_status'));
    }
}
