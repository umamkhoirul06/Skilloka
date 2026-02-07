<?php

namespace App\Http\Controllers\Admin\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Placeholder data - replace with aggregate queries
        $stats = [
            'total_tenants' => 45,
            'active_students' => 12500,
            'monthly_gmv' => 450000000,
            'pending_verifications' => 3
        ];

        return view('super_admin.dashboard', compact('stats'));
    }
}
