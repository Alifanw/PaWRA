<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                  ->orWhere('resource', 'like', "%{$search}%");
            });
        }

        if ($request->has('action')) {
            $query->where('action', $request->action);
        }

        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $logs = $query->latest()
            ->paginate(20)
            ->through(fn ($log) => [
                'id' => $log->id,
                'action' => $log->action,
                'resource' => $log->resource,
                'resource_id' => $log->resource_id,
                'user_name' => $log->user?->name ?? 'System',
                'ip_address' => $log->ip_addr,
                'created_at' => $log->created_at->format('Y-m-d H:i:s'),
            ]);

        $actions = AuditLog::select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        return Inertia::render('Admin/AuditLogs/Index', [
            'logs' => $logs,
            'actions' => $actions,
            'filters' => $request->only(['search', 'action', 'start_date', 'end_date']),
        ]);
    }

    public function show(AuditLog $auditLog)
    {
        $auditLog->load('user');

        return Inertia::render('Admin/AuditLogs/Show', [
            'log' => [
                'id' => $auditLog->id,
                'action' => $auditLog->action,
                'resource' => $auditLog->resource,
                'resource_id' => $auditLog->resource_id,
                'user_name' => $auditLog->user?->name,
                'ip_address' => $auditLog->ip_addr,
                'user_agent' => $auditLog->user_agent,
                'before_data' => $auditLog->before_json,
                'after_data' => $auditLog->after_json,
                'created_at' => $auditLog->created_at,
            ],
        ]);
    }

    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:audit_logs,id',
        ]);

        $deletedCount = AuditLog::whereIn('id', $validated['ids'])->delete();

        return back()->with('success', "$deletedCount audit log(s) deleted successfully");
    }
}
