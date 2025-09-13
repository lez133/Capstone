<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MSWDMember;

class MswdController extends Controller
{
    public function index(Request $request)
    {
        // sample data — replace with real queries in your controllers
        $stats = [
            'total_beneficiaries' => 1248,
            'scheduled_distributions' => 8,
            'pending_verifications' => 23,
            'unread_notifications' => 5,
        ];

        $recent = collect([
            ['id'=>1,'name'=>'Juan Dela Cruz','type'=>'PWD','barangay'=>'Poblacion','contact'=>'09170000001','status'=>'Pending','last_aid'=>'2025-08-12'],
            ['id'=>2,'name'=>'Maria Santos','type'=>'Senior','barangay'=>'Poblacion','contact'=>'09170000002','status'=>'Verified','last_aid'=>'2025-07-20'],
            ['id'=>3,'name'=>'Pedro Reyes','type'=>'PWD','barangay'=>'Burgos','contact'=>'09170000003','status'=>'Pending','last_aid'=>'2025-06-04'],
        ]);

        return view('content.admin-interface.dashboard.mswd-interface', compact('stats','recent'));
    }
}
