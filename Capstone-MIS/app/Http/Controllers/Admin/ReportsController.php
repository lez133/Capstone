<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use App\Models\Schedule;
use App\Models\AidProgram;
use App\Models\Barangay;
use App\Models\Beneficiary;

class ReportsController extends Controller
{
    // GET /admin/reports/overview
    public function overview(Request $request)
    {
        return view('content.admin-interface.reports-logs.report');
    }

    // GET /admin/reports/data?days=30
    public function data(Request $request)
    {
        $days = (int) $request->query('days', 30);
        $end = Carbon::today();
        $start = (clone $end)->subDays($days - 1);

        // find schedules that overlap the period
        $schedules = Schedule::whereDate('start_date', '<=', $end)
            ->whereDate('end_date', '>=', $start)
            ->get();

        // program ids and names
        $programIds = $schedules->pluck('aid_program_id')->filter()->unique()->values();
        $programCount = $programIds->count();
        $programNames = AidProgram::whereIn('id', $programIds)->pluck(
            Schema::hasColumn((new AidProgram)->getTable(), 'aid_program_name') ? 'aid_program_name' : 'name',
            'id'
        )->toArray();

        // barangays covered by those schedules
        $barangayIds = [];
        foreach ($schedules as $s) {
            if (Schema::hasColumn($s->getTable(), 'barangay_ids') && !empty($s->barangay_ids)) {
                $ids = is_string($s->barangay_ids) ? json_decode($s->barangay_ids, true) : $s->barangay_ids;
                if (is_array($ids)) $barangayIds = array_merge($barangayIds, $ids);
            } elseif (Schema::hasColumn($s->getTable(), 'barangay_id') && $s->barangay_id) {
                $barangayIds[] = $s->barangay_id;
            }
        }
        $barangayIds = collect($barangayIds)->filter()->unique()->values()->toArray();
        $barangayCount = count($barangayIds);
        $barangayNames = Barangay::whereIn('id', $barangayIds)->pluck('barangay_name', 'id')->toArray();

        // estimated beneficiaries: verified beneficiaries in those barangays (simple estimate)
        $beneficiariesQuery = Beneficiary::query();
        if (!empty($barangayIds)) {
            $beneficiariesQuery->whereIn('barangay_id', $barangayIds);
        } else {
            $beneficiariesQuery->whereRaw('0 = 1'); // no barangays -> zero
        }
        // try common verified columns
        if (Schema::hasColumn((new Beneficiary)->getTable(), 'verified')) {
            $beneficiariesQuery->where('verified', 1);
        } elseif (Schema::hasColumn((new Beneficiary)->getTable(), 'verified_at')) {
            $beneficiariesQuery->whereNotNull('verified_at');
        }
        $estimatedBeneficiaryCount = $beneficiariesQuery->count();

        // breakdown per program: barangays and beneficiary estimates per program
        $perProgram = [];
        foreach ($programIds as $pid) {
            $progSchedules = $schedules->where('aid_program_id', $pid);
            $pBarangayIds = [];
            foreach ($progSchedules as $ps) {
                if (Schema::hasColumn($ps->getTable(), 'barangay_ids') && !empty($ps->barangay_ids)) {
                    $ids = is_string($ps->barangay_ids) ? json_decode($ps->barangay_ids, true) : $ps->barangay_ids;
                    if (is_array($ids)) $pBarangayIds = array_merge($pBarangayIds, $ids);
                } elseif (Schema::hasColumn($ps->getTable(), 'barangay_id') && $ps->barangay_id) {
                    $pBarangayIds[] = $ps->barangay_id;
                }
            }
            $pBarangayIds = collect($pBarangayIds)->filter()->unique()->values()->toArray();
            $pBenefQuery = Beneficiary::query();
            if (!empty($pBarangayIds)) $pBenefQuery->whereIn('barangay_id', $pBarangayIds);
            if (Schema::hasColumn((new Beneficiary)->getTable(), 'verified')) {
                $pBenefQuery->where('verified', 1);
            } elseif (Schema::hasColumn((new Beneficiary)->getTable(), 'verified_at')) {
                $pBenefQuery->whereNotNull('verified_at');
            }
            $perProgram[] = [
                'id' => $pid,
                'name' => $programNames[$pid] ?? ('Program '.$pid),
                'barangay_count' => count($pBarangayIds),
                'barangay_ids' => $pBarangayIds,
                'estimated_beneficiaries' => $pBenefQuery->count(),
            ];
        }

        // prepare chart data (program vs estimated beneficiaries)
        $labels = array_map(fn($p) => $p['name'], $perProgram);
        $values = array_map(fn($p) => $p['estimated_beneficiaries'], $perProgram);

        return response()->json([
            'period' => [
                'start' => $start->toDateString(),
                'end' => $end->toDateString(),
                'days' => $days
            ],
            'summary' => [
                'program_count' => $programCount,
                'barangay_count' => $barangayCount,
                'estimated_beneficiaries' => $estimatedBeneficiaryCount
            ],
            'per_program' => $perProgram,
            'labels' => $labels,
            'values' => $values,
        ]);
    }
}
