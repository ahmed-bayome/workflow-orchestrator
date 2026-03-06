<?php

namespace App\Http\Controllers;

use App\Models\WorkflowRequest;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index()
    {
        $total = WorkflowRequest::count();
        
        $byStatus = [
            'pending' => WorkflowRequest::where('status', 'pending')->count(),
            'in_progress' => WorkflowRequest::where('status', 'in_progress')->count(),
            'approved' => WorkflowRequest::where('status', 'approved')->count(),
            'rejected' => WorkflowRequest::where('status', 'rejected')->count(),
        ];
        
        $last7Days = WorkflowRequest::where('created_at', '>=', Carbon::now()->subDays(7))->count();

        return response()->json([
            'total' => $total,
            'by_status' => $byStatus,
            'last_7_days' => $last7Days,
        ]);
    }
}
