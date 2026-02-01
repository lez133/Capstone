<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ActivityLog;
use App\Models\MSWDMember;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use Barryvdh\DomPDF\Facade\Pdf as PDF; // optional - if package installed

class LogsController extends Controller
{
    public function index()
    {
        return view('content.admin-interface.reports-logs.index');
    }

    /**
     * JSON endpoint for charts / analytics
     */
    public function data(Request $request)
    {
        $days = (int) $request->query('days', 30);
        $from = Carbon::now()->subDays($days - 1)->startOfDay();
        $to = Carbon::now()->endOfDay();

        // daily counts
        $daily = ActivityLog::selectRaw("DATE(created_at) as day, COUNT(*) as total")
                    ->whereBetween('created_at', [$from, $to])
                    ->groupBy('day')
                    ->orderBy('day')
                    ->get()
                    ->mapWithKeys(function($r){
                        return [$r->day => (int)$r->total];
                    });

        // build continuous labels & values
        $labels = [];
        $values = [];
        for ($i = 0; $i < $days; $i++) {
            $d = $from->copy()->addDays($i)->format('Y-m-d');
            $labels[] = $d;
            $values[] = isset($daily[$d]) ? $daily[$d] : 0;
        }

        // top actions
        $topActions = ActivityLog::select('action', DB::raw('count(*) as total'))
                        ->whereBetween('created_at', [$from, $to])
                        ->groupBy('action')
                        ->orderByDesc('total')
                        ->limit(8)
                        ->get();

        // recent activity sample
        $recent = ActivityLog::with('user')->orderByDesc('created_at')->limit(20)->get();

        return response()->json([
            'labels' => $labels,
            'values' => $values,
            'topActions' => $topActions,
            'recent' => $recent,
        ]);
    }

    /**
     * Export CSV for the same data window
     */
    public function exportCsv(Request $request)
    {
        $days = (int) $request->query('days', 30);
        $from = Carbon::now()->subDays($days - 1)->startOfDay();
        $to = Carbon::now()->endOfDay();

        $filename = 'activity-logs-' . now()->format('Ymd_His') . '.csv';

        $query = ActivityLog::with('user')
            ->whereBetween('created_at', [$from, $to])
            ->orderByDesc('created_at');

        $callback = function() use ($query) {
            $fh = fopen('php://output', 'w');
            // header row
            fputcsv($fh, ['id','user_id','user_name','action','subject_type','subject_id','meta','ip_address','user_agent','created_at']);
            $query->chunk(200, function($rows) use ($fh) {
                foreach ($rows as $r) {
                    $userName = 'System';
                    if ($r->user) {
                        $userName = trim(($r->user->fname ?? '') . ' ' . ($r->user->lname ?? '')) ?: ($r->user->name ?? 'System');
                    } elseif (is_array($r->meta) && !empty($r->meta['user_name'])) {
                        $userName = $r->meta['user_name'];
                    }
                    fputcsv($fh, [
                        $r->id,
                        $r->user_id,
                        $userName,
                        $r->action,
                        $r->subject_type,
                        $r->subject_id,
                        json_encode($r->meta, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE),
                        $r->ip_address,
                        $r->user_agent,
                        $r->created_at->toDateTimeString(),
                    ]);
                }
            });
            fclose($fh);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }

    /**
     * Export PDF (tries barryvdh/laravel-dompdf, then dompdf)
     */
    public function exportPdf(Request $request)
    {
        $days = (int) $request->query('days', 30);
        $from = Carbon::now()->subDays($days - 1)->startOfDay();
        $to = Carbon::now()->endOfDay();

        $logs = ActivityLog::with('user')
            ->whereBetween('created_at', [$from, $to])
            ->orderByDesc('created_at')
            ->limit(1000)
            ->get()
            ->map(function($r){
                $user = 'System';
                if ($r->user) {
                    $user = trim(($r->user->fname ?? '') . ' ' . ($r->user->lname ?? '')) ?: ($r->user->name ?? 'System');
                } elseif (is_array($r->meta) && !empty($r->meta['user_name'])) {
                    $user = $r->meta['user_name'];
                }
                return [
                    'id' => $r->id,
                    'user' => $user,
                    'action' => $r->action,
                    'subject' => $r->subject_type ? (last(explode('\\', $r->subject_type)) . ($r->subject_id ? ' #' . $r->subject_id : '')) : '-',
                    'meta' => is_array($r->meta) ? json_encode($r->meta) : ($r->meta ?? ''),
                    'ip' => $r->ip_address,
                    'when' => $r->created_at->toDateTimeString(),
                ];
            });

        $filename = 'activity-logs-' . now()->format('Ymd_His') . '.pdf';
        $html = view('content.admin-interface.reports-logs.export_pdf', compact('logs','days'))->render();

        // use barryvdh/laravel-dompdf if available
        if (class_exists('Barryvdh\DomPDF\Facade\Pdf') || class_exists(PDF::class)) {
            try {
                $pdf = PDF::loadHTML($html)->setPaper('a4', 'portrait');
                return $pdf->download($filename);
            } catch (\Throwable $e) {
                // fallthrough to next attempt
            }
        }

        // try direct Dompdf
        if (class_exists(\Dompdf\Dompdf::class)) {
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            return response($dompdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ]);
        }

        return redirect()->back()->with('error', 'PDF export requires barryvdh/laravel-dompdf or dompdf/dompdf. Install: composer require barryvdh/laravel-dompdf');
    }
}
